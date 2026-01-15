<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use App\Filament\Resources\Bills\Schemas\BillInfolist;
use App\Helpers\NumberToWords;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewBill extends ViewRecord
{
    protected static string $resource = BillResource::class;
    protected static ?string $title = 'Xem Hóa đơn';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printPdf')
                ->label('In PDF')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    TextInput::make('bill_number')
                        ->label('Số phiếu')
                        ->default(fn () => rand(100, 999))
                        ->required(),
                    TextInput::make('signer_name')
                        ->label('Người ký (Phòng CSVC)')
                        ->placeholder('Hồ Thành Long'),
                ])
                ->action(function (array $data) {
                    $bill = $this->getRecord();
                    $organization = $bill->organizationUnit;
                    $month = $bill->billing_month->month;
                    $year = $bill->billing_month->year;
                    
                    // Lấy dữ liệu meters từ bill_details
                    $meters = $this->getMetersDataFromBill($bill);
                    $totalAmount = $bill->total_amount;
                    
                    $pdf = Pdf::loadView('pdf.consumer-bill', [
                        'consumer' => $organization,
                        'meters' => $meters,
                        'month' => $month,
                        'year' => $year,
                        'billNumber' => $data['bill_number'],
                        'amountInWords' => NumberToWords::convert($totalAmount),
                        'signerName' => $data['signer_name'] ?? '',
                        'preparedBy' => auth()->user()->name ?? '',
                    ]);
                    
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->stream();
                    }, 'phieu-dien-' . $organization->code . '-' . $month . '-' . $year . '.pdf');
                }),
                
            EditAction::make()
                ->visible(fn () => $this->getRecord()->payment_status !== 'PAID'),
        ];
    }
    
    private function getMetersDataFromBill($bill)
    {
        $meters = [];
        
        foreach ($bill->billDetails as $detail) {
            $meter = $detail->electricMeter;
            
            // Lấy chỉ số từ MeterReading
            $endDate = $bill->billing_month->copy()->endOfMonth();
            $startDate = $bill->billing_month->copy()->startOfMonth();
            
            $currentReading = \App\Models\MeterReading::where('electric_meter_id', $meter->id)
                ->whereBetween('reading_date', [$startDate, $endDate])
                ->orderBy('reading_date', 'desc')
                ->first();
            
            $previousReading = $currentReading 
                ? \App\Models\MeterReading::where('electric_meter_id', $meter->id)
                    ->where('reading_date', '<', $currentReading->reading_date)
                    ->orderBy('reading_date', 'desc')
                    ->first()
                : null;
            
            $meters[] = [
                'meter_number' => $meter->meter_number,
                'location' => $meter->installation_location ?? $bill->organizationUnit->building,
                'current_reading' => $currentReading ? $currentReading->reading_value : 0,
                'previous_reading' => $previousReading ? $previousReading->reading_value : 0,
                'hsn' => $detail->hsn,
                'consumption' => $detail->consumption,
                'price' => $detail->price_per_kwh,
                'amount' => $detail->amount,
                'substation' => $meter->substation->name ?? '',
                'subsidy' => $detail->subsidized_applied > 0 ? number_format($detail->subsidized_applied, 0, ',', '.') : '',
            ];
        }
        
        return $meters;
    }

    public function infolist(Schema $schema): Schema
    {
        return BillInfolist::configure($schema);
    }
}
