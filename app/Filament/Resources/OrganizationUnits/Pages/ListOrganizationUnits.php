<?php

namespace App\Filament\Resources\OrganizationUnits\Pages;

use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationUnits extends ListRecords
{
    protected static string $resource = OrganizationUnitResource::class;
    protected static ?string $title = 'Danh sách Đơn vị tổ chức';
}
