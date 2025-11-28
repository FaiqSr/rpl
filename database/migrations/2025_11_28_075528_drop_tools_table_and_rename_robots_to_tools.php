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
        // 1. Drop foreign key constraints first, then drop old tools table if exists
        if (Schema::hasTable('tool_details')) {
            Schema::table('tool_details', function (Blueprint $table) {
                $table->dropForeign(['tool_code']);
            });
            Schema::dropIfExists('tool_details');
        }
        
        if (Schema::hasTable('tools_details')) {
            Schema::table('tools_details', function (Blueprint $table) {
                $table->dropForeign(['tool_code']);
            });
            Schema::dropIfExists('tools_details');
        }
        
        Schema::dropIfExists('tools');
        
        // 2. Rename robots table to tools
        if (Schema::hasTable('robots')) {
            Schema::rename('robots', 'tools');
        }
        
        // 3. Rename robot_id column to tool_id in tools table
        if (Schema::hasTable('tools')) {
            // Check if robot_id column exists and has unique constraint
            $indexes = DB::select("SHOW INDEXES FROM tools WHERE Column_name = 'robot_id'");
            $hasUnique = false;
            foreach ($indexes as $index) {
                if ($index->Non_unique == 0) {
                    $hasUnique = true;
                    DB::statement("ALTER TABLE tools DROP INDEX {$index->Key_name}");
                    break;
                }
            }
            
            // Rename column using raw SQL
            DB::statement('ALTER TABLE tools CHANGE robot_id tool_id VARCHAR(255)');
            
            // Re-add unique constraint if it existed
            if ($hasUnique) {
                Schema::table('tools', function (Blueprint $table) {
                    $table->unique('tool_id');
                });
            }
        }
        
        // 4. Rename robot_activities table to tool_activities
        if (Schema::hasTable('robot_activities')) {
            Schema::rename('robot_activities', 'tool_activities');
            
            // Drop index if exists, then rename column
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
            
            DB::statement('ALTER TABLE tool_activities CHANGE robot_id tool_id VARCHAR(255)');
            
            Schema::table('tool_activities', function (Blueprint $table) {
                $table->index(['tool_id', 'occurred_at']);
            });
        }
        
        // 5. Rename robot_maintenances table to tool_maintenances
        if (Schema::hasTable('robot_maintenances')) {
            Schema::rename('robot_maintenances', 'tool_maintenances');
            
            // Drop index if exists, then rename column
            $indexes = DB::select("SHOW INDEXES FROM tool_maintenances WHERE Column_name = 'robot_id'");
            foreach ($indexes as $index) {
                if (strpos($index->Key_name, 'robot_id') !== false || strpos($index->Key_name, 'scheduled_date') !== false) {
                    try {
                        DB::statement("ALTER TABLE tool_maintenances DROP INDEX {$index->Key_name}");
                    } catch (\Exception $e) {
                        // Index might not exist, continue
                    }
                }
            }
            
            DB::statement('ALTER TABLE tool_maintenances CHANGE robot_id tool_id VARCHAR(255)');
            
            Schema::table('tool_maintenances', function (Blueprint $table) {
                $table->index(['tool_id', 'scheduled_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: rename tools back to robots
        if (Schema::hasTable('tools')) {
            // Rename tool_id back to robot_id
            Schema::table('tools', function (Blueprint $table) {
                $table->dropUnique(['tool_id']);
            });
            
            DB::statement('ALTER TABLE tools CHANGE tool_id robot_id VARCHAR(255)');
            
            Schema::table('tools', function (Blueprint $table) {
                $table->unique('robot_id');
            });
            
            Schema::rename('tools', 'robots');
        }
        
        // Rename tool_activities back to robot_activities
        if (Schema::hasTable('tool_activities')) {
            Schema::table('tool_activities', function (Blueprint $table) {
                $table->dropIndex(['tool_id', 'occurred_at']);
            });
            
            DB::statement('ALTER TABLE tool_activities CHANGE tool_id robot_id VARCHAR(255)');
            
            Schema::table('tool_activities', function (Blueprint $table) {
                $table->index(['robot_id', 'occurred_at']);
            });
            
            Schema::rename('tool_activities', 'robot_activities');
        }
        
        // Rename tool_maintenances back to robot_maintenances
        if (Schema::hasTable('tool_maintenances')) {
            Schema::table('tool_maintenances', function (Blueprint $table) {
                $table->dropIndex(['tool_id', 'scheduled_date']);
            });
            
            DB::statement('ALTER TABLE tool_maintenances CHANGE tool_id robot_id VARCHAR(255)');
            
            Schema::table('tool_maintenances', function (Blueprint $table) {
                $table->index(['robot_id', 'scheduled_date']);
            });
            
            Schema::rename('tool_maintenances', 'robot_maintenances');
        }
    }
};
