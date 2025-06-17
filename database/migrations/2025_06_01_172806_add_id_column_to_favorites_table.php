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
            // Step 1: Create temporary table with correct structure
            Schema::create('favorites_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('sitter_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('created_at')->useCurrent();
                
                // Add unique constraint
                $table->unique(['owner_id', 'sitter_id'], 'favorites_temp_owner_sitter_unique');
            });
            
            // Step 2: Copy data from old table to new table
            $existingRecords = DB::table('favorites')->get();
            foreach ($existingRecords as $record) {
                DB::table('favorites_temp')->insert([
                    'owner_id' => $record->owner_id,
                    'sitter_id' => $record->sitter_id,
                    'created_at' => $record->created_at,
                ]);
            }
            
            // Step 3: Drop old table
            Schema::dropIfExists('favorites');
            
            // Step 4: Rename temp table to favorites
            Schema::rename('favorites_temp', 'favorites');
        }
        
        // Ensure unique constraint exists with correct name
        $indexes = DB::select("SHOW INDEX FROM favorites WHERE Key_name = 'favorites_owner_sitter_unique'");
        if (empty($indexes)) {
            // Check if temp constraint exists and drop it safely
            $tempIndexes = DB::select("SHOW INDEX FROM favorites WHERE Key_name = 'favorites_temp_owner_sitter_unique'");
            if (!empty($tempIndexes)) {
                // First check if there are any foreign keys using this index
                try {
                    DB::statement('ALTER TABLE favorites DROP INDEX favorites_temp_owner_sitter_unique');
                } catch (\Exception $e) {
                    // If dropping fails due to foreign key constraint, skip it
                    // The constraint will be handled differently
                }
            }
            
            // Add the correct unique constraint only if it doesn't exist
            try {
                Schema::table('favorites', function (Blueprint $table) {
                    $table->unique(['owner_id', 'sitter_id'], 'favorites_owner_sitter_unique');
                });
            } catch (\Exception $e) {
                // Constraint might already exist, ignore
            }
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
