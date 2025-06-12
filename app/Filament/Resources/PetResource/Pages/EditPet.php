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
        // Handle photo file upload
        if (isset($data['photo_file']) && $data['photo_file']) {
            try {
                // Get full path to uploaded file
                $fullPath = Storage::disk('public')->path($data['photo_file']);
                
                if (file_exists($fullPath)) {
                    // Get original filename
                    $originalName = basename($data['photo_file']);
                    
                    // Create UploadedFile instance
                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $fullPath,
                        $originalName,
                        Storage::disk('public')->mimeType($data['photo_file']),
                        null,
                        true
                    );
                    
                    // Process the file upload
                    $record->uploadPhoto($uploadedFile);
                    
                    // Clean up temp file
                    Storage::disk('public')->delete($data['photo_file']);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the update
                \Log::error('Pet photo update failed: ' . $e->getMessage());
            }
        }
        
        // Remove photo_file from data as it's not a database field
        unset($data['photo_file']);
        
        // Update other fields
        $record->update($data);
        
        return $record;
    }
}
