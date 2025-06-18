<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Pet;

class FixFilePermissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:fix-permissions';

    /**
     * The console command description.
     */
    protected $description = 'Fix file permissions for uploaded files in DigitalOcean Spaces';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = config('image.storage.disk', 'public');
        
        if ($disk !== 'do_spaces') {
            $this->info('Not using DigitalOcean Spaces, skipping...');
            return;
        }

        $this->info('Fixing file permissions for DigitalOcean Spaces...');
        
        // Fix user avatars
        $this->info('Processing user avatars...');
        $users = User::whereNotNull('avatar_path')
                    ->orWhereNotNull('avatar_thumb_path')
                    ->get();
        
        foreach ($users as $user) {
            $this->fixFilePermission($disk, $user->avatar_path, "User {$user->name} avatar");
            $this->fixFilePermission($disk, $user->avatar_thumb_path, "User {$user->name} avatar thumbnail");
        }
        
        // Fix pet photos
        $this->info('Processing pet photos...');
        $pets = Pet::whereNotNull('photo_path')
                   ->orWhereNotNull('photo_thumb_path')
                   ->get();
        
        foreach ($pets as $pet) {
            $this->fixFilePermission($disk, $pet->photo_path, "Pet {$pet->name} photo");
            $this->fixFilePermission($disk, $pet->photo_thumb_path, "Pet {$pet->name} photo thumbnail");
        }
        
        $this->info('File permissions fixed successfully!');
    }
    
    /**
     * Fix permission for a single file
     */
    private function fixFilePermission(string $disk, ?string $filePath, string $description): void
    {
        if (!$filePath) {
            return;
        }
        
        try {
            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->setVisibility($filePath, 'public');
                $this->line("✅ Fixed: {$description} - {$filePath}");
            } else {
                $this->warn("⚠️  File not found: {$description} - {$filePath}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Failed to fix: {$description} - {$filePath}: " . $e->getMessage());
        }
    }
} 