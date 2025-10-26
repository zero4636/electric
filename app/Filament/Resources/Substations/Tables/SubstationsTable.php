<?php

namespace App\Filament\Resources\Substations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class SubstationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên trạm')->searchable()->sortable(),
                TextColumn::make('code')->label('Mã')->sortable(),
                TextColumn::make('location')->label('Vị trí')->sortable(),
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors(['success'=>'ACTIVE','danger'=>'INACTIVE'])
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
