<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Handle avatar file upload if present
        $avatarFile = $data['avatar_file'] ?? null;
        unset($data['avatar_file']);
        
        // Create the user first
        $record = static::getModel()::create($data);
        
        // Handle avatar upload after user creation
        if ($avatarFile) {
            try {
                [$directory, $thumbWidth, $thumbHeight] = $record->getUploadConfig('avatar_path');
                $filePaths = $record->uploadFile(
                    $avatarFile,
                    $directory,
                    'avatar_path',
                    'avatar_thumb_path',
                    $thumbWidth,
                    $thumbHeight
                );
                
                $record->update($filePaths);
            } catch (\Exception $e) {
                \Log::error("Avatar upload failed: {$e->getMessage()}");
            }
        }
        
        return $record;
    }
}
