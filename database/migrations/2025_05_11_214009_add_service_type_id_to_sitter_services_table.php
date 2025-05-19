<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// This migration adds the service_type_id foreign key to the sitter_services table
return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds the service_type_id column as a foreign key to the sitter_services table.
     */
    public function up(): void
    {
        Schema::table('sitter_services', function (Blueprint $table) {
            // Add a nullable foreign key column referencing the service_types table
            $table->foreignId('service_type_id')
                  ->nullable()
                  ->constrained('service_types')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     * Removes the service_type_id column and its foreign key constraint from the sitter_services table.
     */
    public function down(): void
    {
        Schema::table('sitter_services', function (Blueprint $table) {
            // Drop the foreign key constraint and the column
            $table->dropForeign(['service_type_id']);
            $table->dropColumn('service_type_id');
        });
    }
};
