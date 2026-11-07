# Online Loan Management System

A complete digital loan platform built with **Laravel 13, MySQL, Blade, Tailwind CSS 4, Alpine.js, Lucide icons and UddoktaPay**.

It includes a public website, customer panel, admin panel, loan engine, NID/KYC verification, online payments, notifications and website customization.

> **বাংলা সারসংক্ষেপ:** এটি একটি সম্পূর্ণ অনলাইন ঋণ ব্যবস্থাপনা সিস্টেম। এতে আছে পাবলিক ওয়েবসাইট, গ্রাহক প্যানেল, অ্যাডমিন প্যানেল (`/admin`), এনআইডি যাচাই, UddoktaPay পেমেন্ট, কিস্তি ব্যবস্থাপনা, বাংলা/ইংরেজি ভাষা এবং ডার্ক মোড। ইনস্টলেশনের ধাপ নিচে দেওয়া আছে।

---

## ⚠️ Legal & ethical requirements (read first)

* Lending money to the public in Bangladesh needs the proper **licence/registration** (e.g. Microcredit Regulatory Authority, Bangladesh Bank). **Do not go live without it.** Enter your licensed institution name and number in **Admin → Loan Settings**. They appear on the fee disclosure, agreements and receipts.
* The system is built to stay transparent:
  * Applying is **free**. Fees are **never collected before approval**.
  * Every fee is shown, with amount, purpose, refundability, refund conditions and recipient, **before** the customer signs or pays.
  * A successful fee payment **never** approves or disburses a loan by itself.
  * A loan is created **only** when an admin records a real disbursement with a transfer reference.
  * Reviews must belong to a **real customer with a loan** and must be verified before they are published. Fake reviews cannot be created.
* Follow Bangladesh data-protection, KYC/AML and consumer-protection rules. Write your own Terms & Conditions and Privacy Policy in **Admin → Website → Legal Pages** (ideally with a lawyer).

---

## Features

| Area | What's included |
|---|---|
| **Public website** | Hero section, interactive loan calculator, packages, process steps, benefits, verified reviews, FAQ, contact, WhatsApp button, fee & terms disclosure page, eligibility + repayment calculators, legal pages, SEO meta, 404/500/403/419/429/503 pages |
| **Auth** | Login with mobile **or** email, show/hide password, remember me, rate limiting, OTP mobile verification, duplicate account prevention (unique phone, email, NID), forgot password (SMS OTP or e-mail link), login history |
| **Customer panel** | Dashboard (approved total, balance, paid, remaining installments, next due date, progress), 4-step application wizard, NID upload with previews, application status timeline, agreement e-signature, printable agreement and PDF, installment schedule, online payment, transaction history with filters and PDF receipts, profile, password, notification settings, support tickets, notifications, reviews |
| **Loan engine** | Products with per-duration interest rates, flat-rate calculator, approval with adjustable amount/duration/rate, agreement snapshot, disbursement recording, automatic installment schedule, partial payments, overdue detection with late fees, completion, daily reminders |
| **Payments** | UddoktaPay checkout, redirect callback, webhook (IPN), **server-side verification**, amount and reference checks, idempotency, duplicate TrxID protection, full audit log, refunds (fees), manual re-verify, CSV/Excel export, reports |
| **Admin panel** (`/admin`) | Dashboard KPIs, customers (search, edit, block), NID review (private image streaming, duplicate NID warning), applications workflow, loans and manual repayments, packages, loan settings, website customization (name, logo, favicon, colours, banner, texts, contact, social, legal pages, SEO, homepage section order), reviews, FAQ, announcements, support tickets, staff and **role-based permissions**, activity/login/payment logs, database backups, maintenance mode |
| **UX** | Mobile-first, bottom navigation on phones, dark/light mode, Bangla (default) and English, loading spinners on every submit button, toasts, success/failure animations, confirm dialogs |

---

## Requirements

* PHP **8.3+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `gd`, `zip`, `intl`, `bcmath`
* MySQL 8 / MariaDB 10.6+
* Composer 2
* For rebuilding CSS: **either** Node 18+ (`npm`) **or** the Tailwind standalone CLI (no Node needed)

> **XAMPP users:** open `C:\xampp\php\php.ini` and remove the `;` in front of `extension=gd`, `extension=zip`, `extension=intl` and `extension=sodium`, then restart Apache.

---

## Installation (local)

```bash
# 1. Install PHP dependencies
composer install

# 2. Environment file
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate

# 3. Create an empty database (utf8mb4), e.g. "loan_system", then set DB_* in .env

# 4. Tables + starter data (roles, permissions, 2 sample packages, FAQs)
php artisan migrate --seed

# 5. Public storage link (logo, avatars, banner)
php artisan storage:link

# 6. Create your Super Admin
php artisan admin:create
#   (or put ADMIN_NAME/ADMIN_EMAIL/ADMIN_PHONE/ADMIN_PASSWORD in .env before step 4)

# 7. Start
php artisan serve
```

Open:
* Website: http://localhost:8000
* Admin panel: http://localhost:8000/admin

### Rebuilding the CSS (only after changing Blade classes)

The compiled `public/css/app.css` is already included.

```bash
npm install && npm run build          # with Node
# or, without Node: download the Tailwind CLI binary from
# https://github.com/tailwindlabs/tailwindcss/releases (v4.1.x) and run:
tailwindcss -i resources/css/app.css -o public/css/app.css --minify
```

Alpine.js, Lucide, AOS and the Alpine collapse plugin are vendored in `public/vendor/`, so no build step is needed for JavaScript.

---

## Configuration

All secrets live in `.env`. Nothing is hard-coded.

### UddoktaPay

```dotenv
UDDOKTAPAY_MODE=sandbox
UDDOKTAPAY_BASE_URL=https://sandbox.uddoktapay.com
UDDOKTAPAY_API_KEY=982d381360a69d419689740d9f2e26ce36fb7a50   # public sandbox key
UDDOKTAPAY_CALLBACK_URL=      # empty = {APP_URL}/payment/callback
UDDOKTAPAY_CANCEL_URL=        # empty = {APP_URL}/payment/cancel
UDDOKTAPAY_WEBHOOK_URL=       # empty = {APP_URL}/api/v1/payments/uddoktapay/webhook
```

* UddoktaPay uses **one secret: the API key** (header `RT-UDDOKTAPAY-API-KEY`). There is **no separate API secret**, so the requested `UDDOKTAPAY_API_SECRET` variable was intentionally dropped. The key is used only on the server.
* **Live:** set `UDDOKTAPAY_BASE_URL` to your own UddoktaPay panel domain (e.g. `https://pay.your-domain.com`), set your live API key, and `UDDOKTAPAY_MODE=live`.
* The webhook only works when the site is reachable from the internet (use a tunnel such as `ngrok` for local tests). Payments are still verified from the browser redirect.

### SMS (OTP and reminders)

`SMS_DRIVER=log` writes messages to `storage/logs/laravel.log`, which is handy during development (the OTP code appears there). For production set `SMS_DRIVER=http` and fill `SMS_API_URL`, `SMS_API_KEY`, `SMS_SENDER_ID` and the parameter names your provider expects (`SMS_PARAM_*`). Works with most Bangladeshi bulk-SMS HTTP APIs.

### Mail

Configure `MAIL_*` (SMTP). Used for password-reset links and notification e-mails.

### Scheduler (installment reminders, overdue marking)

Add one cron entry on the server:

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Runs `loans:reminders` daily at 09:00 (Asia/Dhaka): marks overdue installments, applies late fees once, and sends reminders.

---

## How a loan flows

```
Customer registers ─► OTP verify ─► uploads NID (pending)
        │
        ▼
Applies (amount, duration, details, accepts fee disclosure)      status: submitted / nid_pending
        │
Admin: verify NID ─► Start review ─► Approve (amount / months / rate)   status: approved
        │                         └► Reject  /  Request documents
        ▼
Admin: Create agreement (frozen text with all fees)               status: awaiting_agreement
        │
Customer: reads & e-signs (types full name) ─► pays upfront fee ONLY if the package says so
        │
Admin: records REAL disbursement (method + transfer reference)    status: disbursed
        │      → Loan ID created + installment schedule
        ▼
Customer pays installments online (or admin records cash/bank)    status: active
        │
All paid ─► loan completed                                        status: completed
```

### Payment security

1. A local transaction with our own reference is created **before** redirecting to UddoktaPay. The reference travels in `metadata`.
2. The browser redirect (`/payment/callback?invoice_id=…`) and the webhook both call **`verify-payment`** on UddoktaPay's server. Browser parameters are never trusted.
3. The transaction row is **locked**; `processed_at` ensures the business effect (repayment) happens **exactly once**, even if redirect and webhook arrive together.
4. Paid amount must be **≥ expected amount**; a gateway **transaction ID can be used only once**.
5. Every step is written to `payment_logs` (Admin → Logs → Payment Audit Logs).

---

## Admin roles

| Role | Can do |
|---|---|
| Super Admin | Everything (cannot be removed if last) |
| Manager | Everything except staff management, backups and maintenance |
| Loan Officer | Customers (view), NID review, review/approve/reject, agreements, tickets |
| Payment Manager | Disbursements, repayments, payments, refunds, exports |
| Support Agent | View customers/loans/payments, tickets, reviews, FAQ and announcements |

Permissions per role can be changed in **Admin → Roles & Permissions** (see `config/permissions.php`).

---

## Testing

```bash
php vendor/bin/phpunit                    # 84 tests, uses SQLite in memory
```

Covers registration, OTP, login and rate limiting, loan calculations, applications, NID upload and private storage, payment initiation and verification, duplicate callbacks, replayed TrxIDs, amount mismatch, webhook authentication, fee payments, approval → agreement → disbursement, double-disbursement prevention, installments and late fees, reminders, permissions per role, every page rendering, dynamic site name and maintenance mode.

**Live UddoktaPay sandbox test** (needs internet):

```bash
UDDOKTAPAY_SANDBOX_TEST=1 php vendor/bin/phpunit --group sandbox
# Windows PowerShell:  $env:UDDOKTAPAY_SANDBOX_TEST=1; php vendor/bin/phpunit --group sandbox
```

For a full manual sandbox payment: create a loan, click **Pay Now**, complete the payment on the sandbox page, and you are redirected back and verified automatically.

---

## Project structure

```
app/
  Console/Commands/      admin:create, loans:reminders
  Enums/                 ApplicationStatus, DocumentStatus, PaymentStatus, LoanStatus, InstallmentStatus
  Exceptions/            LoanWorkflowException, PaymentGatewayException
  Http/Controllers/
    Admin/               admin panel (one controller per module)
    Api/                 calculator API + UddoktaPay webhook
    Auth/                login, register, OTP, password reset
    Frontend/            home, calculator, legal pages, language
    Payment/             checkout, callback, result
    User/                customer panel
  Http/Middleware/       admin, customer, OTP check, blocked users, locale, maintenance, security headers
  Http/Requests/         form validation
  Models/                Eloquent models (one per table)
  Notifications/         in-app + e-mail + SMS notifications
  Services/              LoanService, LoanCalculator, PaymentService, UddoktaPayService, KycService,
                         OtpService, SmsService, SettingService, BackupService, ActivityLogger ...
  helpers.php            setting(), sys_setting(), money(), bn_digits(), safe_markdown() ...
config/                  uddoktapay.php, sms.php, permissions.php
database/                migrations, seeders, factories
lang/bn.json             Bangla translations (English is the key)
public/css, public/js    compiled CSS + app.js (Alpine components)
public/vendor            Alpine, Lucide, AOS (pinned versions)
resources/views          layouts, components, frontend, auth, user, admin, pdf, errors
routes/                  web.php, admin.php, api.php, console.php
tests/                   Feature + Unit tests
docs/                    DEPLOYMENT.md, PHASES.md
```

---

## Known limitations

* **Bangla text in PDF files:** DomPDF cannot shape Bangla conjuncts, so PDF labels are in English and agreements are frozen in English. Customer-entered Bangla text (e.g. an address) may not render correctly inside PDFs. The on-screen agreement can be printed from the browser with **Print**, which renders Bangla perfectly.
* **Excel export** is CSV with a UTF-8 BOM (opens directly in Excel). A native `.xlsx` needs an extra package such as `maatwebsite/excel`.
* **Refunds through the gateway** are limited to processing fees. Installment refunds must be adjusted manually.
* The UddoktaPay API docs don't publish the exact verify-response schema. The code reads the commonly returned fields (`status`, `invoice_id`, `amount`, `transaction_id`, `payment_method`, `sender_number`, `metadata`, `date`) defensively. Complete one sandbox payment end-to-end before going live.
* NID verification is manual (an admin compares the images). There is no automatic integration with the Election Commission NID server.

See **[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)** for production setup.
