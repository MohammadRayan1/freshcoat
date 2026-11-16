<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained();
            $table->foreignId('city_id')->constrained();
            $table->string('size_label');
            $table->decimal('size_multiplier', 4, 2);
            $table->string('timing');
            $table->boolean('customer_supplies_paint')->default(false);
            $table->char('zip', 5);
            $table->string('address')->nullable();
            $table->text('details')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('provider_quote_request', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->decimal('distance_miles', 6, 2);
            $table->timestamp('notified_at')->nullable();
            $table->unique(['quote_request_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_quote_request');
        Schema::dropIfExists('quote_requests');
    }
};
