<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoanApplication extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'loan_product_id', 'loan_term_id',
        'applicant_name', 'phone', 'email', 'present_address', 'permanent_address', 'occupation',
        'monthly_income', 'purpose', 'emergency_contact_name', 'emergency_contact_phone',
        'requested_amount', 'requested_months', 'interest_rate', 'total_interest', 'total_payable',
        'installment_amount', 'processing_fee', 'status', 'terms_accepted_at', 'terms_accepted_ip',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'present_address' => 'encrypted',
            'permanent_address' => 'encrypted',
            'monthly_income' => 'decimal:2',
            'requested_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'total_interest' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'processing_fee' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'approved_interest_rate' => 'decimal:2',
            'terms_accepted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function product(): BelongsTo { return $this->belongsTo(LoanProduct::class, 'loan_product_id'); }
    public function term(): BelongsTo { return $this->belongsTo(LoanTerm::class, 'loan_term_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function agreement(): HasOne { return $this->hasOne(LoanAgreement::class); }
    public function loan(): HasOne { return $this->hasOne(Loan::class); }
    public function payments(): HasMany { return $this->hasMany(PaymentTransaction::class)->latest(); }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) {
            return $q;
        }

        return $q->where(function ($q) use ($term) {
            $q->where('reference', 'like', "%{$term}%")
                ->orWhere('applicant_name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /* Final figures: approved values when set, otherwise requested values. */
    public function finalAmount(): float { return (float) ($this->approved_amount ?? $this->requested_amount); }
    public function finalMonths(): int { return (int) ($this->approved_months ?? $this->requested_months); }
    public function finalRate(): float { return (float) ($this->approved_interest_rate ?? $this->interest_rate); }

    /** Has the (upfront) processing fee been paid successfully? */
    public function isFeePaid(): bool
    {
        return $this->payments()->where('purpose', 'processing_fee')->where('status', 'completed')->exists();
    }

    /** Does the customer still owe an upfront fee before disbursement? */
    public function requiresUpfrontFee(): bool
    {
        return $this->agreement
            && $this->agreement->fee_collection === 'upfront'
            && (float) $this->agreement->processing_fee > 0
            && ! $this->isFeePaid();
    }
}
