<?php

namespace App\Filament\Resources\ElectricMeters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class ElectricMetersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meter_number')->label('Số công tơ')->searchable()->sortable(),
                TextColumn::make('organizationUnit.name')->label('Đơn vị')->sortable(),
                TextColumn::make('substation.name')->label('Trạm điện')->sortable(),
                TextColumn::make('meter_type')
                    ->label('Loại')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ANALOG' => 'Cơ khí',
                        'DIGITAL' => 'Điện tử',
                        'SMART' => 'Thông minh',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('hsn')->label('Số sê-ri')->sortable(),
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger' => 'INACTIVE',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng hoạt động',
                        default => $state,
                    })
                    ->sortable(),
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
