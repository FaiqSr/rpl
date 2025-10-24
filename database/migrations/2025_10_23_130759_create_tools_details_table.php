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
        Schema::create('tool_details', function (Blueprint $table) {
            $table->uuid('tool_detail_id')->primary();
            $table->foreignUuid('tool_code')->constrained('tools', 'tool_code')->onDelete('cascade');
            $table->string('serial_number')->unique();
            $table->string('condition')->default('good');
            $table->date('purchase_date')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('location')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools_details');
    }
};
