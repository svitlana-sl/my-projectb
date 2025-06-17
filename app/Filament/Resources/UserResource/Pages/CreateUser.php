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
        // Handle avatar file path directly
        $avatarFile = $data['avatar_file'] ?? null;
        unset($data['avatar_file']);
        
        // Create the user first
        $record = static::getModel()::create($data);
        
        // Simple avatar path assignment
        if ($avatarFile) {
            $record->update([
                'avatar_path' => $avatarFile,
                'avatar_thumb_path' => $avatarFile
            ]);
        }
        
        return $record;
    }
}
