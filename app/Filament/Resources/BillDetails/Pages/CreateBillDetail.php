<?php

namespace App\Filament\Resources\BillDetails\Pages;

use App\Filament\Resources\BillDetails\BillDetailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillDetail extends CreateRecord
{
    protected static string $resource = BillDetailResource::class;
    protected static ?string $title = 'Tạo Chi tiết hóa đơn';

    protected function getRedirectUrl(): string
    {
        $pages = $this->getResource()::getPages();
        if (isset($pages['view'])) {
            return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }
        return $this->getResource()::getUrl('index');
    }
}
