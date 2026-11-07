<?php

/*
|--------------------------------------------------------------------------
| Admin permissions & default roles (seeded by AdminRoleSeeder)
|--------------------------------------------------------------------------
| Check in code:  $user->hasPermission('loans.approve')
| In routes:      ->middleware('can:loans.approve')
| In Blade:       @can('loans.approve') ... @endcan
| Super Admin automatically has every permission.
*/

return [
    'permissions' => [
        'dashboard' => ['dashboard.view' => 'View dashboard'],
        'users' => [
            'users.view' => 'View customers',
            'users.manage' => 'Edit / block customers',
            'kyc.review' => 'Verify NID documents',
        ],
        'loans' => [
            'loans.view' => 'View applications & loans',
            'loans.review' => 'Review applications / request documents',
            'loans.approve' => 'Approve / reject applications',
            'loans.agreement' => 'Create loan agreements',
            'loans.disburse' => 'Record disbursements',
            'loans.repayments' => 'Record manual repayments',
            'products.manage' => 'Manage loan packages & durations',
        ],
        'payments' => [
            'payments.view' => 'View payments',
            'payments.verify' => 'Re-verify payments with gateway',
            'payments.refund' => 'Refund payments',
            'payments.export' => 'Export payment reports',
            'payments.methods' => 'Manage manual payment methods',
        ],
        'content' => [
            'settings.loan' => 'Loan settings',
            'settings.website' => 'Website customization & SEO',
            'reviews.manage' => 'Manage reviews',
            'content.manage' => 'Manage FAQ & announcements',
            'tickets.manage' => 'Handle support tickets',
        ],
        'system' => [
            'admins.manage' => 'Manage admin users & roles',
            'logs.view' => 'View activity & login logs',
            'backups.manage' => 'Database backups',
            'system.manage' => 'Maintenance mode & system settings',
        ],
    ],

    'roles' => [
        'super_admin' => ['name' => 'Super Admin', 'permissions' => '*'],
        'manager' => ['name' => 'Manager', 'permissions' => [
            'dashboard.view', 'users.view', 'users.manage', 'kyc.review', 'loans.view', 'loans.review', 'loans.approve',
            'loans.agreement', 'loans.disburse', 'loans.repayments', 'products.manage', 'payments.view', 'payments.verify',
            'payments.refund', 'payments.export', 'payments.methods', 'settings.loan', 'settings.website', 'reviews.manage', 'content.manage',
            'tickets.manage', 'logs.view',
        ]],
        'loan_officer' => ['name' => 'Loan Officer', 'permissions' => [
            'dashboard.view', 'users.view', 'kyc.review', 'loans.view', 'loans.review', 'loans.approve', 'loans.agreement', 'tickets.manage',
        ]],
        'payment_manager' => ['name' => 'Payment Manager', 'permissions' => [
            'dashboard.view', 'users.view', 'loans.view', 'loans.disburse', 'loans.repayments',
            'payments.view', 'payments.verify', 'payments.refund', 'payments.export', 'payments.methods',
        ]],
        'support_agent' => ['name' => 'Support Agent', 'permissions' => [
            'dashboard.view', 'users.view', 'loans.view', 'payments.view', 'tickets.manage', 'reviews.manage', 'content.manage',
        ]],
    ],
];
