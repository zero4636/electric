<?php

namespace App\Filament\Resources\Buildings\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class BuildingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin tòa nhà')
                    ->columns(3)
                    ->components([
                        TextEntry::make('name')
                            ->label('Tên tòa nhà'),
                        TextEntry::make('code')
                            ->label('Mã tòa nhà')
                            ->copyable(),
                        TextEntry::make('substation.name')
                            ->label('Trạm biến áp')
                            ->placeholder('—'),
                    ]),

                Section::make('Vị trí')
                    ->columns(2)
                    ->components([
                        TextEntry::make('address')
                            ->label('Địa chỉ')
                            ->columnSpanFull(),
                        TextEntry::make('location')
                            ->label('Vị trí')
                            ->placeholder('—'),
                    ]),

                Section::make('Thông số')
                    ->columns(2)
                    ->components([
                        TextEntry::make('total_floors')
                            ->label('Số tầng')
                            ->numeric()
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

                Section::make('Công tơ điện')
                    ->columns(1)
                    ->components([
                        TextEntry::make('meterCount')
                            ->label('Số công tơ')
                            ->getStateUsing(function ($record) {
                                return $record->electricMeters()->count();
                            }),
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
