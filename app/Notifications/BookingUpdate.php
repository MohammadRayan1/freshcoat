<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingUpdate extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking, public string $headline) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $notifiable->id === $this->booking->user_id
            ? route('bookings.show', $this->booking)
            : route('pro.dashboard');

        return (new MailMessage)
            ->subject($this->headline)
            ->line($this->headline)
            ->action('View booking', $url);
    }
}
