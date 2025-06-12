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
        Schema::table('pets', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('photo_url')->comment('Path to pet photo file');
            $table->string('photo_thumb_path')->nullable()->after('photo_path')->comment('Path to pet photo thumbnail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'photo_thumb_path']);
        });
    }
};
