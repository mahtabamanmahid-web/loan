<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Initiated = 'initiated';
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Initiated => __('Initiated'),
            self::Pending => __('Pending'),
            self::Completed => __('Successful'),
            self::Failed => __('Failed'),
            self::Cancelled => __('Cancelled'),
            self::Refunded => __('Refunded'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Initiated => 'slate',
            self::Pending => 'amber',
            self::Completed => 'emerald',
            self::Failed => 'rose',
            self::Cancelled => 'zinc',
            self::Refunded => 'violet',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Initiated => 'loader',
            self::Pending => 'clock',
            self::Completed => 'circle-check',
            self::Failed => 'circle-x',
            self::Cancelled => 'ban',
            self::Refunded => 'undo-2',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Cancelled, self::Refunded], true);
    }
}
