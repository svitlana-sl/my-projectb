<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Handle avatar file upload if present and different from existing
        if (isset($data['avatar_file']) && !empty($data['avatar_file'])) {
            // Get first file from array if it's an array
            $avatarFile = is_array($data['avatar_file']) ? reset($data['avatar_file']) : $data['avatar_file'];
            
            // Check if this is a new upload (not the same as current avatar_path)
            if ($avatarFile && $avatarFile !== $record->avatar_path) {
                try {
                    // Delete old avatar files only if uploading new one
                    $record->deleteOldAvatar();
                    
                    // Convert Filament temp path to UploadedFile
                    $uploadedFile = $this->createUploadedFileFromFilament($avatarFile);
                    
                    if ($uploadedFile) {
                        // Upload new avatar
                        [$directory, $thumbWidth, $thumbHeight] = $record->getUploadConfig('avatar_path');
                        $filePaths = $record->uploadFile(
                            $uploadedFile,
                            $directory,
                            'avatar_path',
                            'avatar_thumb_path',
                            $thumbWidth,
                            $thumbHeight
                        );
                        
                        // Add file paths to data
                        $data = array_merge($data, $filePaths);
                    }
                } catch (\Exception $e) {
                    \Log::error("Avatar upload failed: {$e->getMessage()}");
                }
            }
        }
        
        // Remove avatar_file from data as it's not a database field
        unset($data['avatar_file']);
        
        // Update other fields
        $record->update($data);
        
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
