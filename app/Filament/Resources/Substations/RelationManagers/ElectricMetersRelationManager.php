<?php

namespace App\Filament\Resources\Substations\RelationManagers;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ElectricMetersRelationManager extends RelationManager
{
    protected static string $relationship = 'electricMeters';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => ElectricMeterResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('meter_number')
                    ->label('Mã công tơ')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->color('primary')
                    ->weight('bold'),
                TextColumn::make('organizationUnit.name')
                    ->label('Đơn vị')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('installation_location')
                    ->label('Vị trí lắp đặt')
                    ->toggleable()
                    ->placeholder('—')
                    ->wrap(),
                TextColumn::make('meter_type')
                    ->label('Loại')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'RESIDENTIAL' => 'Dân cư',
                        'COMMERCIAL' => 'Thương mại',
                        'INDUSTRIAL' => 'Công nghiệp',
                        default => '—',
                    })
                    ->badge(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger' => 'INACTIVE',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('meter_type')
                    ->label('Loại')
                    ->options([
                        'RESIDENTIAL' => 'Dân cư',
                        'COMMERCIAL' => 'Thương mại',
                        'INDUSTRIAL' => 'Công nghiệp',
                    ]),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng',
                    ]),
            ])
            ->paginationPageOptions([10, 25, 50])
            ->defaultSort('meter_number');
    }
}
