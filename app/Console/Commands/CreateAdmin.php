<?php

namespace App\Console\Commands;

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/** php artisan admin:create  – interactively create an admin account. */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create {--name=} {--email=} {--phone=} {--role=super_admin} {--password=}';

    protected $description = 'Create an admin (staff) account';

    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?: $this->ask('Full name'),
            'email' => $this->option('email') ?: $this->ask('Email'),
            'phone' => $this->option('phone') ?: $this->ask('Mobile number (01XXXXXXXXX)'),
            'password' => $this->option('password') ?: $this->secret('Password (min 8, letters + numbers)'),
            'role' => $this->option('role'),
        ];

        $v = Validator::make($data, [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/', 'unique:users,phone'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role' => 'required|exists:admin_roles,slug',
        ]);

        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        $user = User::create(collect($data)->except('role')->all());
        $user->forceFill([
            'admin_role_id' => AdminRole::where('slug', $data['role'])->value('id'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ])->save();

        $this->info("Admin created. Login at ".url('/admin/login')." with {$data['email']}");

        return self::SUCCESS;
    }
}
