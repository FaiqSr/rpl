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
        Schema::table('sensor_readings', function (Blueprint $table) {
            // Derivative: perubahan nilai dari data sebelumnya (delta)
            $table->decimal('derivative_amonia', 6, 2)->nullable()->after('amonia_ppm');
            $table->decimal('derivative_suhu', 5, 2)->nullable()->after('suhu_c');
            $table->decimal('derivative_kelembaban', 6, 2)->nullable()->after('kelembaban_rh');
            $table->decimal('derivative_cahaya', 6, 2)->nullable()->after('cahaya_lux');
            
            // Gradient: rate of change per jam (slope)
            $table->decimal('gradient_amonia', 6, 2)->nullable()->after('derivative_amonia');
            $table->decimal('gradient_suhu', 5, 2)->nullable()->after('derivative_suhu');
            $table->decimal('gradient_kelembaban', 6, 2)->nullable()->after('derivative_kelembaban');
            $table->decimal('gradient_cahaya', 6, 2)->nullable()->after('derivative_cahaya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->dropColumn([
                'derivative_amonia',
                'derivative_suhu',
                'derivative_kelembaban',
                'derivative_cahaya',
                'gradient_amonia',
                'gradient_suhu',
                'gradient_kelembaban',
                'gradient_cahaya'
            ]);
        });
    }
};
