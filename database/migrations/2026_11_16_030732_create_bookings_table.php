<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->unique()->constrained();
            $table->foreignId('provider_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->unsignedInteger('amount_cents');
            $table->unsignedInteger('service_fee_cents');
            $table->string('payment_mode');
            $table->string('status');
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_setup_intent_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('charged_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
