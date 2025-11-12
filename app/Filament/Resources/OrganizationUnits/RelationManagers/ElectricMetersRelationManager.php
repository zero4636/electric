<?php

namespace App\Filament\Resources\OrganizationUnits\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ElectricMetersRelationManager extends RelationManager
{
    protected static string $relationship = 'electricMeters';

    protected static ?string $recordTitleAttribute = 'meter_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meter_number')->label('Mã công tơ')->sortable()->searchable(),
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
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng hoạt động',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tạo mới'),
            ])
            ->recordActions([
                EditAction::make()->label('Sửa'),
                DeleteAction::make()->label('Xóa'),
            ]);
    }
}
