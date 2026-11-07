<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\AdminRole;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Test-only factory (used by PHPUnit tests, never by production seeders).
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '01'.fake()->numberBetween(3, 9).fake()->unique()->numerify('########'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password1'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null, 'phone_verified_at' => null]);
    }

    public function blocked(): static
    {
        return $this->state(fn () => ['is_blocked' => true]);
    }

    /** Admin with the given role slug (roles must be seeded first). */
    public function admin(string $role = AdminRole::SUPER_ADMIN): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->forceFill(['admin_role_id' => AdminRole::where('slug', $role)->value('id')])->save();
        });
    }

    /** Customer whose NID is already verified. */
    public function kycApproved(): static
    {
        return $this->afterCreating(function (User $user) {
            $nid = fake()->unique()->numerify('##########');
            $doc = new UserDocument([
                'nid_number' => $nid,
                'nid_hash' => UserDocument::hashNid($nid),
                'front_image_path' => 'kyc/test/front.jpg',
                'back_image_path' => 'kyc/test/back.jpg',
            ]);
            $doc->user_id = $user->id;
            $doc->status = DocumentStatus::Approved;
            $doc->save();
        });
    }
}
