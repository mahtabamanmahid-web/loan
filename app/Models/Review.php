<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Customer review. Always linked to a real user account; only reviews that an admin
 * has marked as verified AND published appear on the homepage.
 */
class Review extends Model
{
    protected $fillable = ['user_id', 'loan_id', 'display_name', 'location', 'rating', 'comment'];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean', 'is_published' => 'boolean', 'is_featured' => 'boolean', 'rating' => 'integer'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function loan(): BelongsTo { return $this->belongsTo(Loan::class); }

    public function scopePublic(Builder $q): Builder
    {
        return $q->where('is_published', true)->where('is_verified', true)
            ->orderByDesc('is_featured')->latest();
    }
}
