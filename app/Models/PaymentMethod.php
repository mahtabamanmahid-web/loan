<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name', 'type', 'account_number', 'account_name', 'account_kind',
        'instructions', 'qr_path', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    public function qrUrl(): ?string
    {
        return $this->qr_path ? Storage::disk('public')->url($this->qr_path) : null;
    }

    /** Lucide icon for the method type. */
    public function icon(): string
    {
        return match ($this->type) {
            'bank' => 'landmark',
            'other' => 'wallet',
            default => 'smartphone',
        };
    }
}
