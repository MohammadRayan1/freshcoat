<?php

namespace App\Notifications;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewQuoteRequest extends Notification
{
    use Queueable;

    public function __construct(public QuoteRequest $quoteRequest, public float $distanceMiles) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->quoteRequest;

        return (new MailMessage)
            ->subject("New quote request: {$request->jobName()} in {$request->city->name}")
            ->line("A homeowner {$this->distanceMiles} miles from you wants a quote.")
            ->line("{$request->jobName()} · {$request->size_label} · {$request->timingLabel()}")
            ->lineIf($request->media()->exists(), 'The homeowner attached photos or video.')
            ->action('View and quote', route('pro.requests.show', $request));
    }
}
