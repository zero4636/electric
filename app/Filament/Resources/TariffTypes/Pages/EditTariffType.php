<?php

namespace App\Filament\Resources\TariffTypes\Pages;

use App\Filament\Resources\TariffTypes\TariffTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTariffType extends EditRecord
{
    protected static string $resource = TariffTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
