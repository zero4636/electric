<?php

namespace App\Filament\Resources\ElectricityTariffs\Pages;

use App\Filament\Resources\ElectricityTariffs\ElectricityTariffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElectricityTariff extends CreateRecord
{
    protected static string $resource = ElectricityTariffResource::class;
    protected static ?string $title = 'Tạo Biểu giá điện';

    protected function getRedirectUrl(): string
    {
        // Check if view route exists, otherwise redirect to index
        $pages = $this->getResource()::getPages();
        if (isset($pages['view'])) {
            return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }
        return $this->getResource()::getUrl('index');
    }
}
