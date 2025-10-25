<?php

namespace App\Filament\Resources\ElectricMeters\Pages;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewElectricMeter extends ViewRecord
{
    protected static string $resource = ElectricMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
