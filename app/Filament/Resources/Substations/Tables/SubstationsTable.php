<?php

namespace App\Filament\Resources\Substations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class SubstationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã trạm')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Tên trạm')
                    ->searchable()
                    ->sortable()
                    ->limit(35)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->name),
                TextColumn::make('location')
                    ->label('Vị trí')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->location)
                    ->placeholder('—'),
                TextColumn::make('buildings_count')
                    ->label('Số tòa nhà')
                    ->getStateUsing(fn($record) => $record->buildings()->count())
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                TextColumn::make('electricMeters_count')
                    ->label('Số công tơ')
                    ->getStateUsing(fn($record) => $record->electricMeters()->count())
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
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
                    ->label('Tạo Trạm điện mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
