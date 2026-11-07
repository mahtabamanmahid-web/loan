# Deployment Guide

This guide covers a VPS (Ubuntu + Nginx + PHP-FPM + MySQL), with notes for cPanel shared hosting.

---

## 1. Server requirements

* Ubuntu 22.04/24.04, 1 GB RAM minimum (2 GB recommended)
* PHP 8.3+ with `fpm mysql mbstring xml curl zip gd intl bcmath`
* MySQL 8 or MariaDB 10.6+
* Nginx, Composer 2, Git, Supervisor (for the queue worker)
* A domain with **HTTPS** (Let's Encrypt). HTTPS is mandatory for a financial site.

```bash
sudo apt update
sudo apt install nginx mysql-server supervisor unzip git \
  php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath
```

## 2. Database

```sql
CREATE DATABASE loan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'loan_user'@'localhost' IDENTIFIED BY 'a-long-random-password';
GRANT ALL PRIVILEGES ON loan_system.* TO 'loan_user'@'localhost';
FLUSH PRIVILEGES;
```

## 3. Code

```bash
cd /var/www
sudo git clone <your-repo> loan && cd loan       # or upload the files
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
```

### Production `.env` (important values)

```dotenv
APP_ENV=production
APP_DEBUG=false                      # NEVER true in production
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Dhaka

DB_DATABASE=loan_system
DB_USERNAME=loan_user
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_LIFETIME=60
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true           # requires HTTPS

QUEUE_CONNECTION=database
MAIL_MAILER=smtp                     # + MAIL_HOST/PORT/USERNAME/PASSWORD

UDDOKTAPAY_MODE=live
UDDOKTAPAY_BASE_URL=https://pay.your-domain.com
UDDOKTAPAY_API_KEY=<live key from UddoktaPay panel>

SMS_DRIVER=http
SMS_API_URL=...
SMS_API_KEY=...
SMS_SENDER_ID=...
```

> **Back up your `APP_KEY`.** NID numbers and addresses are encrypted with it. If the key is lost, that data cannot be decrypted.

```bash
php artisan migrate --force --seed
php artisan storage:link
php artisan admin:create
php artisan config:cache && php artisan route:cache && php artisan view:cache

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 4. Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/loan/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    client_max_body_size 12M;          # NID uploads (3 × 4 MB)
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-Content-Type-Options nosniff;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known) { deny all; }
    gzip on; gzip_types text/css application/javascript application/json image/svg+xml;
}
```

```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

## 5. Scheduler and queue

```bash
# Cron (installment reminders, overdue marking, cleanup)
( crontab -l -u www-data 2>/dev/null; echo "* * * * * cd /var/www/loan && php artisan schedule:run >> /dev/null 2>&1" ) | sudo crontab -u www-data -
```

Supervisor `/etc/supervisor/conf.d/loan-worker.conf`:

```ini
[program:loan-worker]
command=php /var/www/loan/artisan queue:work --sleep=3 --tries=3 --max-time=3600
user=www-data
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/loan/storage/logs/worker.log
```

```bash
sudo supervisorctl reread && sudo supervisorctl update
```

## 6. UddoktaPay live setup

1. In your UddoktaPay panel, copy the **API key** into `.env`.
2. Webhook URL: `https://your-domain.com/api/v1/payments/uddoktapay/webhook`
3. Run `php artisan config:cache` after changing `.env`.
4. Make one small real payment and check **Admin → Payments → (payment) → Audit Log**.

## 7. Behind Cloudflare or a load balancer

Edit `bootstrap/app.php` and enable `trustProxies` with your proxy IP ranges, so customer IPs (used for rate limiting and audit logs) and HTTPS are detected correctly. Don't trust `*` unless the server is only reachable through the proxy.

## 8. Backups

* **Database:** Admin → Backups (manual), or schedule `mysqldump` with cron.
* **Files that must also be backed up:**
  * `storage/app/private/kyc` (NID images, private)
  * `storage/app/public` (logo, banners, avatars)
  * `.env` (contains `APP_KEY`)
* Keep at least one copy off the server, encrypted.

## 9. Updating

```bash
php artisan down
git pull && composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
php artisan up
```

## 10. cPanel / shared hosting

* Upload the project **outside** `public_html` (e.g. `/home/user/loan`), then point the domain's document root to `/home/user/loan/public`. If you can't, copy the `public/` contents into `public_html` and edit the two paths in `public_html/index.php`.
* Create the database in **MySQL Databases** and fill `.env`.
* Use **Terminal** (or SSH) for `composer install`, `php artisan migrate --seed`, `php artisan storage:link` and `php artisan admin:create`.
* **Cron Jobs**: `* * * * * /usr/local/bin/php /home/user/loan/artisan schedule:run >/dev/null 2>&1`
* Without Supervisor, set `QUEUE_CONNECTION=sync` (notifications are sent immediately).

## 11. Go-live checklist

- [ ] Lending licence/registration obtained; institution name and number set in Admin → Loan Settings
- [ ] Terms & Conditions, Privacy Policy and loan terms written (Admin → Website → Legal Pages)
- [ ] Loan packages, rates and fees reviewed (sample packages replaced)
- [ ] `APP_DEBUG=false`, HTTPS active, `SESSION_SECURE_COOKIE=true`
- [ ] SMS gateway works (register a test account and receive the OTP)
- [ ] SMTP works (forgot password by e-mail)
- [ ] UddoktaPay live key set; one real payment verified end-to-end
- [ ] Cron running (`php artisan schedule:list`)
- [ ] Backups configured and a restore tested
- [ ] Default Super Admin password changed; extra staff created with the least-privilege role
