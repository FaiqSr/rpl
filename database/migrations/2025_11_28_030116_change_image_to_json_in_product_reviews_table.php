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
        // First, convert existing string values to JSON array format
        \DB::table('product_reviews')->whereNotNull('image')->get()->each(function($review) {
            $image = $review->image;
            if ($image && !empty($image)) {
                // If it's already JSON, skip
                $decoded = json_decode($image, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return;
                }
                // Convert string to JSON array
                \DB::table('product_reviews')
                    ->where('review_id', $review->review_id)
                    ->update(['image' => json_encode([$image])]);
            }
        });
        
        Schema::table('product_reviews', function (Blueprint $table) {
            // Change image column from string to JSON to support multiple images
            $table->json('image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert JSON array back to string (take first image)
        \DB::table('product_reviews')->whereNotNull('image')->get()->each(function($review) {
            $image = $review->image;
            if ($image) {
                $decoded = json_decode($image, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) > 0) {
                    \DB::table('product_reviews')
                        ->where('review_id', $review->review_id)
                        ->update(['image' => $decoded[0]]);
                }
            }
        });
        
        Schema::table('product_reviews', function (Blueprint $table) {
            // Revert back to string
            $table->string('image')->nullable()->change();
        });
    }
};
