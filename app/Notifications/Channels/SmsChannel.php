<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

/** Custom notification channel: delivers toSms() text through SmsService. */
class SmsChannel
{
    public function __construct(private SmsService $sms) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $to = $notifiable->routeNotificationFor('sms', $notification);
        if ($to) {
            $this->sms->send($to, $notification->toSms($notifiable));
        }
    }
}
