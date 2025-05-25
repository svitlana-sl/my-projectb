<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['sitter_id']);
        });
        
        Schema::table('favorites', function (Blueprint $table) {
            // Drop the existing primary key
            $table->dropPrimary(['owner_id', 'sitter_id']);
            
            // Add auto-increment ID as primary key
            $table->id()->first();
            
            // Keep the unique constraint for owner_id + sitter_id
            $table->unique(['owner_id', 'sitter_id']);
        });
        
        Schema::table('favorites', function (Blueprint $table) {
            // Re-add foreign key constraints
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sitter_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['sitter_id']);
            
            // Drop the ID column
            $table->dropColumn('id');
            
            // Drop the unique constraint
            $table->dropUnique(['owner_id', 'sitter_id']);
            
            // Restore the composite primary key
            $table->primary(['owner_id', 'sitter_id']);
            
            // Re-add foreign keys
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sitter_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
