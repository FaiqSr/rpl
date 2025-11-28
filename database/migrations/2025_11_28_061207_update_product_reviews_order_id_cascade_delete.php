<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['order_id']);
            
            // Drop unique constraint that includes order_id
            $table->dropUnique(['product_id', 'user_id', 'order_id']);
        });
        
        Schema::table('product_reviews', function (Blueprint $table) {
            // Recreate foreign key with cascade delete
            $table->foreign('order_id')
                  ->references('order_id')
                  ->on('orders')
                  ->onDelete('cascade');
            
            // Recreate unique constraint (allows multiple NULL values for order_id)
            $table->unique(['product_id', 'user_id', 'order_id'], 'product_reviews_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // Drop cascade foreign key
            $table->dropForeign(['order_id']);
            
            // Drop unique constraint
            $table->dropUnique('product_reviews_unique');
        });
        
        Schema::table('product_reviews', function (Blueprint $table) {
            // Restore original foreign key with set null
            $table->foreign('order_id')
                  ->references('order_id')
                  ->on('orders')
                  ->onDelete('set null');
            
            // Restore original unique constraint
            $table->unique(['product_id', 'user_id', 'order_id']);
        });
    }
};
