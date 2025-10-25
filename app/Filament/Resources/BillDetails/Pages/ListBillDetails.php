<?php

namespace App\Filament\Resources\BillDetails\Pages;

use App\Filament\Resources\BillDetails\BillDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBillDetails extends ListRecords
{
    protected static string $resource = BillDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
