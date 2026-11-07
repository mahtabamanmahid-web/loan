<?php

namespace App\Notifications;

use App\Models\LoanInstallment;

class InstallmentReminder extends AppNotification
{
    protected bool $sms = true;

    public function __construct(public LoanInstallment $installment) {}

    public function title(): string
    {
        return $this->installment->due_date->isPast() ? __('Installment overdue') : __('Installment reminder');
    }

    public function message(): string
    {
        return __('Installment #:no of :amount for loan :loan is due on :date.', [
            'no' => $this->installment->installment_number,
            'amount' => money($this->installment->dueAmount()),
            'loan' => $this->installment->loan->loan_number,
            'date' => $this->installment->due_date->format('d M Y'),
        ]);
    }

    public function url(): ?string { return route('user.loans.show', $this->installment->loan_id); }
    public function icon(): string { return 'alarm-clock'; }
    public function color(): string { return $this->installment->due_date->isPast() ? 'rose' : 'amber'; }
}
