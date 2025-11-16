<?php

namespace App\Filament\Resources\OrganizationUnits\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Đơn vị con';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type === 'UNIT';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên đơn vị/Hộ tiêu thụ')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->url(fn ($record) => route('filament.admin.resources.organization-units.view', ['record' => $record]))
                    ->color('primary'),
                TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                BadgeColumn::make('type')
                    ->label('Loại')
                    ->colors([
                        'success' => 'UNIT',
                        'warning' => 'CONSUMER',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'UNIT' => 'Đơn vị',
                        'CONSUMER' => 'Hộ tiêu thụ',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('building')
                    ->label('Nhà/Tòa')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),
                TextColumn::make('contact_name')
                    ->label('Người liên hệ')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('contact_phone')
                    ->label('SĐT')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                TextColumn::make('electric_meters_count')
                    ->label('Số công tơ')
                    ->counts('electricMeters')
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
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->defaultSort('name', 'asc')
            ->headerActions([
                CreateAction::make()->label('Thêm đơn vị con'),
            ])
            ->recordActions([
                ViewAction::make()->label('Xem'),
                EditAction::make()->label('Sửa'),
            ]);
    }
}
