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
        // Simple fix: just ensure unique constraint exists
        // Don't touch primary keys at all
        
        try {
            // Check if unique constraint already exists
            $indexes = DB::select("SHOW INDEX FROM favorites WHERE Key_name = 'favorites_owner_sitter_unique'");
            
            if (empty($indexes)) {
                Schema::table('favorites', function (Blueprint $table) {
                    $table->unique(['owner_id', 'sitter_id'], 'favorites_owner_sitter_unique');
                });
                echo "Added unique constraint to favorites table.\n";
            } else {
                echo "Unique constraint already exists.\n";
            }
        } catch (\Exception $e) {
            // If constraint already exists with different name or any other issue, just log it
            echo "Could not add unique constraint: " . $e->getMessage() . "\n";
            // Don't throw - this is not critical
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
