<?php

namespace App\Notifications;

use App\Models\Loan;

class LoanDisbursed extends AppNotification
{
    protected bool $sms = true;

    public function __construct(public Loan $loan) {}

    public function title(): string { return __('Loan disbursed'); }

    public function message(): string
    {
        return __(':amount has been sent for loan :loan. First installment of :inst is due on :date.', [
            'amount' => money($this->loan->disbursed_amount), 'loan' => $this->loan->loan_number,
            'inst' => money($this->loan->installment_amount), 'date' => $this->loan->first_due_date->format('d M Y'),
        ]);
    }

    public function url(): ?string { return route('user.loans.show', $this->loan); }
    public function icon(): string { return 'banknote'; }
    public function color(): string { return 'emerald'; }
}
