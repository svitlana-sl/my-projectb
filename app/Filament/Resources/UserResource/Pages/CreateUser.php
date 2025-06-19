<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Handle avatar file upload if present
        $avatarFile = $data['avatar_file'] ?? null;
        unset($data['avatar_file']);
        
        // Create the user first
        $record = static::getModel()::create($data);
        
        // Handle avatar upload after user creation
        if ($avatarFile) {
            try {
                // Convert Filament temp path to UploadedFile
                $uploadedFile = $this->createUploadedFileFromFilament($avatarFile);
                
                if ($uploadedFile) {
                    [$directory, $thumbWidth, $thumbHeight] = $record->getUploadConfig('avatar_path');
                    $filePaths = $record->uploadFile(
                        $uploadedFile,
                        $directory,
                        'avatar_path',
                        'avatar_thumb_path',
                        $thumbWidth,
                        $thumbHeight
                    );
                    
                    $record->update($filePaths);
                }
            } catch (\Exception $e) {
                \Log::error("Avatar upload failed: {$e->getMessage()}");
            }
        }
        
        return $record;
    }
    
    /**
     * Create UploadedFile from Filament temp path
     */
    private function createUploadedFileFromFilament(string $tempPath): ?UploadedFile
    {
        try {
            $disk = config('image.storage.disk', 'public');
            
            if (!Storage::disk($disk)->exists($tempPath)) {
                return null;
            }
            
            $originalName = basename($tempPath);
            $mimeType = Storage::disk($disk)->mimeType($tempPath);
            
            if ($disk === 'do_spaces') {
                // For DigitalOcean Spaces, download to local temp
                $fileContent = Storage::disk($disk)->get($tempPath);
                $localTempPath = sys_get_temp_dir() . '/' . $originalName;
                file_put_contents($localTempPath, $fileContent);
                
                return new UploadedFile($localTempPath, $originalName, $mimeType, null, true);
            }
            
            // For local storage
            $fullPath = Storage::disk($disk)->path($tempPath);
            return new UploadedFile($fullPath, $originalName, $mimeType, null, true);
            
        } catch (\Exception $e) {
            \Log::error("Failed to create UploadedFile from Filament: {$e->getMessage()}");
            return null;
        }
    }
}
