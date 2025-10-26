<?php

namespace App\Filament\Resources\MeterReadings\Pages;

use App\Filament\Resources\MeterReadings\MeterReadingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMeterReading extends ViewRecord
{
    protected static string $resource = MeterReadingResource::class;
    protected static ?string $title = 'Xem Chỉ số công tơ';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
