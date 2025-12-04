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
        Schema::create('threshold_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('threshold_profiles')->onDelete('cascade');
            $table->string('sensor_type', 50)->comment('amonia_ppm, suhu_c, kelembaban_rh, cahaya_lux');
            $table->decimal('ideal_min', 10, 2)->nullable();
            $table->decimal('ideal_max', 10, 2)->nullable();
            $table->decimal('warn_min', 10, 2)->nullable();
            $table->decimal('warn_max', 10, 2)->nullable();
            $table->decimal('danger_min', 10, 2)->nullable();
            $table->decimal('danger_max', 10, 2)->nullable();
            $table->timestamps();
            
            $table->unique(['profile_id', 'sensor_type']);
            $table->index('sensor_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threshold_values');
    }
};
