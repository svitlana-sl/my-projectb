<?php

namespace App\Filament\Resources\SitterProfileResource\Pages;

use App\Filament\Resources\SitterProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSitterProfile extends EditRecord
{
    protected static string $resource = SitterProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
