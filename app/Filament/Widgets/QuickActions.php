<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use App\Filament\Resources\MeterReadings\MeterReadingResource;
use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    use CanPoll;

    protected string $view = 'filament.widgets.quick-actions';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getCreateOrgUnitUrl(): string
    {
        return OrganizationUnitResource::getUrl('create');
    }

    public function getCreateMeterUrl(): string
    {
        return ElectricMeterResource::getUrl('create');
    }

    public function getCreateReadingUrl(): string
    {
        return MeterReadingResource::getUrl('create');
    }
}
