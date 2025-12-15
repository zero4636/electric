<?php

namespace App\Filament\Widgets;

use App\Models\ElectricMeter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OverdueReadingsTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ElectricMeter::query()
                    ->with(['organizationUnit', 'substation', 'meterReadings' => function ($q) {
                        $q->latest('reading_date')->limit(1);
                    }])
                    ->where('status', 'ACTIVE')
                    ->whereDoesntHave('meterReadings', function (Builder $q) {
                        $q->where('reading_date', '>=', now()->subDays(30));
                    })
                    ->orWhereHas('meterReadings', function (Builder $q) {
                        // Include meters where latest reading is old
                        $q->whereIn('id', function ($subQuery) {
                            $subQuery->select(DB::raw('MAX(id)'))
                                ->from('meter_readings')
                                ->groupBy('electric_meter_id')
                                ->having(DB::raw('MAX(reading_date)'), '<', now()->subDays(30));
                        });
                    })
                    ->limit(20)
            )
            ->heading('Công tơ chưa đọc số >30 ngày')
            ->columns([
                TextColumn::make('meter_number')
                    ->label('Mã công tơ')
                    ->searchable()
                    ->url(fn (ElectricMeter $record) => route('filament.admin.resources.electric-meters.view', $record)),
                
                TextColumn::make('organizationUnit.name')
                    ->label('Đơn vị/Hộ')
                    ->limit(30)
                    ->url(fn (ElectricMeter $record) => $record->organizationUnit 
                        ? route('filament.admin.resources.organization-units.view', $record->organizationUnit)
                        : null),
                
                TextColumn::make('substation.name')
                    ->label('Trạm')
                    ->limit(25),
                
                TextColumn::make('installation_location')
                    ->label('Vị trí')
                    ->limit(30),
                
                TextColumn::make('last_reading_date')
                    ->label('Đọc cuối')
                    ->getStateUsing(function (ElectricMeter $record) {
                        $latest = $record->meterReadings->first();
                        return $latest ? $latest->reading_date->format('d/m/Y') : 'Chưa có';
                    })
                    ->badge()
                    ->color(fn (ElectricMeter $record) => 
                        $record->meterReadings->isEmpty() ? 'danger' : 'warning'
                    ),
                
                TextColumn::make('days_overdue')
                    ->label('Quá hạn')
                    ->getStateUsing(function (ElectricMeter $record) {
                        $latest = $record->meterReadings->first();
                        if (!$latest) {
                            return 'Chưa đọc lần nào';
                        }
                        $days = now()->diffInDays($latest->reading_date);
                        return "{$days} ngày";
                    })
                    ->badge()
                    ->color('danger'),
            ]);
    }
}
