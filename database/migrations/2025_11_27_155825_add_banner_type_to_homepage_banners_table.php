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
        Schema::table('homepage_banners', function (Blueprint $table) {
            $table->string('banner_type')->default('square')->after('image_url'); // 'square', 'rectangle_top', 'rectangle_bottom'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_banners', function (Blueprint $table) {
            $table->dropColumn('banner_type');
        });
    }
};
