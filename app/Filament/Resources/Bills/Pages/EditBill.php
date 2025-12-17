<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;
    protected static ?string $title = 'Sửa Hóa đơn';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalculate')
                ->label('Tính lại tổng tiền')
                ->icon('heroicon-o-calculator')
                ->visible(fn () => $this->getRecord()->payment_status !== 'PAID')
                ->action(function () {
                    $bill = $this->getRecord();
                    $newTotal = $bill->billDetails()->sum('amount');
                    
                    $bill->update(['total_amount' => $newTotal]);
                    
                    Notification::make()
                        ->title('Đã cập nhật tổng tiền')
                        ->body("Tổng tiền mới: " . number_format($newTotal, 0, ',', '.') . " VND")
                        ->success()
                        ->send();
                }),
            DeleteAction::make()
                ->label('Xóa')
                ->visible(fn () => $this->getRecord()->payment_status !== 'PAID'),
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // If bill is paid, make it read-only by redirecting to view
        if ($this->getRecord()->payment_status === 'PAID') {
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->getRecord()]));
        }
        
        // Ensure billing_month is properly formatted for the select
        if (isset($data['billing_month'])) {
            // Convert to Y-m-01 format for select options
            $billingMonth = \Carbon\Carbon::parse($data['billing_month']);
            $data['billing_month'] = $billingMonth->format('Y-m-01');
        }
        
        return $data;
    }
}
