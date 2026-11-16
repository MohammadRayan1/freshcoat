<?php

namespace App\Notifications;

use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimCode extends Notification
{
    use Queueable;

    public function __construct(public Provider $provider, public string $code) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your FreshCoat verification code')
            ->line("Use this code to claim {$this->provider->business_name} on FreshCoat:")
            ->line("**{$this->code}**")
            ->line('It expires in 15 minutes. If you didn\'t ask for this, you can ignore this email.');
    }
}
