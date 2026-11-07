<?php

namespace App\Notifications;

use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base class for every customer notification.
 * Child classes only implement title(), message(), url() and icon().
 * Delivery: always in-app (database) + email/SMS according to user preferences.
 */
abstract class AppNotification extends Notification
{
    use Queueable;

    /** Should this notification also go out as SMS? (Only important events.) */
    protected bool $sms = false;

    abstract public function title(): string;

    abstract public function message(): string;

    abstract public function url(): ?string;

    public function icon(): string
    {
        return 'bell';
    }

    public function color(): string
    {
        return 'indigo';
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email && $notifiable->wantsNotification('email')) {
            $channels[] = 'mail';
        }
        if ($this->sms && $notifiable->wantsNotification('sms')) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'message' => $this->message(),
            'url' => $this->url(),
            'icon' => $this->icon(),
            'color' => $this->color(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title().' – '.site_name())
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line($this->message());

        if ($this->url()) {
            $mail->action(__('View details'), $this->url());
        }

        return $mail->salutation(site_name());
    }

    public function toSms(object $notifiable): string
    {
        return site_name().': '.$this->message();
    }
}
