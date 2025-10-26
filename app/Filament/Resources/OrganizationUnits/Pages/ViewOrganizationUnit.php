<?php

namespace App\Filament\Resources\OrganizationUnits\Pages;

use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrganizationUnit extends ViewRecord
{
    protected static string $resource = OrganizationUnitResource::class;
    protected static ?string $title = 'Xem Đơn vị tổ chức';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
