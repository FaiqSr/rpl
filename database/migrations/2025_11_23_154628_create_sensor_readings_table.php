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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->decimal('amonia_ppm', 5, 2); // Ammonia in ppm
            $table->decimal('suhu_c', 4, 1); // Temperature in Celsius
            $table->decimal('kelembaban_rh', 5, 1); // Humidity in percentage
            $table->decimal('cahaya_lux', 6, 1); // Light in lux
            $table->timestamp('recorded_at')->useCurrent(); // Waktu pencatatan sensor
            $table->timestamps();
            
            // Index untuk query cepat berdasarkan waktu
            $table->index('recorded_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
