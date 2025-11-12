<?php

namespace App\Filament\Resources\BillDetails\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bill.billing_date')
                    ->label('Ngày HĐ')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('electricMeter.meter_number')
                    ->label('Công tơ')
                    ->sortable()
                    ->copyable()
                    ->searchable(),
                TextColumn::make('startReading.reading_value')
                    ->label('Chỉ số đầu')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->placeholder('—'),
                TextColumn::make('endReading.reading_value')
                    ->label('Chỉ số cuối')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->placeholder('—'),
                TextColumn::make('consumption')
                    ->label('Tiêu thụ')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kWh')
                    ->sortable()
                    ->weight('bold')
                    ->alignRight(),
                TextColumn::make('price_per_kwh')
                    ->label('Đơn giá')
                    ->money('VND', true)
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('subsidized_amount')
                    ->label('Hỗ trợ')
                    ->money('VND', true)
                    ->placeholder('—')
                    ->alignRight(),
                TextColumn::make('amount')
                    ->label('Thành tiền')
                    ->money('VND', true)
                    ->sortable()
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->filters([
                //
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo Chi tiết hóa đơn mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
