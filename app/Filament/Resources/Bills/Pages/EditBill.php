<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;
    protected static ?string $title = 'Sửa Hóa đơn';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Xóa'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $pages = $this->getResource()::getPages();
        if (isset($pages['view'])) {
            return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }
        return $this->getResource()::getUrl('index');
    }
}
