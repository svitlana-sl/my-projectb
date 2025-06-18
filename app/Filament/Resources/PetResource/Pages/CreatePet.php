<?php

namespace App\Filament\Resources\PetResource\Pages;

use App\Filament\Resources\PetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreatePet extends CreateRecord
{
    protected static string $resource = PetResource::class;
    
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Handle photo file upload using simplified method
        $photoFile = $data['photo_file'] ?? null;
        unset($data['photo_file']);
        
        // Create the pet first
        $record = static::getModel()::create($data);
        
        // Handle photo upload after pet creation
        if ($photoFile) {
            $record->handleFilamentUpload($photoFile, 'photo_path', 'photo_thumb_path');
        }
        
        return $record;
    }
}
