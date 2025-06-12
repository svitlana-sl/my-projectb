<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
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
        // Handle avatar file upload
        if (isset($data['avatar_file']) && $data['avatar_file']) {
            // Create UploadedFile from the stored file
            $tempPath = storage_path('app/public/' . $data['avatar_file']);
            if (file_exists($tempPath)) {
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempPath,
                    basename($data['avatar_file']),
                    mime_content_type($tempPath),
                    null,
                    true
                );
                
                $record->uploadAvatar($uploadedFile);
                
                // Clean up temp file
                Storage::disk('public')->delete($data['avatar_file']);
            }
        }
        
        // Remove avatar_file from data as it's not a database field
        unset($data['avatar_file']);
        
        // Update other fields
        $record->update($data);
        
        return $record;
    }
}
