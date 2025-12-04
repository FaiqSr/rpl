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
        // Use raw SQL to ensure columns are added
        $columns = [
            'shipping_service' => "ALTER TABLE `orders` ADD COLUMN `shipping_service` VARCHAR(255) NULL AFTER `buyer_address`",
            'payment_method' => "ALTER TABLE `orders` ADD COLUMN `payment_method` VARCHAR(255) NULL AFTER `shipping_service`",
            'tracking_number' => "ALTER TABLE `orders` ADD COLUMN `tracking_number` VARCHAR(255) NULL AFTER `payment_method`",
            'payment_status' => "ALTER TABLE `orders` ADD COLUMN `payment_status` VARCHAR(255) NOT NULL DEFAULT 'pending' AFTER `tracking_number`",
            'paid_at' => "ALTER TABLE `orders` ADD COLUMN `paid_at` TIMESTAMP NULL AFTER `payment_status`"
        ];
        
        foreach ($columns as $colName => $sql) {
            try {
                if (!Schema::hasColumn('orders', $colName)) {
                    \DB::statement($sql);
                }
            } catch (\Exception $e) {
                // Column might already exist, continue
                if (!str_contains($e->getMessage(), 'Duplicate column name')) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('orders', 'tracking_number')) {
                $table->dropColumn('tracking_number');
            }
            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('orders', 'shipping_service')) {
                $table->dropColumn('shipping_service');
            }
        });
    }
};
