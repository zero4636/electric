<?php

namespace App\Filament\Resources\ElectricMeters\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables; 
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Models\MeterReading;

class MeterReadingsRelationManager extends RelationManager
{
    protected static string $relationship = 'meterReadings';
    protected static ?string $title = 'Lịch sử ghi chỉ số';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reading_date')
            ->defaultSort('reading_date', 'desc')
            ->columns([
                TextColumn::make('reading_date')
                    ->label('Ngày ghi')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reading_value')
                    ->label('Chỉ số')
                    ->numeric(2)
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('consumption')
                    ->label('Tiêu thụ')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $previous = MeterReading::where('electric_meter_id', $record->electric_meter_id)
                            ->where(function($q) use ($record) {
                                $q->where('reading_date', '<', $record->reading_date)
                                  ->orWhere(function($q2) use ($record) {
                                      $q2->where('reading_date', '=', $record->reading_date)
                                         ->where('id', '<', $record->id);
                                  });
                            })
                            ->latest('reading_date')
                            ->latest('id')
                            ->first();

                        if (!$previous) {
                            return '—';
                        }
                        $consumption = ($record->reading_value - $previous->reading_value) * $record->electricMeter->hsn;
                        return number_format($consumption, 2) . ' kWh';
                    })
                    ->color(fn ($state) => $state === '—' ? 'gray' : 'success'),
                TextColumn::make('reader_name')
                    ->label('Người ghi')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Ghi chú')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->notes)
                    ->placeholder('—'),
            ])
            ->filters([
                Filter::make('date_range')
                    ->label('Khoảng ngày')
                    ->form([
                        DatePicker::make('from')->label('Từ'),
                        DatePicker::make('until')->label('Đến'),
                    ])
                    ->query(fn ($query, $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $from) => $q->where('reading_date', '>=', $from))
                        ->when($data['until'] ?? null, fn ($q, $until) => $q->where('reading_date', '<=', $until))),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Thêm chỉ số')
                    ->modalHeading('Thêm chỉ số công tơ')
                    ->modalDescription('Nhập chỉ số mới cho công tơ này')
                    ->form([
                        DatePicker::make('reading_date')
                            ->label('Ngày ghi')
                            ->required()
                            ->native(false)
                            ->default(now())
                            ->displayFormat('d/m/Y'),
                        TextInput::make('reading_value')
                            ->label('Chỉ số mới (kWh)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('reader_name')
                            ->label('Người ghi')
                            ->default(auth()->user()?->name)
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        // inject electric_meter_id from parent record
                        $data['electric_meter_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->after(function ($record) {
                        // could dispatch event or log
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Sửa ghi chú')
                    ->modalHeading('Sửa ghi chú')
                    ->form([
                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(4)
                            ->maxLength(1000),
                    ]),
                DeleteAction::make()
                    ->label('Xóa'),
            ])
            ->paginationPageOptions([10,25,50]);
    }
}
