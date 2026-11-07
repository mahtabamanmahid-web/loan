<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['gateway_response', 'payment_url'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'gateway_fee' => 'decimal:2',
            'charged_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'processed_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function application(): BelongsTo { return $this->belongsTo(LoanApplication::class, 'loan_application_id'); }
    public function loan(): BelongsTo { return $this->belongsTo(Loan::class); }
    public function installment(): BelongsTo { return $this->belongsTo(LoanInstallment::class, 'loan_installment_id'); }
    public function logs(): HasMany { return $this->hasMany(PaymentLog::class)->latest('id'); }

    public function purposeLabel(): string
    {
        return match ($this->purpose) {
            'processing_fee' => __('Processing Fee'),
            'installment' => __('Loan Installment'),
            default => ucfirst(str_replace('_', ' ', $this->purpose)),
        };
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) {
            return $q;
        }

        return $q->where(fn ($q) => $q->where('reference', 'like', "%{$term}%")
            ->orWhere('gateway_transaction_id', 'like', "%{$term}%")
            ->orWhere('gateway_invoice_id', 'like', "%{$term}%")
            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")));
    }
}
