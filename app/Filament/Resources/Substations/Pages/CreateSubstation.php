<?php

namespace App\Filament\Resources\Substations\Pages;

use App\Filament\Resources\Substations\SubstationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubstation extends CreateRecord
{
    protected static string $resource = SubstationResource::class;
    protected static ?string $title = 'Tạo Trạm điện mới';

    protected function getRedirectUrl(): string
    {
        $pages = $this->getResource()::getPages();
        if (isset($pages['view'])) {
            return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }
        return $this->getResource()::getUrl('index');
    }
}
