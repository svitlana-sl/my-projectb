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
        // Update the enum to include 'completed' status
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending' COMMENT 'enum(''pending'',''accepted'',''rejected'',''completed'')'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'completed' status from enum
        // First update any 'completed' records to 'accepted' to avoid constraint violation
        DB::statement("UPDATE service_requests SET status = 'accepted' WHERE status = 'completed'");
        
        // Then modify the enum back to original values
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending' COMMENT 'enum(''pending'',''accepted'',''rejected'')'");
    }
};
