<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->uuid('review_id')->primary();
            $table->uuid('product_id');
            $table->uuid('user_id');
            $table->uuid('order_id')->nullable(); // Relasi ke order untuk validasi
            $table->integer('rating')->default(5); // 1-5 bintang
            $table->text('review')->nullable(); // Ulasan teks
            $table->timestamps();
            
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('set null');
            $table->unique(['product_id', 'user_id', 'order_id']); // Satu review per user per produk per order
            $table->index(['product_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
