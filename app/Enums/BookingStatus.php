<?php

namespace App\Enums;

enum BookingStatus: string
{
    case AwaitingCard = 'awaiting_card';
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case PaymentFailed = 'payment_failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingCard => 'Waiting for card',
            self::Scheduled => 'Scheduled',
            self::Completed => 'Completed',
            self::PaymentFailed => 'Payment failed',
            self::Cancelled => 'Cancelled',
        };
    }
}
