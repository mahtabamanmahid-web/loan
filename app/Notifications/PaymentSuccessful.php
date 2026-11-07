<?php

namespace App\Notifications;

use App\Models\PaymentTransaction;

class PaymentSuccessful extends AppNotification
{
    protected bool $sms = true;

    public function __construct(public PaymentTransaction $transaction) {}

    public function title(): string { return __('Payment successful'); }

    public function message(): string
    {
        return __('We received :amount (:purpose). Transaction ID: :trx', [
            'amount' => money($this->transaction->amount),
            'purpose' => $this->transaction->purposeLabel(),
            'trx' => $this->transaction->gateway_transaction_id ?? $this->transaction->reference,
        ]);
    }

    public function url(): ?string { return route('user.transactions.show', $this->transaction); }
    public function icon(): string { return 'circle-check'; }
    public function color(): string { return 'emerald'; }
}
