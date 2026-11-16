<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount_cents');
            $table->date('earliest_start_date')->nullable();
            $table->text('message')->nullable();
            $table->boolean('direct_pay')->default(false);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['quote_request_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
