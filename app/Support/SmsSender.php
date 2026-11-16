<?php

namespace App\Support;

interface SmsSender
{
    public function send(string $phone, string $message): void;
}
