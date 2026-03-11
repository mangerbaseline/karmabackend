<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('appointment_id')->nullable()->index();

            $table->enum('channel', ['sms', 'whatsapp', 'email', 'push']);
            $table->string('recipient');
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued');
            $table->dateTime('scheduled_for')->nullable();
            $table->dateTime('sent_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
