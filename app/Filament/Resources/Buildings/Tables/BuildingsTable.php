<?php

namespace App\Filament\Resources\Buildings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BuildingsTable
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
                    
                TextColumn::make('name')
                    ->label('Tên tòa nhà')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->wrap(),
                    
                TextColumn::make('substation.name')
                    ->label('Trạm biến áp')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->placeholder('—'),
                    
                TextColumn::make('address')
                    ->label('Địa chỉ')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->placeholder('—'),
                    
                TextColumn::make('total_floors')
                    ->label('Số tầng')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->placeholder('—'),
                    
                TextColumn::make('meters_count')
                    ->label('Số công tơ')
                    ->getStateUsing(fn($record) => $record->electricMeters()->count())
                    ->badge()
                    ->color('info')
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
                SelectFilter::make('substation_id')
                    ->label('Trạm biến áp')
                    ->relationship('substation', 'name'),
                    
                SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo Tòa nhà mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

