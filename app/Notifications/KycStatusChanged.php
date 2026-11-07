<?php

namespace App\Notifications;

use App\Enums\DocumentStatus;
use App\Models\UserDocument;

class KycStatusChanged extends AppNotification
{
    protected bool $sms = true;

    public function __construct(public UserDocument $document) {}

    public function title(): string { return __('NID verification: :status', ['status' => $this->document->status->label()]); }

    public function message(): string
    {
        return match ($this->document->status) {
            DocumentStatus::Approved => __('Your NID has been verified successfully.'),
            DocumentStatus::Rejected => __('Your NID verification was rejected. :note', ['note' => $this->document->admin_note]),
            DocumentStatus::Resubmit => __('Please upload your NID again. :note', ['note' => $this->document->admin_note]),
            default => __('Your NID is under review.'),
        };
    }

    public function url(): ?string { return route('user.kyc.show'); }
    public function icon(): string { return $this->document->status->icon(); }
    public function color(): string { return $this->document->status->color(); }
}
