<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Diagnose current table structure
        echo "\n=== FAVORITES TABLE DIAGNOSIS ===\n";
        
        // Check if table exists
        if (!Schema::hasTable('favorites')) {
            echo "ERROR: favorites table does not exist!\n";
            return;
        }
        
        // Get columns
        $columns = Schema::getColumnListing('favorites');
        echo "Columns: " . implode(', ', $columns) . "\n";
        
        // Get detailed column info
        $columnDetails = DB::select("DESCRIBE favorites");
        echo "\nColumn Details:\n";
        foreach ($columnDetails as $column) {
            echo "- {$column->Field}: {$column->Type} | Key: {$column->Key} | Extra: {$column->Extra}\n";
        }
        
        // Get indexes
        $indexes = DB::select("SHOW INDEX FROM favorites");
        echo "\nIndexes:\n";
        foreach ($indexes as $index) {
            echo "- {$index->Key_name}: {$index->Column_name} | Unique: {$index->Non_unique}\n";
        }
        
        // Check primary keys specifically
        $primaryKeys = collect($indexes)->where('Key_name', 'PRIMARY')->pluck('Column_name')->toArray();
        echo "\nPrimary Keys: " . implode(', ', $primaryKeys) . "\n";
        
        // Check if unique constraint exists
        $uniqueConstraints = collect($indexes)->where('Key_name', 'favorites_owner_sitter_unique');
        echo "Unique constraint exists: " . ($uniqueConstraints->isNotEmpty() ? 'YES' : 'NO') . "\n";
        
        echo "=== END DIAGNOSIS ===\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is just diagnostic, no rollback needed
    }
};
