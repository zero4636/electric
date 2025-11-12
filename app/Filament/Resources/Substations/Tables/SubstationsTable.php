<?php

namespace App\Filament\Resources\Substations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SubstationsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã trạm')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->icon('heroicon-o-bolt'),
                    
                TextColumn::make('name')
                    ->label('Tên trạm biến áp')
                    ->searchable()
                    ->sortable()
                    ->limit(35)
                    ->wrap()
                    ->description(fn ($record) => $record->location),
                    
                TextColumn::make('location')
                    ->label('Vị trí')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('electric_meters_count')
                    ->label('Mã công tơ')
                    ->counts('electricMeters')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->sortable(),
                    
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger' => 'INACTIVE',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng',
                        default => $state,
                    })
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Hoạt động')
                    ->falseLabel('Ngừng hoạt động')
                    ->queries(
                        true: fn ($query) => $query->where('status', 'ACTIVE'),
                        false: fn ($query) => $query->where('status', 'INACTIVE'),
                    ),
                    
                TernaryFilter::make('has_meters')
                    ->label('Có công tơ')
                    ->placeholder('Tất cả')
                    ->trueLabel('Có công tơ')
                    ->falseLabel('Chưa có công tơ')
                    ->queries(
                        true: fn ($query) => $query->has('electricMeters'),
                        false: fn ($query) => $query->doesntHave('electricMeters'),
                    ),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code', 'asc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
