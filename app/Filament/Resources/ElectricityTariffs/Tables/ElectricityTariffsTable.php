<?php

namespace App\Filament\Resources\ElectricityTariffs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class ElectricityTariffsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('tariffType.name')
                    ->label('Loại')
                    ->searchable()
                    ->sortable()
                    ->colors([
                        'primary' => fn($record) => $record->tariffType?->color === 'primary',
                        'success' => fn($record) => $record->tariffType?->color === 'success',
                        'warning' => fn($record) => $record->tariffType?->color === 'warning',
                        'danger' => fn($record) => $record->tariffType?->color === 'danger',
                        'info' => fn($record) => $record->tariffType?->color === 'info',
                    ])
                    ->placeholder('—'),
                TextColumn::make('price_per_kwh')
                    ->label('Giá (VNĐ/kWh)')
                    ->money('VND')
                    ->sortable()
                    ->alignRight()
                    ->weight('bold'),
                TextColumn::make('effective_from')
                    ->label('Từ ngày')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('effective_to')
                    ->label('Đến ngày')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),
                BadgeColumn::make('is_active')
                    ->label('Trạng thái')
                    ->getStateUsing(function ($record) {
                        $today = now();
                        $from = \Carbon\Carbon::parse($record->effective_from);
                        $to = $record->effective_to ? \Carbon\Carbon::parse($record->effective_to) : null;
                        
                        if ($today->lt($from)) {
                            return 'upcoming';
                        }
                        if ($to && $today->gt($to)) {
                            return 'expired';
                        }
                        return 'active';
                    })
                    ->colors([
                        'success' => 'active',
                        'warning' => 'upcoming',
                        'danger' => 'expired',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Đang dùng',
                        'upcoming' => 'Chưa dùng',
                        'expired' => 'Hết hạn',
                        default => $state,
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo Biểu giá điện mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
