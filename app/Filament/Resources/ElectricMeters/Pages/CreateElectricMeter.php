<?php

namespace App\Filament\Resources\ElectricMeters\Pages;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElectricMeter extends CreateRecord
{
    protected static string $resource = ElectricMeterResource::class;
    protected static ?string $title = 'Tạo Công tơ điện';

    protected function getRedirectUrl(): string
    {
        $pages = $this->getResource()::getPages();
        if (isset($pages['view'])) {
            return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }
        return $this->getResource()::getUrl('index');
    }
}
