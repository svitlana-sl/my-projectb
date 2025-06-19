<?php

namespace App\Filament\Resources\PetResource\Pages;

use App\Filament\Resources\PetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

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
                [$directory, $thumbWidth, $thumbHeight] = $record->getUploadConfig('photo_path');
                $filePaths = $record->uploadFile(
                    $photoFile,
                    $directory,
                    'photo_path',
                    'photo_thumb_path',
                    $thumbWidth,
                    $thumbHeight
                );
                
                $record->update($filePaths);
            } catch (\Exception $e) {
                \Log::error("Photo upload failed: {$e->getMessage()}");
            }
        }
        
        return $record;
    }
}
