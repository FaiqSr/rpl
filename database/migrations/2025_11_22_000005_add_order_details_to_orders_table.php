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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('total_price');
            $table->text('notes')->nullable()->after('status');
            $table->string('buyer_name')->nullable()->after('notes');
            $table->string('buyer_phone')->nullable()->after('buyer_name');
            $table->text('buyer_address')->nullable()->after('buyer_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['status', 'notes', 'buyer_name', 'buyer_phone', 'buyer_address']);
        });
    }
};
