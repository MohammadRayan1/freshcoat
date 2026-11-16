<?php

namespace App\Enums;

enum PaymentMode: string
{
    /** Customer pays by card through FreshCoat; charged on completion. */
    case Platform = 'platform';

    /** Customer pays the painter directly; the painter prepays the service fee. */
    case Direct = 'direct';
}
