<?php

namespace App\Filament\Resources\Substations\Pages;

use App\Filament\Resources\Substations\SubstationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubstations extends ListRecords
{
    protected static string $resource = SubstationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
