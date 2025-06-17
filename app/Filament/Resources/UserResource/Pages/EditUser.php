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
        // Handle avatar file path directly
        if (isset($data['avatar_file']) && $data['avatar_file']) {
            $data['avatar_path'] = $data['avatar_file'];
            $data['avatar_thumb_path'] = $data['avatar_file'];
        }
        
        // Remove avatar_file from data as it's not a database field
        unset($data['avatar_file']);
        
        // Update all fields
        $record->update($data);
        
        return $record;
    }
}
