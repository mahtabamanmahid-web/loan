<?php

namespace App\Notifications;

use App\Models\LoanApplication;

class ApplicationSubmitted extends AppNotification
{
    protected bool $sms = true;

    public function __construct(public LoanApplication $application) {}

    public function title(): string { return __('Application submitted'); }

    public function message(): string
    {
        return __('We received your loan application :ref for :amount. We will review it and notify you.', [
            'ref' => $this->application->reference, 'amount' => money($this->application->requested_amount),
        ]);
    }

    public function url(): ?string { return route('user.applications.show', $this->application); }
    public function icon(): string { return 'send'; }
    public function color(): string { return 'sky'; }
}
