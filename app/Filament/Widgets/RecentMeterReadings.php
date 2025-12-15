<?php

namespace App\Filament\Widgets;

use App\Models\MeterReading;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentMeterReadings extends BaseWidget
{
    protected static ?string $heading = 'Chỉ số công tơ gần đây';

    protected int|string|array $columnSpan = 'full';
    
    protected static ?int $sort = 3;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                MeterReading::query()->with(['electricMeter.organizationUnit'])->latest('reading_date')->latest('id')->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('electricMeter.meter_number')
                    ->label('Mã công tơ')
                    ->url(fn ($record) => route('filament.admin.resources.electric-meters.view', ['record' => $record->electricMeter]))
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('electricMeter.organizationUnit.name')
                    ->label('Đơn vị/Hộ tiêu thụ')
                    ->wrap(),
                Tables\Columns\TextColumn::make('reading_date')
                    ->label('Ngày ghi')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reading_value')
                    ->label('Chỉ số')
                    ->numeric(2)
                    ->alignRight(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Ghi chú')
                    ->limit(40)
                    ->wrap(),
            ])
            ->paginated(false);
    }
}
