<?php

namespace App\Filament\Resources\BillDetails\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bill.id')->label('Mã hóa đơn')->sortable(),
                TextColumn::make('electricMeter.meter_number')->label('Công tơ')->sortable(),
                TextColumn::make('consumption')->label('Tiêu thụ')->suffix(' kWh')->sortable(),
                TextColumn::make('price_per_kwh')->label('Đơn giá')->money('VND', true)->sortable(),
                TextColumn::make('hsn')->label('Số sê-ri')->sortable(),
                TextColumn::make('amount')->label('Thành tiền')->money('VND', true)->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
