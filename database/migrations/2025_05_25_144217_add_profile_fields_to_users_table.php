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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'sitter', 'both', 'admin'])->default('owner')->after('password');
            $table->string('avatar_url')->nullable()->after('role')->comment('URL of user\'s avatar');
            $table->string('address_line')->nullable()->after('avatar_url')->comment('street and house number');
            $table->string('city')->nullable()->after('address_line');
            $table->string('postal_code')->nullable()->after('city');
            $table->string('country')->nullable()->after('postal_code');
            $table->decimal('latitude', 10, 7)->nullable()->after('country')->comment('geographical coordinate for map');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude')->comment('geographical coordinate for map');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'avatar_url',
                'address_line',
                'city',
                'postal_code',
                'country',
                'latitude',
                'longitude'
            ]);
        });
    }
};
