<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
        // Handle avatar file upload if present
        if (isset($data['avatar_file']) && $data['avatar_file']) {
            try {
                // Delete old avatar files
                $record->deleteOldAvatar();
                
                // Upload new avatar
                [$directory, $thumbWidth, $thumbHeight] = $record->getUploadConfig('avatar_path');
                $filePaths = $record->uploadFile(
                    $data['avatar_file'],
                    $directory,
                    'avatar_path',
                    'avatar_thumb_path',
                    $thumbWidth,
                    $thumbHeight
                );
                
                // Add file paths to data
                $data = array_merge($data, $filePaths);
            } catch (\Exception $e) {
                \Log::error("Avatar upload failed: {$e->getMessage()}");
            }
        }
        
        // Remove avatar_file from data as it's not a database field
        unset($data['avatar_file']);
        
        // Update other fields
        $record->update($data);
        
        return $record;
    }
}
