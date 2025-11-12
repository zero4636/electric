<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBill extends CreateRecord
{
    protected static string $resource = BillResource::class;
    protected static ?string $title = 'Tạo Hóa đơn';

    protected function getRedirectUrl(): string
    {
        $pages = $this->getResource()::getPages();
        if (isset($pages['view'])) {
            return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }
        return $this->getResource()::getUrl('index');
    }
}
