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
        // Check current state of favorites table and fix it
        $columns = Schema::getColumnListing('favorites');
        
        // If table has ID column but no unique constraint, add it
        if (in_array('id', $columns)) {
            // Check if unique constraint exists
            $indexes = DB::select("SHOW INDEX FROM favorites WHERE Key_name = 'favorites_owner_sitter_unique'");
            
            if (empty($indexes)) {
                Schema::table('favorites', function (Blueprint $table) {
                    $table->unique(['owner_id', 'sitter_id'], 'favorites_owner_sitter_unique');
                });
            }
        } else {
            // If no ID column exists, add it properly
            // First check if we have composite primary key
            $primaryKeys = DB::select("SHOW INDEX FROM favorites WHERE Key_name = 'PRIMARY'");
            
            if (!empty($primaryKeys)) {
                // We have a primary key, need to work around it
                Schema::table('favorites', function (Blueprint $table) {
                    $table->bigIncrements('temp_id')->first();
                });
                
                // Drop the composite primary key and set new one
                DB::statement('ALTER TABLE favorites DROP PRIMARY KEY');
                DB::statement('ALTER TABLE favorites CHANGE temp_id id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
                
                // Add unique constraint
                Schema::table('favorites', function (Blueprint $table) {
                    $table->unique(['owner_id', 'sitter_id'], 'favorites_owner_sitter_unique');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is designed to fix broken state, 
        // rollback would be dangerous in production
        // Leave empty to prevent accidental rollback
    }
};
