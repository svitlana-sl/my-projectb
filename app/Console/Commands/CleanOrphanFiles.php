<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Pet;

class CleanOrphanFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:clean-orphans {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned files that no longer have corresponding database records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No files will be deleted');
        }
        
        $this->info('🧹 Starting orphan file cleanup...');
        
        $deletedCount = 0;
        $totalSize = 0;
        
        // Clean avatar files
        $deletedCount += $this->cleanAvatarFiles($dryRun, $totalSize);
        
        // Clean pet photo files
        $deletedCount += $this->cleanPetPhotoFiles($dryRun, $totalSize);
        
        // Clean temp files older than 24 hours
        $deletedCount += $this->cleanTempFiles($dryRun, $totalSize);
        
        $this->info("✅ Cleanup completed!");
        $this->info("📁 Files processed: {$deletedCount}");
        $this->info("💾 Space freed: " . $this->formatBytes($totalSize));
        
        if ($dryRun) {
            $this->warn('⚠️  This was a DRY RUN. Run without --dry-run to actually delete files.');
        }
    }
    
    private function cleanAvatarFiles(bool $dryRun, int &$totalSize): int
    {
        $this->info('🔍 Checking avatar files...');
        
        $deletedCount = 0;
        $validPaths = User::whereNotNull('avatar_path')
            ->pluck('avatar_path')
            ->merge(User::whereNotNull('avatar_thumb_path')->pluck('avatar_thumb_path'))
            ->filter()
            ->toArray();
        
        $avatarDirectories = Storage::disk('public')->directories('avatars');
        
        foreach ($avatarDirectories as $directory) {
            $files = Storage::disk('public')->files($directory);
            
            foreach ($files as $file) {
                if (!in_array($file, $validPaths)) {
                    $size = Storage::disk('public')->size($file);
                    $totalSize += $size;
                    
                    if ($dryRun) {
                        $this->line("Would delete: {$file} (" . $this->formatBytes($size) . ")");
                    } else {
                        Storage::disk('public')->delete($file);
                        $this->line("Deleted: {$file} (" . $this->formatBytes($size) . ")");
                    }
                    $deletedCount++;
                }
            }
            
            // Remove empty directories
            if (empty(Storage::disk('public')->files($directory)) && 
                empty(Storage::disk('public')->directories($directory))) {
                if ($dryRun) {
                    $this->line("Would delete empty directory: {$directory}");
                } else {
                    Storage::disk('public')->deleteDirectory($directory);
                    $this->line("Deleted empty directory: {$directory}");
                }
            }
        }
        
        return $deletedCount;
    }
    
    private function cleanPetPhotoFiles(bool $dryRun, int &$totalSize): int
    {
        $this->info('🔍 Checking pet photo files...');
        
        $deletedCount = 0;
        $validPaths = Pet::whereNotNull('photo_path')
            ->pluck('photo_path')
            ->merge(Pet::whereNotNull('photo_thumb_path')->pluck('photo_thumb_path'))
            ->filter()
            ->toArray();
        
        $petDirectories = Storage::disk('public')->directories('pets');
        
        foreach ($petDirectories as $directory) {
            $files = Storage::disk('public')->files($directory);
            
            foreach ($files as $file) {
                if (!in_array($file, $validPaths)) {
                    $size = Storage::disk('public')->size($file);
                    $totalSize += $size;
                    
                    if ($dryRun) {
                        $this->line("Would delete: {$file} (" . $this->formatBytes($size) . ")");
                    } else {
                        Storage::disk('public')->delete($file);
                        $this->line("Deleted: {$file} (" . $this->formatBytes($size) . ")");
                    }
                    $deletedCount++;
                }
            }
            
            // Remove empty directories
            if (empty(Storage::disk('public')->files($directory)) && 
                empty(Storage::disk('public')->directories($directory))) {
                if ($dryRun) {
                    $this->line("Would delete empty directory: {$directory}");
                } else {
                    Storage::disk('public')->deleteDirectory($directory);
                    $this->line("Deleted empty directory: {$directory}");
                }
            }
        }
        
        return $deletedCount;
    }
    
    private function cleanTempFiles(bool $dryRun, int &$totalSize): int
    {
        $this->info('🔍 Cleaning temp files older than 24 hours...');
        
        $deletedCount = 0;
        $cutoffTime = now()->subDay();
        
        $tempFiles = Storage::disk('public')->files('temp');
        
        foreach ($tempFiles as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);
            
            if ($lastModified < $cutoffTime->timestamp) {
                $size = Storage::disk('public')->size($file);
                $totalSize += $size;
                
                if ($dryRun) {
                    $this->line("Would delete old temp file: {$file} (" . $this->formatBytes($size) . ")");
                } else {
                    Storage::disk('public')->delete($file);
                    $this->line("Deleted old temp file: {$file} (" . $this->formatBytes($size) . ")");
                }
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }
}
