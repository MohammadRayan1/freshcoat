<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->constrained();
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->unsignedSmallInteger('service_radius_miles')->default(15);
            $table->unsignedSmallInteger('years_in_business')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->string('contractor_registration_number')->nullable();
            $table->timestamp('insurance_verified_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('pro_trial_ends_at')->nullable();
            $table->string('stripe_account_id')->nullable()->unique();
            $table->boolean('stripe_charges_enabled')->default(false);
            $table->boolean('allows_direct_pay')->default(false);
            $table->timestamps();
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('provider_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('book_now_price_cents')->nullable();
            $table->unique(['provider_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_service');
        Schema::dropIfExists('providers');
    }
};
