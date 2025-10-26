<?php

namespace App\Filament\Resources\OrganizationUnits\Pages;

use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use Filament\Resources\Pages\Page;
use App\Models\OrganizationUnit;

class TreeOrganizationUnits extends Page
{
    protected static string $resource = OrganizationUnitResource::class;

    // resource can stay static
    // Filament\Resources\Pages\Page defines $view as a non-static property, so match that
    protected string $view = 'filament.organization_units.tree';

    // Title is static in the Filament base page class
    protected static ?string $title = 'Cây Đơn vị tổ chức';

    public $data;

    public function mount(): void
    {
        // load all units and pass to view via property
        // avoid calling toTree (not available unless using nested set package)
        $this->data = OrganizationUnit::all();
    }
}
