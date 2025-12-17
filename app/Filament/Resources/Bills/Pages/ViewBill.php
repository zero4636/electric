<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use App\Filament\Resources\Bills\Schemas\BillInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewBill extends ViewRecord
{
    protected static string $resource = BillResource::class;
    protected static ?string $title = 'Xem Hóa đơn';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->getRecord()->payment_status !== 'PAID'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return BillInfolist::configure($schema);
    }
}
