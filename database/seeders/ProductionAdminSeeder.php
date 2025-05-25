<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductionAdminSeeder extends Seeder
{
    /**
     * Run the database seeds for production environment.
     */
    public function run(): void
    {
        // Only run in production
        if (!app()->environment('production')) {
            $this->command->info('Skipping ProductionAdminSeeder - not in production environment');
            return;
        }

        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        
        if (!$email) {
            $this->command->error('ADMIN_EMAIL environment variable is required');
            return;
        }
        
        if (!$password) {
            $password = Str::random(16);
            $this->command->warn("No ADMIN_PASSWORD set, generated random password: {$password}");
            $this->command->warn("SAVE THIS PASSWORD! It won't be shown again.");
        }
        
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Production Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command->info("Admin user created: {$email}");
            if (env('ADMIN_PASSWORD')) {
                $this->command->info("Using password from ADMIN_PASSWORD environment variable");
            }
        } else {
            $this->command->info("Admin user already exists: {$email}");
        }
    }
} 