# Build Phases: Files and Purpose

This lists every file created or changed in each phase and what it does.

## Phase 1: Project setup and database
| File | Purpose |
|---|---|
| `database/migrations/0001_01_00_000000_create_admin_roles_table.php` | `admin_roles`, `admin_permissions`, pivot (RBAC) |
| `database/migrations/0001_01_01_000000_create_users_table.php` | `users` (phone, role, block, locale, prefs), password resets, sessions |
| `database/migrations/2026_11_07_000001_…user_profiles_and_documents…` | `user_profiles` (encrypted addresses), `user_documents` (encrypted NID, private image paths, status) |
| `…000002_create_loan_products_and_terms_tables.php` | Packages (limits, fee disclosure, late fee) + durations with own interest rate |
| `…000003_create_loan_applications_table.php` | Applications: applicant snapshot, calculator snapshot, approval values, status |
| `…000004_create_loans_tables.php` | `loan_agreements`, `loans` (unique disbursement reference), `loan_installments` |
| `…000005_create_payment_tables.php` | `payment_transactions` (unique invoice/TrxID, processed_at), `payment_logs`, `loan_repayments` |
| `…000006_create_content_tables.php` | `website_settings`, `system_settings`, `reviews`, `faqs`, `announcements` |
| `…000007_create_support_and_audit_tables.php` | `notifications`, `support_tickets` (+ replies), `admin_activity_logs`, `login_histories`, `otp_codes` |
| `app/Models/*` | One Eloquent model per table, with casts (encrypted fields, enums) and relationships |
| `app/Enums/*` | Status enums with label, colour and icon |
| `database/seeders/*` | Roles/permissions, starter packages, FAQs, optional Super Admin from `.env` |
| `database/factories/*` | Test data factories (tests only) |
| `config/permissions.php` | Permission list and default role mapping |
| `app/Services/SettingService.php`, `app/helpers.php` | Cached dynamic settings plus helper functions |

## Phase 2: Authentication and user management
| File | Purpose |
|---|---|
| `app/Http/Requests/Auth/LoginRequest.php` | Phone/e-mail login, rate limit, blocked check, login history |
| `app/Http/Requests/Auth/RegisterRequest.php` | Registration validation and phone normalisation |
| `app/Http/Controllers/Auth/*` | Login, register, OTP verification, password reset (SMS OTP / e-mail link) |
| `app/Services/OtpService.php`, `SmsService.php` | Hashed OTPs with expiry and attempt limit; SMS driver (log/http) |
| `app/Http/Middleware/*` | `admin`, `customer`, `phone.verified`, `not.blocked`, locale, maintenance, security headers |
| `resources/views/auth/*` | Login, register (strength meter), OTP boxes, forgot/reset pages |

## Phase 3: Homepage and frontend
| File | Purpose |
|---|---|
| `resources/css/app.css` → `public/css/app.css` | Tailwind theme, brand colour variables, components, animations |
| `public/js/app.js` | Alpine components: calculator, wizard, eligibility, toasts, theme, loading buttons |
| `resources/views/layouts/{base,app,auth}.blade.php` | HTML shell (SEO, favicon, dark mode), public header/footer, mobile bottom nav, WhatsApp |
| `resources/views/components/*` | Reusable UI: icon, input, select, textarea, modal, alert, badge, status-badge, stat-card, empty, page-header, brand |
| `resources/views/frontend/home/*` | Homepage sections (each can be reordered or hidden by the admin) |
| `resources/views/frontend/{calculator,loan-terms,page}.blade.php` | Calculators, fee disclosure, legal pages |
| `app/Http/Controllers/Frontend/*`, `app/Services/ProductCatalog.php` | Public controllers and calculator data |

## Phase 4: User dashboard
`User/DashboardController`, `views/user/dashboard.blade.php`, `layouts/user.blade.php` show real figures from the database (approved total, balance, paid, remaining installments, next due, progress, open applications, recent transactions).

## Phase 5: Loan application and NID verification
| File | Purpose |
|---|---|
| `User/ApplicationController`, `Requests/User/StoreLoanApplicationRequest` | 4-step wizard, server validation, status page, cancel, agreement e-sign, PDFs |
| `User/KycController`, `Requests/User/KycRequest`, `Services/KycService` | NID upload (type, size and dimension checks), private storage, duplicate-NID block |
| `views/user/applications/*`, `views/user/kyc.blade.php`, `views/agreements/content.blade.php` | Wizard, status timeline, agreement page, KYC page |

## Phase 6: Loan calculator and loan management
| File | Purpose |
|---|---|
| `Services/LoanCalculator.php` | Flat-rate maths, schedule (rounding-safe), eligibility estimate |
| `Services/LoanService.php` | Submit, review, approve, reject, request documents, agreement, disburse (row lock), repayments, overdue and late fees |
| `Console/Commands/SendInstallmentReminders.php`, `routes/console.php` | Daily overdue marking and reminders |

## Phase 7: UddoktaPay integration
| File | Purpose |
|---|---|
| `config/uddoktapay.php` | Gateway configuration from `.env` |
| `Services/UddoktaPayService.php` | `checkout-v2`, `verify-payment`, `refund-payment`, webhook key check |
| `Services/PaymentService.php` | Initiate, verify (lock + idempotency + amount + TrxID checks), cancel, refund, audit log |
| `Payment/PaymentController.php`, `Api/UddoktaPayWebhookController.php` | Checkout redirects, callback, result page, IPN |

## Phase 8: Transaction management
`User/TransactionController` (filters, details, PDF receipt) and `views/pdf/*` (receipt, application form, agreement).

## Phase 9: Admin panel
`routes/admin.php`, `app/Http/Controllers/Admin/*` (Dashboard, Users, KYC, Applications, Loans, Products, Payments, Settings, Reviews, FAQ, Announcements, Tickets, Admin users, Roles, Logs, Backups) and `resources/views/admin/**`.

## Phase 10: Website customization and reviews
`Admin/SettingController` (branding, colours, uploads, homepage sections, contact, legal Markdown, SEO, maintenance) and `Admin/ReviewController` plus `User/ReviewController` (real-customer reviews with a verify/publish/feature workflow).

## Phase 11: Security and testing
* Security: hashed passwords, CSRF, Eloquent (no raw SQL with input), Blade escaping, Markdown with HTML stripped, encrypted PII, private KYC storage with audited streaming, RBAC gates, rate limits, security headers, mass-assignment protection, login history, admin audit logs.
* Tests in `tests/`: 84 automated tests plus an opt-in live sandbox group.

## Phase 12: Deployment and documentation
`README.md`, `docs/DEPLOYMENT.md`, `docs/PHASES.md`, `.env.example`, `package.json`, `lang/bn.json`.
