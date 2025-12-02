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
            $table->string('shipping_service')->nullable()->after('buyer_address');
            $table->string('payment_method')->nullable()->after('shipping_service');
            $table->string('tracking_number')->nullable()->after('payment_method');
            $table->string('payment_status')->default('pending')->after('tracking_number'); // pending, paid, expired
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_service', 'payment_method', 'tracking_number', 'payment_status', 'paid_at']);
        });
    }
};
