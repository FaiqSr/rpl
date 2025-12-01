<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_comments', function (Blueprint $table) {
            $table->uuid('comment_id')->primary();
            $table->uuid('article_id');
            $table->uuid('user_id');
            $table->uuid('parent_id')->nullable(); // Untuk balasan komentar
            $table->text('content');
            $table->timestamps();
            
            $table->foreign('article_id')->references('article_id')->on('articles')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('comment_id')->on('article_comments')->onDelete('cascade');
            $table->index(['article_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_comments');
    }
};
