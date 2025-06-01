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
        // Get current table structure
        $columns = Schema::getColumnListing('favorites');
        $indexes = DB::select("SHOW INDEX FROM favorites");
        
        // Check if ID column exists
        $hasIdColumn = in_array('id', $columns);
        
        // Check what primary keys exist
        $primaryKeys = collect($indexes)->where('Key_name', 'PRIMARY')->pluck('Column_name')->toArray();
        
        // Check if unique constraint exists
        $hasUniqueConstraint = collect($indexes)->where('Key_name', 'favorites_owner_sitter_unique')->isNotEmpty();
        
        if ($hasIdColumn) {
            // ID column exists, just ensure we have the unique constraint
            if (!$hasUniqueConstraint) {
                try {
                    Schema::table('favorites', function (Blueprint $table) {
                        $table->unique(['owner_id', 'sitter_id'], 'favorites_owner_sitter_unique');
                    });
                } catch (\Exception $e) {
                    // If constraint already exists with different name, ignore
                    if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                        throw $e;
                    }
                }
            }
        } else {
            // ID column doesn't exist, need to add it
            if (in_array('owner_id', $primaryKeys) && in_array('sitter_id', $primaryKeys)) {
                // We have composite primary key, need to replace it
                Schema::table('favorites', function (Blueprint $table) {
                    $table->bigIncrements('temp_id')->first();
                });
                
                // Drop composite primary key and set new one
                DB::statement('ALTER TABLE favorites DROP PRIMARY KEY');
                DB::statement('ALTER TABLE favorites CHANGE temp_id id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
                
                // Add unique constraint
                Schema::table('favorites', function (Blueprint $table) {
                    $table->unique(['owner_id', 'sitter_id'], 'favorites_owner_sitter_unique');
                });
            } else {
                // No primary key or different structure, just add ID
                Schema::table('favorites', function (Blueprint $table) {
                    $table->id()->first();
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
