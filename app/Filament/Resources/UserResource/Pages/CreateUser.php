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
        // Handle avatar file upload using simplified method
        $avatarFile = $data['avatar_file'] ?? null;
        unset($data['avatar_file']);
        
        // Create the user first
        $record = static::getModel()::create($data);
        
        // Handle avatar upload after user creation
        if ($avatarFile) {
            $record->handleFilamentUpload($avatarFile, 'avatar_path', 'avatar_thumb_path');
        }
        
        return $record;
    }
}
