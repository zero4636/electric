<?php

namespace App\Filament\Widgets;

use App\Models\OrganizationUnit;
use App\Models\MeterReading;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class TopConsumersWidget extends BaseWidget
{
    protected static ?string $heading = 'Top 5 hộ tiêu thụ nhiều nhất tháng này';

    protected int | string | array $columnSpan = 2;
    
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Lấy tất cả consumers
        $consumers = OrganizationUnit::where('type', 'CONSUMER')
            ->with('electricMeters')
            ->get();
        
        $consumptionData = [];
        
        foreach ($consumers as $consumer) {
            $totalConsumption = 0;
            $totalAmount = 0;
            
            foreach ($consumer->electricMeters as $meter) {
                $startDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
                $endDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
                
                $current = MeterReading::where('electric_meter_id', $meter->id)
                    ->whereBetween('reading_date', [$startDate, $endDate])
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                if ($current) {
                    $previous = MeterReading::where('electric_meter_id', $meter->id)
                        ->where('reading_date', '<', $current->reading_date)
                        ->orderBy('reading_date', 'desc')
                        ->first();
                    
                    if ($previous) {
                        $rawConsumption = ($current->reading_value - $previous->reading_value) * $meter->hsn;
                        if ($rawConsumption > 0) {
                            // Tính tiền theo logic thực tế
                            $subsidizedApplied = min($rawConsumption, $meter->subsidized_kwh ?? 0);
                            $chargeableKwh = max(0, $rawConsumption - $subsidizedApplied);
                            
                            // Lấy biểu giá hiện tại
                            $tariff = \App\Models\ElectricityTariff::where('tariff_type_id', $meter->tariff_type_id)
                                ->where('effective_from', '<=', $endDate)
                                ->where(function($q) use ($endDate) {
                                    $q->whereNull('effective_to')->orWhere('effective_to', '>=', $endDate);
                                })
                                ->first();
                            
                            $price = $tariff ? $tariff->price_per_kwh : 3505; // fallback
                            $amount = $chargeableKwh * $price;
                            
                            $totalConsumption += $rawConsumption;
                            $totalAmount += $amount;
                        }
                    } else {
                        // Không có previous reading -> coi như bắt đầu từ 0
                        $rawConsumption = $current->reading_value * $meter->hsn;
                        if ($rawConsumption > 0) {
                            $subsidizedApplied = min($rawConsumption, $meter->subsidized_kwh ?? 0);
                            $chargeableKwh = max(0, $rawConsumption - $subsidizedApplied);
                            
                            $tariff = \App\Models\ElectricityTariff::where('tariff_type_id', $meter->tariff_type_id)
                                ->where('effective_from', '<=', $endDate)
                                ->where(function($q) use ($endDate) {
                                    $q->whereNull('effective_to')->orWhere('effective_to', '>=', $endDate);
                                })
                                ->first();
                            
                            $price = $tariff ? $tariff->price_per_kwh : 3505; // fallback
                            $amount = $chargeableKwh * $price;
                            
                            $totalConsumption += $rawConsumption;
                            $totalAmount += $amount;
                        }
                    }
                }
            }
            
            if ($totalConsumption > 0) {
                $consumptionData[] = [
                    'id' => $consumer->id,
                    'code' => $consumer->code,
                    'name' => $consumer->name,
                    'parent' => $consumer->parent?->name ?? '-',
                    'consumption' => $totalConsumption,
                    'amount' => $totalAmount,
                ];
            }
        }
        
        // Sắp xếp giảm dần và lấy top 5
        usort($consumptionData, fn($a, $b) => $b['consumption'] <=> $a['consumption']);
        $topConsumers = array_slice($consumptionData, 0, 5);
        
        return $table
            ->query(
                // Dummy query - we'll use custom data
                OrganizationUnit::query()->whereIn('id', array_column($topConsumers, 'id'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(function ($record) use ($topConsumers) {
                        $index = array_search($record->id, array_column($topConsumers, 'id'));
                        return $index !== false ? $index + 1 : '-';
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        1 => 'success',
                        2 => 'info',
                        3 => 'warning',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên hộ tiêu thụ')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-o-eye')
                    ->description('Click để xem chi tiết'),
                    
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Đơn vị')
                    ->default('-'),
                    
                Tables\Columns\TextColumn::make('consumption')
                    ->label('Tiêu thụ')
                    ->state(function ($record) use ($topConsumers) {
                        $data = collect($topConsumers)->firstWhere('id', $record->id);
                        return $data ? number_format($data['consumption'], 2, ',', '.') . ' kWh' : '-';
                    })
                    ->color('primary')
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->label('Số tiền')
                    ->state(function ($record) use ($topConsumers) {
                        $data = collect($topConsumers)->firstWhere('id', $record->id);
                        return $data ? number_format($data['amount'], 0, ',', '.') . ' đ' : '-';
                    })
                    ->color('warning'),
            ])
            ->recordUrl(fn (OrganizationUnit $record): string => 
                route('filament.admin.resources.organization-units.view', ['record' => $record->id])
            )
            ->paginated(false);
    }
}
