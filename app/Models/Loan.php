<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A disbursed loan. Created only by LoanService::disburse(). */
class Loan extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'principal' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'total_interest' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'processing_fee' => 'decimal:2',
            'disbursed_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'total_late_fee' => 'decimal:2',
            'disbursed_at' => 'datetime',
            'first_due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function application(): BelongsTo { return $this->belongsTo(LoanApplication::class, 'loan_application_id'); }
    public function installments(): HasMany { return $this->hasMany(LoanInstallment::class)->orderBy('installment_number'); }
    public function repayments(): HasMany { return $this->hasMany(LoanRepayment::class)->latest('paid_at'); }
    public function disbursedBy(): BelongsTo { return $this->belongsTo(User::class, 'disbursed_by'); }

    /** Amount still owed (instalments + late fees − paid). */
    public function outstanding(): float
    {
        return max(round((float) $this->total_payable + (float) $this->total_late_fee - (float) $this->total_paid, 2), 0);
    }

    public function progressPercent(): int
    {
        $due = (float) $this->total_payable + (float) $this->total_late_fee;

        return $due > 0 ? (int) min(100, round((float) $this->total_paid / $due * 100)) : 0;
    }

    public function nextInstallment(): ?LoanInstallment
    {
        return $this->installments()->where('status', '!=', InstallmentStatus::Paid->value)->orderBy('installment_number')->first();
    }

    public function remainingInstallmentsCount(): int
    {
        return $this->installments()->where('status', '!=', InstallmentStatus::Paid->value)->count();
    }
}
