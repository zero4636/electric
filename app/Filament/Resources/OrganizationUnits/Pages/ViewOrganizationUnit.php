<?php

namespace App\Filament\Resources\OrganizationUnits\Pages;

use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrganizationUnit extends ViewRecord
{
    protected static string $resource = OrganizationUnitResource::class;
    protected static ?string $title = 'Xem Đơn vị tổ chức';
    
    protected function getHeaderWidgets(): array
    {
        return [
            ViewOrganizationUnit\ElectricityConsumptionChart::class,
            ViewOrganizationUnit\ElectricityStatsOverview::class,
        ];
    }
    
    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
