<?php

namespace App\Filament\Resources\BillDetails\Pages;

use App\Filament\Resources\BillDetails\BillDetailResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBillDetail extends ViewRecord
{
    protected static string $resource = BillDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
