<?php

namespace App\Filament\Resources\ElectricityTariffs\Pages;

use App\Filament\Resources\ElectricityTariffs\ElectricityTariffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElectricityTariff extends CreateRecord
{
    protected static string $resource = ElectricityTariffResource::class;
    protected static ?string $title = 'Tạo Biểu giá điện';
}
