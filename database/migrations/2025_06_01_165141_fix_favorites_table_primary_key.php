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
        // Check if the table already has an 'id' column
        if (!Schema::hasColumn('favorites', 'id')) {
            // Step 1: Add ID column as auto-increment but not primary key yet
            Schema::table('favorites', function (Blueprint $table) {
                $table->bigIncrements('temp_id')->first();
            });
            
            // Step 2: Rename temp_id to id and make it the primary key
            // First, we need to drop the existing composite primary key
            DB::statement('ALTER TABLE favorites DROP PRIMARY KEY');
            
            // Rename the column and set it as primary key
            DB::statement('ALTER TABLE favorites CHANGE temp_id id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
            
            // Step 3: Add unique constraint for the original composite key
            Schema::table('favorites', function (Blueprint $table) {
                $table->unique(['owner_id', 'sitter_id'], 'favorites_owner_sitter_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('favorites', 'id')) {
            Schema::table('favorites', function (Blueprint $table) {
                // Drop the unique constraint
                $table->dropUnique('favorites_owner_sitter_unique');
                
                // Drop the ID primary key
                $table->dropColumn('id');
            });
            
            // Restore the composite primary key
            Schema::table('favorites', function (Blueprint $table) {
                $table->primary(['owner_id', 'sitter_id']);
            });
        }
    }
};
