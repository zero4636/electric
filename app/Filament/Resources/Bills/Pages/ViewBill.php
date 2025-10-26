<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBill extends ViewRecord
{
    protected static string $resource = BillResource::class;
    protected static ?string $title = 'Xem Hóa đơn';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
