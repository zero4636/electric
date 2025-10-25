<?php

namespace App\Filament\Resources\Substations\Pages;

use App\Filament\Resources\Substations\SubstationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubstation extends EditRecord
{
    protected static string $resource = SubstationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
