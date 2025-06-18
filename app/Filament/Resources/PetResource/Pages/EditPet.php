<?php

namespace App\Filament\Resources\PetResource\Pages;

use App\Filament\Resources\PetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
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
        // Handle photo file upload using simplified method
        if (isset($data['photo_file']) && $data['photo_file']) {
            $record->handleFilamentUpload($data['photo_file'], 'photo_path', 'photo_thumb_path');
        }
        
        // Remove photo_file from data as it's not a database field
        unset($data['photo_file']);
        
        // Update other fields
        $record->update($data);
        
        return $record;
    }
}
