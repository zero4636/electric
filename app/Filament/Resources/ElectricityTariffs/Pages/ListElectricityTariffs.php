<?php

namespace App\Filament\Resources\ElectricityTariffs\Pages;

use App\Filament\Resources\ElectricityTariffs\ElectricityTariffResource;
use Filament\Resources\Pages\ListRecords;

class ListElectricityTariffs extends ListRecords
{
    protected static string $resource = ElectricityTariffResource::class;
    protected static ?string $title = 'Danh sách Biểu giá điện';
}
