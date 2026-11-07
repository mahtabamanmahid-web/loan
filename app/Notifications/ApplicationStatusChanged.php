<?php

namespace App\Notifications;

use App\Enums\ApplicationStatus;
use App\Models\LoanApplication;

/** Approved / rejected / documents requested / agreement ready. */
class ApplicationStatusChanged extends AppNotification
{
    protected bool $sms = true;

    public function __construct(public LoanApplication $application) {}

    public function title(): string
    {
        return match ($this->application->status) {
            ApplicationStatus::Approved => __('Application approved'),
            ApplicationStatus::Rejected => __('Application not approved'),
            ApplicationStatus::DocumentsRequested => __('Additional documents needed'),
            ApplicationStatus::AwaitingAgreement => __('Your loan agreement is ready'),
            default => __('Application status updated'),
        };
    }

    public function message(): string
    {
        $ref = $this->application->reference;

        return match ($this->application->status) {
            ApplicationStatus::Approved => __('Application :ref was approved for :amount. Your agreement will be prepared next.', ['ref' => $ref, 'amount' => money($this->application->approved_amount)]),
            ApplicationStatus::Rejected => __('Application :ref was not approved. Reason: :reason', ['ref' => $ref, 'reason' => $this->application->admin_note]),
            ApplicationStatus::DocumentsRequested => __('Please provide more information for :ref: :note', ['ref' => $ref, 'note' => $this->application->admin_note]),
            ApplicationStatus::AwaitingAgreement => __('Please read and accept the agreement for :ref.', ['ref' => $ref]),
            default => __('Application :ref is now: :status', ['ref' => $ref, 'status' => $this->application->status->label()]),
        };
    }

    public function url(): ?string { return route('user.applications.show', $this->application); }
    public function icon(): string { return $this->application->status->icon(); }
    public function color(): string { return $this->application->status->color(); }
}
