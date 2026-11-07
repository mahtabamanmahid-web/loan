<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanInstallment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => InstallmentStatus::class,
            'due_date' => 'date',
            'principal_component' => 'decimal:2',
            'interest_component' => 'decimal:2',
            'amount' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo { return $this->belongsTo(Loan::class); }
    public function repayments(): HasMany { return $this->hasMany(LoanRepayment::class); }

    /** What the customer must still pay for this instalment (incl. late fee). */
    public function dueAmount(): float
    {
        return max(round((float) $this->amount + (float) $this->late_fee - (float) $this->paid_amount, 2), 0);
    }
}
