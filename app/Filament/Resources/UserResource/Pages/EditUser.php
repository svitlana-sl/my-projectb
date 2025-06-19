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
        // Handle avatar file upload if present and different from current
        if (isset($data['avatar_file']) && $data['avatar_file']) {
            if ($this->isNewFileUpload($data['avatar_file'], $record->avatar_path)) {
                try {
                    // Delete old avatar files
                    $record->deleteOldAvatar();
                    
                    // Convert Filament temp path to UploadedFile
                    $uploadedFile = $this->createUploadedFileFromFilament($data['avatar_file']);
                    
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
     * Check if the uploaded file is new (not existing path)
     */
    private function isNewFileUpload(string $uploadedFile, ?string $existingPath): bool
    {
        return !str_starts_with($uploadedFile, $existingPath ?? '');
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
