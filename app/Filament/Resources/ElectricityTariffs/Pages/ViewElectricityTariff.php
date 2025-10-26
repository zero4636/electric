<?php

namespace App\Filament\Resources\ElectricityTariffs\Pages;

use App\Filament\Resources\ElectricityTariffs\ElectricityTariffResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewElectricityTariff extends ViewRecord
{
    protected static string $resource = ElectricityTariffResource::class;
    protected static ?string $title = 'Xem Biểu giá điện';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
