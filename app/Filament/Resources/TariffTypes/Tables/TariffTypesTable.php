<?php

namespace App\Filament\Resources\TariffTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class TariffTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                    
                BadgeColumn::make('name')
                    ->label('Tên loại')
                    ->searchable()
                    ->sortable()
                    ->colors([
                        'primary' => fn($record) => $record->color === 'primary',
                        'success' => fn($record) => $record->color === 'success',
                        'warning' => fn($record) => $record->color === 'warning',
                        'danger' => fn($record) => $record->color === 'danger',
                        'info' => fn($record) => $record->color === 'info',
                    ]),
                    
                TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('—'),
                    
                TextColumn::make('tariffs_count')
                    ->label('Số biểu giá')
                    ->getStateUsing(fn($record) => $record->electricityTariffs()->count())
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                    
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
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
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo loại biểu giá mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

