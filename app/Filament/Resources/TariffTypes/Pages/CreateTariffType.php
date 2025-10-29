<?php

namespace App\Filament\Resources\TariffTypes\Pages;

use App\Filament\Resources\TariffTypes\TariffTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTariffType extends CreateRecord
{
    protected static string $resource = TariffTypeResource::class;
    protected static ?string $title = 'Tạo loại biểu giá mới';
}
