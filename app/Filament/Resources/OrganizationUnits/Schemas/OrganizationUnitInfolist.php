<?php

namespace App\Filament\Resources\OrganizationUnits\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class OrganizationUnitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chung')
                    ->columns(3)
                    ->components([
                        TextEntry::make('name')
                            ->label('Tên đơn vị'),
                        TextEntry::make('code')
                            ->label('Mã đơn vị')
                            ->copyable(),
                        TextEntry::make('type')
                            ->label('Loại')
                            ->badge()
                            ->colors([
                                'blue' => 'ORGANIZATION',
                                'green' => 'UNIT',
                                'purple' => 'CONSUMER',
                            ])
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'UNIT' => 'Đơn vị',
                                'CONSUMER' => 'Khách hàng',
                                default => $state,
                            }),
                    ]),

                Section::make('Cấp bậc')
                    ->columns(2)
                    ->visible(fn ($record) => $record->type === 'CONSUMER')
                    ->components([
                        TextEntry::make('parent.name')
                            ->label('Đơn vị cấp trên')
                            ->placeholder('—')
                            ->url(fn ($record) => $record->parent ? route('filament.admin.resources.organization-units.view', ['record' => $record->parent]) : null)
                            ->color('primary')
                            ->icon('heroicon-o-arrow-top-right-on-square'),
                        TextEntry::make('status')
                            ->label('Trạng thái')
                            ->badge()
                            ->colors([
                                'success' => 'ACTIVE',
                                'danger' => 'INACTIVE',
                            ])
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'ACTIVE' => 'Hoạt động',
                                'INACTIVE' => 'Ngừng',
                                default => $state,
                            }),
                        TextEntry::make('parent.contact_name')
                            ->label('Người đại diện (đơn vị cha)')
                            ->placeholder('—'),
                        TextEntry::make('parent.contact_phone')
                            ->label('SĐT đại diện (đơn vị cha)')
                            ->placeholder('—')
                            ->copyable(),
                    ]),

                Section::make('Đơn vị con')
                    ->visible(fn ($record) => $record->type === 'UNIT')
                    ->components([
                        TextEntry::make('children_count')
                            ->label('Tổng số đơn vị con')
                            ->state(fn ($record) => $record->children()->count())
                            ->badge()
                            ->color('info'),
                        TextEntry::make('status')
                            ->label('Trạng thái')
                            ->badge()
                            ->colors([
                                'success' => 'ACTIVE',
                                'danger' => 'INACTIVE',
                            ])
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'ACTIVE' => 'Hoạt động',
                                'INACTIVE' => 'Ngừng',
                                default => $state,
                            }),
                    ]),

                Section::make('Thông tin liên hệ')
                    ->columns(2)
                    ->components([
                        TextEntry::make('contact_name')
                            ->label('Người liên hệ')
                            ->placeholder('—'),
                        TextEntry::make('contact_phone')
                            ->label('SĐT liên hệ')
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('—')
                            ->copyable(),
                    ]),

                Section::make('Địa chỉ')
                    ->columns(2)
                    ->components([
                        TextEntry::make('address')
                            ->label('Địa chỉ hộ tiêu thụ')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('building')
                            ->label('Nhà/Tòa nhà')
                            ->placeholder('—')
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('Ghi chú')
                    ->components([
                        TextEntry::make('notes')
                            ->label('Ghi chú')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
