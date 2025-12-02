<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function(Blueprint $table){
            if (!Schema::hasColumn('users','phone')) {
                $table->string('phone',30)->nullable()->after('role');
            }
            if (!Schema::hasColumn('users','address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });
    }
    public function down(): void {
        Schema::table('users', function(Blueprint $table){
            if (Schema::hasColumn('users','address')) $table->dropColumn('address');
            if (Schema::hasColumn('users','phone')) $table->dropColumn('phone');
        });
    }
};