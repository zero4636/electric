<?php

namespace App\Filament\Resources\ElectricMeters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class ElectricMetersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meter_number')
                    ->label('Số công tơ')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('organizationUnit.name')
                    ->label('Đơn vị')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->organizationUnit?->name)
                    ->wrap(),
                TextColumn::make('building.name')
                    ->label('Tòa nhà')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->tooltip(fn ($record) => $record->building?->name),
                TextColumn::make('substation.name')
                    ->label('Trạm')
                    ->searchable()
                    ->sortable()
                    ->limit(15)
                    ->placeholder('—')
                    ->tooltip(fn ($record) => $record->substation?->name),
                TextColumn::make('hsn')
                    ->label('HSN')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignCenter(),
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger' => 'INACTIVE',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ACTIVE' => '✓',
                        'INACTIVE' => '✗',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo Công tơ điện mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
