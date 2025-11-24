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
        Schema::create('robot_activities', function (Blueprint $table) {
            $table->id();
            $table->string('robot_id');
            $table->enum('activity_type', ['patrol_start', 'patrol_end', 'charging_start', 'charging_end', 'maintenance', 'error', 'position_update']);
            $table->string('description')->nullable();
            $table->string('position')->nullable();
            $table->json('metadata')->nullable(); // {"distance": 0.5, "duration": 30}
            $table->timestamp('occurred_at');
            $table->timestamps();
            
            $table->index(['robot_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robot_activities');
    }
};
