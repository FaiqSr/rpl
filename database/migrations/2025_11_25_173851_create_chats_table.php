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
        Schema::create('chats', function (Blueprint $table) {
            $table->uuid('chat_id')->primary();
            $table->foreignUuid('buyer_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignUuid('seller_id')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->foreignUuid('order_id')->nullable()->constrained('orders', 'order_id')->onDelete('set null');
            $table->string('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->integer('buyer_unread_count')->default(0);
            $table->integer('seller_unread_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
