<?php

namespace App\Filament\Resources\MeterReadings\Pages;

use App\Filament\Resources\MeterReadings\MeterReadingResource;
use App\Filament\Resources\MeterReadings\Schemas\MeterReadingInfolist;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewMeterReading extends ViewRecord
{
    protected static string $resource = MeterReadingResource::class;
    protected static ?string $title = 'Xem Chỉ số công tơ';

    public function infolist(Schema $schema): Schema
    {
        return MeterReadingInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Sửa ghi chú')
                ->icon('heroicon-o-pencil')
                ->modalHeading('Sửa ghi chú')
                ->modalDescription('Chỉ có thể sửa ghi chú, không thể thay đổi chỉ số')
                ->form([
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Ghi chú')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Ghi chú về lần đọc này (nếu có)'),
                ]),
            DeleteAction::make(),
        ];
    }
}
