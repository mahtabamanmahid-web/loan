<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * php artisan db:seed
     *
     * Creates roles/permissions, starter loan packages and FAQs.
     * A Super Admin is created ONLY when ADMIN_EMAIL, ADMIN_PHONE and ADMIN_PASSWORD
     * are set in .env – otherwise run:  php artisan admin:create
     */
    public function run(): void
    {
        $this->call([
            AdminRoleSeeder::class,
            LoanProductSeeder::class,
            FaqSeeder::class,
        ]);

        $email = env('ADMIN_EMAIL');
        $phone = env('ADMIN_PHONE');
        $password = env('ADMIN_PASSWORD');

        if ($email && $phone && $password && ! User::where('email', $email)->exists()) {
            $admin = User::create(['name' => env('ADMIN_NAME', 'Super Admin'), 'email' => $email, 'phone' => $phone, 'password' => $password]);
            $admin->forceFill([
                'admin_role_id' => AdminRole::where('slug', AdminRole::SUPER_ADMIN)->value('id'),
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ])->save();
            $this->command?->info("Super Admin created: {$email}");
        } elseif (! User::whereNotNull('admin_role_id')->exists()) {
            $this->command?->warn('No admin exists yet. Run: php artisan admin:create');
        }
    }
}
