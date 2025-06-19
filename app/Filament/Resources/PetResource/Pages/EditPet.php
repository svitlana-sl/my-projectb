<?php

namespace App\Filament\Resources\PetResource\Pages;

use App\Filament\Resources\PetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
                
                // Upload new photo
                [$directory, $thumbWidth, $thumbHeight] = $record->getUploadConfig('photo_path');
                $filePaths = $record->uploadFile(
                    $data['photo_file'],
                    $directory,
                    'photo_path',
                    'photo_thumb_path',
                    $thumbWidth,
                    $thumbHeight
                );
                
                // Add file paths to data
                $data = array_merge($data, $filePaths);
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
}
