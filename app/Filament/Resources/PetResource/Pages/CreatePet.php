<?php

namespace App\Filament\Resources\PetResource\Pages;

use App\Filament\Resources\PetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreatePet extends CreateRecord
{
    protected static string $resource = PetResource::class;
    
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Handle photo file upload if present
        $photoFile = $data['photo_file'] ?? null;
        unset($data['photo_file']);
        
        // Create the pet first
        $record = static::getModel()::create($data);
        
        // Handle photo upload after pet creation
        if ($photoFile) {
            try {
                // Convert Filament temp path to UploadedFile
                $uploadedFile = $this->createUploadedFileFromFilament($photoFile);
                
                if ($uploadedFile) {
                    [$directory, $thumbWidth, $thumbHeight] = $record->getUploadConfig('photo_path');
                    $filePaths = $record->uploadFile(
                        $uploadedFile,
                        $directory,
                        'photo_path',
                        'photo_thumb_path',
                        $thumbWidth,
                        $thumbHeight
                    );
                    
                    $record->update($filePaths);
                }
            } catch (\Exception $e) {
                \Log::error("Photo upload failed: {$e->getMessage()}");
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
