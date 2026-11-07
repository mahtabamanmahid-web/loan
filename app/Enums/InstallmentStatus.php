<?php

namespace App\Enums;

enum InstallmentStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Upcoming'),
            self::Partial => __('Partially Paid'),
            self::Paid => __('Paid'),
            self::Overdue => __('Overdue'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'sky',
            self::Partial => 'amber',
            self::Paid => 'emerald',
            self::Overdue => 'rose',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'calendar-clock',
            self::Partial => 'circle-dashed',
            self::Paid => 'circle-check',
            self::Overdue => 'alarm-clock',
        };
    }
}
