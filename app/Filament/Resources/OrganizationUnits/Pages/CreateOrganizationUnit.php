<?php

namespace App\Filament\Resources\OrganizationUnits\Pages;

use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizationUnit extends CreateRecord
{
    protected static string $resource = OrganizationUnitResource::class;
    protected static ?string $title = 'Tạo Đơn vị tổ chức';

    protected function getRedirectUrl(): string
    {
        $pages = $this->getResource()::getPages();
        if (isset($pages['view'])) {
            return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }
        return $this->getResource()::getUrl('index');
    }
}
