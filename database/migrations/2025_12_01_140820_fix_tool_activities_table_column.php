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
        // Check if tool_activities table exists
        if (!Schema::hasTable('tool_activities')) {
            // If table doesn't exist, create it
            Schema::create('tool_activities', function (Blueprint $table) {
                $table->id();
                $table->string('tool_id');
                $table->enum('activity_type', ['patrol_start', 'patrol_end', 'charging_start', 'charging_end', 'maintenance', 'error', 'position_update']);
                $table->string('description')->nullable();
                $table->string('position')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();
                
                $table->index(['tool_id', 'occurred_at']);
            });
        } else {
            // Table exists, check if it has robot_id column
            $columns = Schema::getColumnListing('tool_activities');
            
            if (in_array('robot_id', $columns) && !in_array('tool_id', $columns)) {
                // Drop index if exists
                try {
                    $indexes = DB::select("SHOW INDEXES FROM tool_activities WHERE Column_name = 'robot_id'");
                    foreach ($indexes as $index) {
                        if (strpos($index->Key_name, 'robot_id') !== false || strpos($index->Key_name, 'occurred_at') !== false) {
                            try {
                                DB::statement("ALTER TABLE tool_activities DROP INDEX {$index->Key_name}");
                            } catch (\Exception $e) {
                                // Index might not exist, continue
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Continue if error
                }
                
                // Rename robot_id to tool_id
                DB::statement('ALTER TABLE tool_activities CHANGE robot_id tool_id VARCHAR(255)');
                
                // Re-add index
                Schema::table('tool_activities', function (Blueprint $table) {
                    $table->index(['tool_id', 'occurred_at']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: reverse the changes if needed
        if (Schema::hasTable('tool_activities')) {
            $columns = Schema::getColumnListing('tool_activities');
            
            if (in_array('tool_id', $columns) && !in_array('robot_id', $columns)) {
                try {
                    $indexes = DB::select("SHOW INDEXES FROM tool_activities WHERE Column_name = 'tool_id'");
                    foreach ($indexes as $index) {
                        if (strpos($index->Key_name, 'tool_id') !== false || strpos($index->Key_name, 'occurred_at') !== false) {
                            try {
                                DB::statement("ALTER TABLE tool_activities DROP INDEX {$index->Key_name}");
                            } catch (\Exception $e) {
                                // Index might not exist, continue
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Continue if error
                }
                
                DB::statement('ALTER TABLE tool_activities CHANGE tool_id robot_id VARCHAR(255)');
                
                Schema::table('tool_activities', function (Blueprint $table) {
                    $table->index(['robot_id', 'occurred_at']);
                });
            }
        }
    }
};
