<?php

namespace App\Filament\Resources\MeterReadings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class MeterReadingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reading_date')->label('Ngày ghi')->date()->sortable(),
                TextColumn::make('electricMeter.meter_number')->label('Công tơ')->sortable(),
                TextColumn::make('reading_value')->label('Chỉ số')->sortable(),
                TextColumn::make('hsn')->label('Số sê-ri')->sortable(),
            ])
            ->filters([
                Filter::make('meter')
                    ->label('Công tơ')
                    ->form([
                        Select::make('electric_meter_id')
                            ->label('Công tơ')
                            ->relationship('electricMeter','meter_number'),
                    ])->query(fn($query, $data) => $query->when($data['electric_meter_id'] ?? null, fn($q,$id)=> $q->where('electric_meter_id', $id))),

                Filter::make('date')
                    ->label('Ngày ghi')
                    ->form([
                        DatePicker::make('from')->label('Từ ngày'),
                        DatePicker::make('until')->label('Đến ngày'),
                    ])
                    ->query(function ($query, $data) {
                        return $query->when(isset($data['from']), fn($q) => $q->where('reading_date', '>=', $data['from']))
                                     ->when(isset($data['until']), fn($q) => $q->where('reading_date', '<=', $data['until']));
                    }),
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
