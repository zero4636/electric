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
                TextColumn::make('bill.billing_month')
                    ->label('Tháng HĐ')
                    ->date('m/Y')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('electricMeter.meter_number')
                    ->label('Công tơ')
                    ->sortable()
                    ->copyable()
                    ->searchable(),
                TextColumn::make('consumption')
                    ->label('Tiêu thụ')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kWh')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('subsidized_applied')
                    ->label('Bao cấp')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kWh')
                    ->placeholder('—')
                    ->alignRight(),
                TextColumn::make('chargeable_kwh')
                    ->label('Tính tiền')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kWh')
                    ->weight('bold')
                    ->alignRight(),
                TextColumn::make('price_per_kwh')
                    ->label('Đơn giá')
                    ->money('VND', true)
                    ->sortable()
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
