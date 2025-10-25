<?php

namespace App\Filament\Resources\ElectricMeters\Pages;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditElectricMeter extends EditRecord
{
    protected static string $resource = ElectricMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
