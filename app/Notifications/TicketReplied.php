<?php

namespace App\Notifications;

use App\Models\SupportTicket;

class TicketReplied extends AppNotification
{
    public function __construct(public SupportTicket $ticket) {}

    public function title(): string { return __('Support replied to your ticket'); }

    public function message(): string
    {
        return __('Ticket :no: :subject', ['no' => $this->ticket->ticket_number, 'subject' => $this->ticket->subject]);
    }

    public function url(): ?string { return route('user.tickets.show', $this->ticket); }
    public function icon(): string { return 'message-circle'; }
}
