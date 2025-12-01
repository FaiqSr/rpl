<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create tools table if it doesn't exist (it should have been created by renaming robots, but robots didn't exist)
        if (!Schema::hasTable('tools')) {
            Schema::create('tools', function (Blueprint $table) {
            $table->id();
                $table->string('tool_id')->unique();
                $table->string('name');
                $table->string('model');
                $table->string('location')->nullable();
                $table->enum('operational_status', ['operating', 'idle', 'maintenance', 'offline', 'charging'])->default('offline');
                $table->integer('battery_level')->default(100); // 0-100
                $table->timestamp('last_activity_at')->nullable();
                $table->string('current_position')->nullable(); // "Kandang A - Area 3"
                $table->decimal('total_distance_today', 8, 2)->default(0); // km
                $table->integer('operating_hours_today')->default(0); // minutes
                $table->decimal('uptime_percentage', 5, 2)->default(100); // 7 hari terakhir
                $table->json('health_status')->nullable(); // {"motors": "normal", "sensors": "normal", "battery": "good"}
                $table->integer('patrol_count_today')->default(0);
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
