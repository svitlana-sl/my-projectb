<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Log incoming data for debugging
        \Log::info('CreateUser handleRecordCreation called with data: ' . json_encode($data));
        \Log::info('Available data keys: ' . implode(', ', array_keys($data)));
        
        // Remove avatar_file from data as it's not a database field
        $avatarFile = $data['avatar_file'] ?? null;
        unset($data['avatar_file']);
        
        \Log::info('Avatar file from form: ' . ($avatarFile ?? 'NULL'));
        \Log::info('Avatar file type: ' . gettype($avatarFile));
        
        // Create the user first
        $record = static::getModel()::create($data);
        \Log::info('User created with ID: ' . $record->id);
        
        // Handle avatar file upload after user creation
        if ($avatarFile) {
            try {
                \Log::info('Processing avatar file upload');
                $fullPath = Storage::disk('public')->path($avatarFile);
                \Log::info('Looking for file at: ' . $fullPath);
                
                if (file_exists($fullPath)) {
                    \Log::info('File exists, processing upload');
                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $fullPath,
                        basename($avatarFile),
                        Storage::disk('public')->mimeType($avatarFile),
                        null,
                        true
                    );
                    
                    $record->uploadAvatar($uploadedFile);
                    \Log::info('Avatar upload completed');
                    
                    // Clean up temp file
                    Storage::disk('public')->delete($avatarFile);
                    \Log::info('Temp file cleaned up');
                } else {
                    \Log::error('File not found at: ' . $fullPath);
                }
            } catch (\Exception $e) {
                \Log::error('User avatar upload failed: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        } else {
            \Log::info('No avatar file provided');
        }
        
        return $record;
    }
}
