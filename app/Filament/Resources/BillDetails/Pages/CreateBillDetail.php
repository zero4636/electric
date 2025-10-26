<?php

namespace App\Filament\Resources\BillDetails\Pages;

use App\Filament\Resources\BillDetails\BillDetailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillDetail extends CreateRecord
{
    protected static string $resource = BillDetailResource::class;
    protected static ?string $title = 'Tạo Chi tiết hóa đơn';
}
