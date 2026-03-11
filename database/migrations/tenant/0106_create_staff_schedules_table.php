<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff_profiles')->cascadeOnDelete();

            $table->integer('weekday')->nullable();
            $table->date('date')->nullable();

            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);

            $table->timestamps();

            $table->index(['salon_id', 'staff_id']);
            $table->index(['salon_id', 'weekday']);
            $table->index(['salon_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_schedules');
    }
};
