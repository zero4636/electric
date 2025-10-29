<?php

namespace App\Filament\Resources\ElectricMeters\Pages;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use Filament\Resources\Pages\ListRecords;

class ListElectricMeters extends ListRecords
{
    protected static string $resource = ElectricMeterResource::class;
    protected static ?string $title = 'Danh sách Công tơ điện';
}
