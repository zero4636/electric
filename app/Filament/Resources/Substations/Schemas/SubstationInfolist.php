<?php

namespace App\Filament\Resources\Substations\Schemas;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class SubstationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin trạm biến áp / Khu vực')
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
                    ->columns(2)
                    ->components([
                        TextEntry::make('location')
                            ->label('Khu vực')
                            ->placeholder('—'),
                        TextEntry::make('address')
                            ->label('Địa chỉ chi tiết')
                            ->placeholder('—'),
                    ]),

                // Danh sách công tơ được chuyển sang thẻ Quan hệ (Relation Manager) bên dưới để hỗ trợ phân trang
                Section::make('Tổng quan công tơ')
                    ->icon('heroicon-o-bolt')
                    ->columns(3)
                    ->components([
                        TextEntry::make('meters_count')
                            ->label('Tổng số công tơ')
                            ->getStateUsing(fn ($record) => $record->electricMeters()->count())
                            ->badge()
                            ->color('info')
                            ->weight('bold'),
                        TextEntry::make('active_meters')
                            ->label('Đang hoạt động')
                            ->getStateUsing(fn ($record) => $record->electricMeters()->where('status', 'ACTIVE')->count())
                            ->badge()
                            ->color('success'),
                        TextEntry::make('inactive_meters')
                            ->label('Ngừng hoạt động')
                            ->getStateUsing(fn ($record) => $record->electricMeters()->where('status', 'INACTIVE')->count())
                            ->badge()
                            ->color('danger'),
                    ]),
            ]);
    }
}
