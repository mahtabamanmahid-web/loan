<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Defaulted = 'defaulted';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active Loan'),
            self::Completed => __('Completed'),
            self::Defaulted => __('Defaulted'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'teal',
            self::Completed => 'slate',
            self::Defaulted => 'rose',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Active => 'activity',
            self::Completed => 'trophy',
            self::Defaulted => 'triangle-alert',
        };
    }
}
