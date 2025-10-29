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
                                'ORGANIZATION' => 'Tổ chức',
                                'UNIT' => 'Đơn vị',
                                'CONSUMER' => 'Khách hàng',
                                default => $state,
                            }),
                    ]),

                Section::make('Cấp bậc')
                    ->columns(2)
                    ->components([
                        TextEntry::make('parent.name')
                            ->label('Đơn vị cha')
                            ->placeholder('—'),
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
                            ->label('Điện thoại')
                            ->placeholder('—'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('—'),
                        TextEntry::make('address')
                            ->label('Địa chỉ')
                            ->placeholder('—'),
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
