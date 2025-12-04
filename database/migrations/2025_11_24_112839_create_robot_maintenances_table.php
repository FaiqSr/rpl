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
        Schema::create('robot_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('robot_id');
            $table->enum('maintenance_type', ['routine', 'repair', 'cleaning', 'parts_replacement', 'inspection']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('technician_name')->nullable();
            $table->text('notes')->nullable();
            $table->json('parts_replaced')->nullable(); // [{"name": "Filter", "cost": 50000}]
            $table->integer('next_service_days')->nullable(); // Hari sampai service berikutnya
            $table->timestamps();
            
            $table->index(['robot_id', 'scheduled_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robot_maintenances');
    }
};
