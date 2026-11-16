<?php

namespace App\Providers;

use App\Payments\FakeGateway;
use App\Payments\PaymentGateway;
use App\Payments\StripeGateway;
use App\Support\LogSmsSender;
use App\Support\SmsSender;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function () {
            $secret = config('services.stripe.secret');

            return $secret
                ? new StripeGateway(new StripeClient($secret))
                : new FakeGateway;
        });

        $this->app->singleton(SmsSender::class, LogSmsSender::class);
    }

    public function boot(): void
    {
        //
    }
}
