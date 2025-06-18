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
        // Log incoming data for debugging
        \Log::info('CreatePet handleRecordCreation called with data: ' . json_encode($data));
        \Log::info('Available data keys: ' . implode(', ', array_keys($data)));
        
        // Remove photo_file from data as it's not a database field
        $photoFile = $data['photo_file'] ?? null;
        unset($data['photo_file']);
        
        \Log::info('Photo file from form: ' . ($photoFile ?? 'NULL'));
        \Log::info('Photo file type: ' . gettype($photoFile));
        
        // Create the pet first
        $record = static::getModel()::create($data);
        \Log::info('Pet created with ID: ' . $record->id);
        
        // Handle photo file upload after pet creation
        if ($photoFile) {
            try {
                $disk = config('image.storage.disk', 'public');
                
                // Get full path to uploaded file
                $fullPath = Storage::disk($disk)->path($photoFile);
                \Log::info('Looking for file at: ' . $fullPath);
                
                if (Storage::disk($disk)->exists($photoFile)) {
                    \Log::info('File exists, processing upload');
                    
                    // Get original filename and extension
                    $originalName = basename($photoFile);
                    
                    // Create UploadedFile instance
                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $fullPath,
                        $originalName,
                        Storage::disk($disk)->mimeType($photoFile),
                        null,
                        true
                    );
                    
                    // Process the file upload
                    $record->uploadPhoto($uploadedFile);
                    \Log::info('Photo upload completed');
                    
                    // Clean up temp file
                    Storage::disk($disk)->delete($photoFile);
                    \Log::info('Temp file cleaned up');
                } else {
                    \Log::error('File not found at: ' . $fullPath);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the creation
                \Log::error('Pet photo upload failed: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        } else {
            \Log::info('No photo file provided');
        }
        
        return $record;
    }
}
