<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable(); // linked to central users

            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('job_title')->nullable(); // alias for SQL

            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->enum('employment_type', ['full_time', 'part_time', 'contractor'])->default('full_time');
            $table->boolean('can_take_bookings')->default(true);
            $table->boolean('is_visible_online')->default(true);

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('avatar_url')->nullable();

            $table->timestamps();

            $table->index(['salon_id', 'is_active']);
            $table->index(['salon_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
