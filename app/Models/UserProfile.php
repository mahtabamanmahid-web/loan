<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'present_address', 'permanent_address', 'occupation', 'monthly_income',
        'date_of_birth', 'gender', 'emergency_contact_name', 'emergency_contact_phone',
    ];

    protected function casts(): array
    {
        return [
            // Addresses are personal data – encrypted at rest with APP_KEY.
            'present_address' => 'encrypted',
            'permanent_address' => 'encrypted',
            'monthly_income' => 'decimal:2',
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
