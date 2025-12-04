<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change buyer_unread_count and seller_unread_count from boolean to integer
        Schema::table('chats', function (Blueprint $table) {
            $table->integer('buyer_unread_count')->default(0)->change();
            $table->integer('seller_unread_count')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->boolean('buyer_unread_count')->default(0)->change();
            $table->boolean('seller_unread_count')->default(0)->change();
        });
    }
};
