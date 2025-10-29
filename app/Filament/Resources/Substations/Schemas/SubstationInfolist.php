<?php

namespace App\Filament\Resources\Substations\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class SubstationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin trạm')
                    ->columns(3)
                    ->components([
                        TextEntry::make('name')
                            ->label('Tên trạm'),
                        TextEntry::make('code')
                            ->label('Mã trạm')
                            ->copyable(),
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

                Section::make('Vị trí')
                    ->columns(1)
                    ->components([
                        TextEntry::make('location')
                            ->label('Địa chỉ')
                            ->columnSpanFull(),
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
