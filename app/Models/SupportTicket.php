<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = ['ticket_number', 'user_id', 'subject', 'category', 'priority', 'status'];

    protected function casts(): array
    {
        return ['closed_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function replies(): HasMany { return $this->hasMany(SupportTicketReply::class)->oldest(); }

    public function statusColor(): string
    {
        return match ($this->status) {
            'open' => 'sky', 'answered' => 'emerald', 'customer_reply' => 'amber', default => 'slate',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => __('Open'), 'answered' => __('Answered'), 'customer_reply' => __('Customer Replied'), default => __('Closed'),
        };
    }
}
