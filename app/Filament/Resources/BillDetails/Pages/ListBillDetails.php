<?php

namespace App\Filament\Resources\BillDetails\Pages;

use App\Filament\Resources\BillDetails\BillDetailResource;
use Filament\Resources\Pages\ListRecords;

class ListBillDetails extends ListRecords
{
    protected static string $resource = BillDetailResource::class;
    protected static ?string $title = 'Danh sách Chi tiết hóa đơn';
}
