<?php

namespace App\Filament\Resources\SitterProfileResource\Pages;

use App\Filament\Resources\SitterProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSitterProfiles extends ListRecords
{
    protected static string $resource = SitterProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
