<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One selectable duration (e.g. 6 months) with its own interest rate. */
class LoanTerm extends Model
{
    protected $fillable = ['loan_product_id', 'months', 'interest_rate', 'is_active'];

    protected function casts(): array
    {
        return ['interest_rate' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }
}
