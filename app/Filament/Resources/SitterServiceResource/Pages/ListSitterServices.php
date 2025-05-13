<?php

namespace App\Filament\Resources\SitterServiceResource\Pages;

use App\Filament\Resources\SitterServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSitterServices extends ListRecords
{
    protected static string $resource = SitterServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 