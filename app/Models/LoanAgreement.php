<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanAgreement extends Model
{
    protected $fillable = [
        'agreement_number', 'loan_application_id', 'content', 'principal', 'months', 'interest_rate',
        'total_payable', 'installment_amount', 'processing_fee', 'fee_collection', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'processing_fee' => 'decimal:2',
            'accepted_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }
}
