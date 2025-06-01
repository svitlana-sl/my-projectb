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
        // Check current state and add ID column if it doesn't exist
        $columns = Schema::getColumnListing('favorites');
        
        if (!in_array('id', $columns)) {
            // Step 1: Add simple bigint column first (no auto-increment, no primary key)
            Schema::table('favorites', function (Blueprint $table) {
                $table->unsignedBigInteger('temp_id')->nullable()->first();
            });
            
            // Step 2: Populate the temp_id with sequential values for existing records
            $existingRecords = DB::table('favorites')->count();
            if ($existingRecords > 0) {
                DB::statement('SET @row_number = 0');
                DB::statement('UPDATE favorites SET temp_id = (@row_number := @row_number + 1)');
            }
            
            // Step 3: Drop the existing composite primary key (owner_id, sitter_id)
            DB::statement('ALTER TABLE favorites DROP PRIMARY KEY');
            
            // Step 4: Modify temp_id to be auto-increment primary key and rename to id
            DB::statement('ALTER TABLE favorites CHANGE temp_id id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        }
        
        // Ensure unique constraint exists (should be added by emergency migration)
        $indexes = DB::select("SHOW INDEX FROM favorites WHERE Key_name = 'favorites_owner_sitter_unique'");
        if (empty($indexes)) {
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
        // Check if ID column exists before trying to drop it
        if (Schema::hasColumn('favorites', 'id')) {
            Schema::table('favorites', function (Blueprint $table) {
                // Drop unique constraint if it exists
                try {
                    $table->dropUnique('favorites_owner_sitter_unique');
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
                
                // Drop the ID column
                $table->dropColumn('id');
            });
            
            // Restore the composite primary key
            Schema::table('favorites', function (Blueprint $table) {
                $table->primary(['owner_id', 'sitter_id']);
            });
        }
    }
};
