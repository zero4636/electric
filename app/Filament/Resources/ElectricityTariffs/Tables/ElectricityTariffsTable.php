<?php

namespace App\Filament\Resources\ElectricityTariffs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ElectricityTariffsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tariff_type')
                    ->label('Loại')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'RESIDENTIAL' => 'Sinh hoạt',
                        'BUSINESS' => 'Kinh doanh',
                        'INDUSTRIAL' => 'Sản xuất',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('price_per_kwh')->label('Giá (kWh)')->money('VND', true)->sortable(),
                TextColumn::make('effective_from')->label('Hiệu lực từ')->date()->sortable(),
                TextColumn::make('effective_to')->label('Hiệu lực đến')->date()->sortable(),
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
