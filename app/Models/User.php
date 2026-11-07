<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'locale', 'avatar_path',
        'notification_preferences', 'terms_accepted_at',
    ];

    // admin_role_id, is_blocked, phone_verified_at are NOT fillable on purpose
    // (prevents mass-assignment privilege escalation). Set them explicitly.

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    /* ---------------- Relationships ---------------- */

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class)->latest();
    }

    public function latestDocument(): HasOne
    {
        return $this->hasOne(UserDocument::class)->latestOfMany();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LoanApplication::class)->latest();
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class)->latest();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class)->latest();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class)->latest();
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class)->latest('created_at');
    }

    public function adminRole(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class);
    }

    /* ---------------- Helpers ---------------- */

    public function isAdmin(): bool
    {
        return $this->admin_role_id !== null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->adminRole?->slug === AdminRole::SUPER_ADMIN;
    }

    /** Check an admin permission slug such as "loans.approve". */
    public function hasPermission(string $permission): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return $this->isSuperAdmin() || $this->adminRole->hasPermission($permission);
    }

    public function nidStatus(): ?string
    {
        return $this->latestDocument?->status?->value;
    }

    public function isKycApproved(): bool
    {
        return $this->latestDocument?->status === DocumentStatus::Approved;
    }

    public function hasOpenApplication(): bool
    {
        return $this->applications()->whereIn('status', ApplicationStatus::open())->exists();
    }

    public function avatarUrl(): string
    {
        if ($this->avatar_path) {
            return Storage::disk('public')->url($this->avatar_path);
        }

        return 'https://ui-avatars.com/api/?background=random&color=fff&name='.urlencode($this->name);
    }

    /** Notification channel preferences with sensible defaults. */
    public function wantsNotification(string $channel): bool
    {
        $prefs = array_merge(['email' => true, 'sms' => true], $this->notification_preferences ?? []);

        return (bool) ($prefs[$channel] ?? true);
    }

    /** Used by the SMS channel. */
    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }
}
