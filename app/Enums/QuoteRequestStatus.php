<?php

namespace App\Enums;

enum QuoteRequestStatus: string
{
    case Open = 'open';
    case Booked = 'booked';
    case Closed = 'closed';
}
