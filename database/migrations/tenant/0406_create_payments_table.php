<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('appointment_id')->nullable()->index();
            $table->unsignedBigInteger('client_id')->index();

            $table->decimal('amount', 12, 2);
            $table->char('currency_code', 3)->default('EUR');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'online', 'stripe', 'other'])->default('cash');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('paid');
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
