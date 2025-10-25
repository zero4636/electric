<?php

namespace App\Filament\Resources\BillDetails\Pages;

use App\Filament\Resources\BillDetails\BillDetailResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBillDetail extends EditRecord
{
    protected static string $resource = BillDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
