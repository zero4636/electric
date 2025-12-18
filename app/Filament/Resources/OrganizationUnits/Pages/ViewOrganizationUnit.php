<?php

namespace App\Filament\Resources\OrganizationUnits\Pages;

use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use App\Helpers\NumberToWords;
use App\Models\MeterReading;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ViewOrganizationUnit extends ViewRecord
{
    protected static string $resource = OrganizationUnitResource::class;
    protected static ?string $title = 'Xem Đơn vị tổ chức';
    
    protected function getHeaderWidgets(): array
    {
        return [
            ViewOrganizationUnit\ElectricityConsumptionChart::class,
            ViewOrganizationUnit\ElectricityStatsOverview::class,
        ];
    }
    
    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printPdf')
                ->label('In PDF')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    Select::make('month')
                        ->label('Tháng')
                        ->options(function () {
                            $months = [];
                            for ($i = 1; $i <= 12; $i++) {
                                $months[$i] = 'Tháng ' . $i;
                            }
                            return $months;
                        })
                        ->default(now()->month)
                        ->required(),
                    Select::make('year')
                        ->label('Năm')
                        ->options(function () {
                            $years = [];
                            $currentYear = now()->year;
                            for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
                                $years[$i] = 'Năm ' . $i;
                            }
                            return $years;
                        })
                        ->default(now()->year)
                        ->required(),
                    TextInput::make('bill_number')
                        ->label('Số phiếu')
                        ->default(fn () => rand(100, 999))
                        ->required(),
                    TextInput::make('signer_name')
                        ->label('Người ký (Phòng CSVC)')
                        ->placeholder('Hồ Thành Long'),
                ])
                ->action(function (array $data) {
                    $organization = $this->record;
                    $month = $data['month'];
                    $year = $data['year'];
                    
                    // Lấy dữ liệu meters và readings
                    $meters = $this->getMetersData($organization, $month, $year);
                    $totalAmount = array_sum(array_column($meters, 'amount'));
                    
                    $pdf = Pdf::loadView('pdf.' . ($organization->type === 'CONSUMER' ? 'consumer' : 'organization-unit') . '-bill', [
                        $organization->type === 'CONSUMER' ? 'consumer' : 'organization' => $organization,
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
                
            EditAction::make(),
        ];
    }
    
    private function getMetersData($organization, $month, $year)
    {
        $meters = [];
        
        if ($organization->type === 'UNIT') {
            // Lấy tất cả consumers con
            $consumers = $organization->children()
                ->where('type', 'CONSUMER')
                ->with(['electricMeters.substation'])
                ->get();
            
            foreach ($consumers as $consumer) {
                foreach ($consumer->electricMeters()->where('status', 'ACTIVE')->get() as $meter) {
                    $readings = $this->getMeterReadings($meter->id, $month, $year);
                    
                    if ($readings) {
                        $rawConsumption = ($readings['current'] - $readings['previous']) * $meter->hsn;
                        
                        // Tính tiền theo logic thực tế
                        $subsidizedApplied = min($rawConsumption, $meter->subsidized_kwh ?? 0);
                        $chargeableKwh = max(0, $rawConsumption - $subsidizedApplied);
                        
                        // Lấy biểu giá hiện tại
                        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                        $tariff = \App\Models\ElectricityTariff::where('tariff_type_id', $meter->tariff_type_id)
                            ->where('effective_from', '<=', $endDate)
                            ->where(function($q) use ($endDate) {
                                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $endDate);
                            })
                            ->first();
                        
                        $price = $tariff ? $tariff->price_per_kwh : 3505; // fallback
                        $amount = $chargeableKwh * $price;
                        
                        $meters[] = [
                            'name' => $consumer->name,
                            'code' => $consumer->code,
                            'location' => $meter->installation_location ?? ($consumer->building ?? $consumer->address),
                            'meter_number' => $meter->meter_number,
                            'current_reading' => $readings['current'],
                            'previous_reading' => $readings['previous'],
                            'hsn' => $meter->hsn,
                            'consumption' => $rawConsumption,
                            'price' => $price,
                            'amount' => $amount,
                            'substation' => $meter->substation->name ?? '',
                            'subsidy' => $subsidizedApplied > 0 ? number_format($subsidizedApplied, 0, ',', '.') : '',
                        ];
                    }
                }
            }
        } else {
            // Consumer - lấy meters của chính nó
            $consumerMeters = $organization->electricMeters()
                ->where('status', 'ACTIVE')
                ->with('substation')
                ->get();
            
            foreach ($consumerMeters as $meter) {
                $readings = $this->getMeterReadings($meter->id, $month, $year);
                
                if ($readings) {
                    $rawConsumption = ($readings['current'] - $readings['previous']) * $meter->hsn;
                    
                    // Tính tiền theo logic thực tế
                    $subsidizedApplied = min($rawConsumption, $meter->subsidized_kwh ?? 0);
                    $chargeableKwh = max(0, $rawConsumption - $subsidizedApplied);
                    
                    // Lấy biểu giá hiện tại
                    $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                    $tariff = \App\Models\ElectricityTariff::where('tariff_type_id', $meter->tariff_type_id)
                        ->where('effective_from', '<=', $endDate)
                        ->where(function($q) use ($endDate) {
                            $q->whereNull('effective_to')->orWhere('effective_to', '>=', $endDate);
                        })
                        ->first();
                    
                    $price = $tariff ? $tariff->price_per_kwh : 3505; // fallback
                    $amount = $chargeableKwh * $price;
                    
                    $meters[] = [
                        'meter_number' => $meter->meter_number,
                        'location' => $meter->installation_location ?? $organization->building,
                        'current_reading' => $readings['current'],
                        'previous_reading' => $readings['previous'],
                        'hsn' => $meter->hsn,
                        'consumption' => $rawConsumption,
                        'price' => $price,
                        'amount' => $amount,
                        'substation' => $meter->substation->name ?? '',
                        'subsidy' => $subsidizedApplied > 0 ? number_format($subsidizedApplied, 0, ',', '.') : '',
                    ];
                }
            }
        }
        
        return $meters;
    }
    
    private function getMeterReadings($meterId, $month, $year)
    {
        // Lấy 2 readings gần nhất trong tháng
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $readings = MeterReading::where('electric_meter_id', $meterId)
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date', 'desc')
            ->limit(2)
            ->get();
        
        if ($readings->count() >= 2) {
            return [
                'current' => $readings[0]->reading_value,
                'previous' => $readings[1]->reading_value,
            ];
        }
        
        // Fallback: lấy reading cuối và trước đó
        $current = MeterReading::where('electric_meter_id', $meterId)
            ->where('reading_date', '<=', $endDate)
            ->orderBy('reading_date', 'desc')
            ->first();
        
        if (!$current) {
            return null;
        }
        
        $previous = MeterReading::where('electric_meter_id', $meterId)
            ->where('reading_date', '<', $current->reading_date)
            ->orderBy('reading_date', 'desc')
            ->first();
        
        return [
            'current' => $current->reading_value,
            'previous' => $previous ? $previous->reading_value : 0,
        ];
    }
}

