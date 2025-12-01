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
        Schema::create('article_article_category', function (Blueprint $table) {
            $table->uuid('article_id');
            $table->uuid('category_id');
            $table->timestamps();
            
            $table->foreign('article_id')->references('article_id')->on('articles')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('article_categories')->onDelete('cascade');
            $table->primary(['article_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_article_category');
    }
};
