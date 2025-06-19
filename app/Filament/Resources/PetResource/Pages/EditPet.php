<?php

namespace App\Filament\Resources\PetResource\Pages;

use App\Filament\Resources\PetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EditPet extends EditRecord
{
    protected static string $resource = PetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Handle photo file upload if present
        if (isset($data['photo_file']) && $data['photo_file']) {
            try {
                // Delete old photo files
                $record->deleteOldPhoto();
                
                // Convert Filament temp path to UploadedFile
                $uploadedFile = $this->createUploadedFileFromFilament($data['photo_file']);
                
                if ($uploadedFile) {
                    // Upload new photo
                    [$directory, $thumbWidth, $thumbHeight] = $record->getUploadConfig('photo_path');
                    $filePaths = $record->uploadFile(
                        $uploadedFile,
                        $directory,
                        'photo_path',
                        'photo_thumb_path',
                        $thumbWidth,
                        $thumbHeight
                    );
                    
                    // Add file paths to data
                    $data = array_merge($data, $filePaths);
                }
            } catch (\Exception $e) {
                \Log::error("Photo upload failed: {$e->getMessage()}");
            }
        }
        
        // Remove photo_file from data as it's not a database field
        unset($data['photo_file']);
        
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
