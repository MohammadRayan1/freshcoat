<?php

namespace App\Notifications;

use App\Models\Quote;
use App\Support\Fees;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteReceived extends Notification
{
    use Queueable;

    public function __construct(public Quote $quote) {}

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
            ->subject("New quote from {$this->quote->provider->business_name}")
            ->line("{$this->quote->provider->business_name} quoted ".Fees::money($this->quote->amount_cents).' for your '.$this->quote->quoteRequest->jobName().' job.')
            ->action('Compare quotes', route('quote-requests.show', $this->quote->quoteRequest));
    }
}
