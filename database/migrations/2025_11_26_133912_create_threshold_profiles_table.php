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
        Schema::create('threshold_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('profile_key', 50)->unique()->comment('default, 1-7, 8-14, dll');
            $table->string('profile_name', 100)->comment('Default, 1-7 hari, dll');
            $table->integer('age_min_days')->nullable()->comment('NULL untuk default');
            $table->integer('age_max_days')->nullable()->comment('NULL untuk default');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index('profile_key');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threshold_profiles');
    }
};
