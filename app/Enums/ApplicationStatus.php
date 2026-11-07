<?php

namespace App\Enums;

/**
 * Every status a loan application can be in.
 * label() / color() / icon() are used by the <x-status-badge> component.
 */
enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case NidPending = 'nid_pending';
    case DocumentsRequested = 'documents_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case AwaitingAgreement = 'awaiting_agreement';
    case Disbursed = 'disbursed';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => __('Application Submitted'),
            self::UnderReview => __('Under Review'),
            self::NidPending => __('NID Verification Pending'),
            self::DocumentsRequested => __('Additional Documents Required'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::AwaitingAgreement => __('Awaiting Agreement'),
            self::Disbursed => __('Loan Disbursed'),
            self::Active => __('Active Loan'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'sky',
            self::UnderReview => 'indigo',
            self::NidPending, self::DocumentsRequested => 'amber',
            self::Approved, self::Disbursed => 'emerald',
            self::Rejected, self::Cancelled => 'rose',
            self::AwaitingAgreement => 'violet',
            self::Active => 'teal',
            self::Completed => 'slate',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Submitted => 'send',
            self::UnderReview => 'search',
            self::NidPending => 'id-card',
            self::DocumentsRequested => 'file-warning',
            self::Approved => 'badge-check',
            self::Rejected => 'circle-x',
            self::AwaitingAgreement => 'file-signature',
            self::Disbursed => 'banknote',
            self::Active => 'activity',
            self::Completed => 'trophy',
            self::Cancelled => 'ban',
        };
    }

    /** Statuses in which the customer still has an "open" application. */
    public static function open(): array
    {
        return [
            self::Submitted->value, self::UnderReview->value, self::NidPending->value,
            self::DocumentsRequested->value, self::Approved->value, self::AwaitingAgreement->value,
        ];
    }

    /** Ordered steps for the progress timeline on the status page. */
    public static function timeline(): array
    {
        return [self::Submitted, self::UnderReview, self::Approved, self::AwaitingAgreement, self::Disbursed, self::Active, self::Completed];
    }
}
