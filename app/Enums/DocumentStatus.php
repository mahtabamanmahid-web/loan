<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Resubmit = 'resubmit';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending Verification'),
            self::Approved => __('Verified'),
            self::Rejected => __('Rejected'),
            self::Resubmit => __('Resubmission Required'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Approved => 'emerald',
            self::Rejected => 'rose',
            self::Resubmit => 'orange',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'hourglass',
            self::Approved => 'shield-check',
            self::Rejected => 'shield-x',
            self::Resubmit => 'rotate-ccw',
        };
    }
}
