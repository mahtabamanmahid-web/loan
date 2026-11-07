<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function loan(): BelongsTo { return $this->belongsTo(Loan::class); }
    public function installment(): BelongsTo { return $this->belongsTo(LoanInstallment::class, 'loan_installment_id'); }
    public function transaction(): BelongsTo { return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id'); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }
}
