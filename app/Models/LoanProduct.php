<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'name_bn', 'slug', 'description', 'description_bn', 'icon', 'min_amount', 'max_amount',
        'repayment_frequency', 'processing_fee_type', 'processing_fee_value', 'fee_collection',
        'fee_refundable', 'fee_purpose', 'fee_refund_policy', 'late_fee_per_installment', 'grace_days',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'processing_fee_value' => 'decimal:2',
            'late_fee_per_installment' => 'decimal:2',
            'fee_refundable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function terms(): HasMany
    {
        return $this->hasMany(LoanTerm::class)->orderBy('months');
    }

    public function activeTerms(): HasMany
    {
        return $this->terms()->where('is_active', true);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    /** Localised name / description. */
    public function displayName(): string
    {
        return app()->getLocale() === 'bn' && $this->name_bn ? $this->name_bn : $this->name;
    }

    public function displayDescription(): ?string
    {
        return app()->getLocale() === 'bn' && $this->description_bn ? $this->description_bn : $this->description;
    }

    /** Processing fee in Taka for a given principal. */
    public function feeFor(float $amount): float
    {
        $value = (float) $this->processing_fee_value;

        return round($this->processing_fee_type === 'percent' ? $amount * $value / 100 : $value, 2);
    }

    public function feeLabel(): string
    {
        $v = rtrim(rtrim(number_format((float) $this->processing_fee_value, 2), '0'), '.');

        return $this->processing_fee_type === 'percent' ? $v.'%' : '৳'.$v;
    }
}
