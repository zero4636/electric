<?php

namespace App\Filament\Resources\Substations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class SubstationsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã trạm')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->icon('heroicon-o-bolt'),
                    
                TextColumn::make('name')
                    ->label('Tên trạm biến áp')
                    ->searchable()
                    ->sortable()
                    ->limit(35)
                    ->wrap()
                    ->description(fn ($record) => $record->location),
                    
                TextColumn::make('location')
                    ->label('Vị trí')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('electric_meters_count')
                    ->label('Mã công tơ')
                    ->counts('electricMeters')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->sortable(),
                    
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
                    
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Hoạt động')
                    ->falseLabel('Ngừng hoạt động')
                    ->queries(
                        true: fn ($query) => $query->where('status', 'ACTIVE'),
                        false: fn ($query) => $query->where('status', 'INACTIVE'),
                    ),
                    
                TernaryFilter::make('has_meters')
                    ->label('Có công tơ')
                    ->placeholder('Tất cả')
                    ->trueLabel('Có công tơ')
                    ->falseLabel('Chưa có công tơ')
                    ->queries(
                        true: fn ($query) => $query->has('electricMeters'),
                        false: fn ($query) => $query->doesntHave('electricMeters'),
                    ),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Xuất Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn () => 'tram-bien-ap-' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                            ->withColumns([
                                \pxlrbt\FilamentExcel\Columns\Column::make('code')->heading('Mã trạm'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('name')->heading('Tên trạm biến áp'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('location')->heading('Vị trí'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('capacity')->heading('Công suất (kVA)'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('voltage_level')->heading('Cấp điện áp'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('installation_date')
                                    ->heading('Ngày lắp đặt')
                                    ->formatStateUsing(fn ($state) => $state?->format('d/m/Y')),
                                \pxlrbt\FilamentExcel\Columns\Column::make('electric_meters_count')->heading('Số công tơ'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('status')
                                    ->heading('Trạng thái')
                                    ->formatStateUsing(fn ($state) => match($state) {
                                        'ACTIVE' => 'Hoạt động',
                                        'INACTIVE' => 'Ngừng',
                                        'MAINTENANCE' => 'Bảo trì',
                                        default => $state
                                    }),
                                \pxlrbt\FilamentExcel\Columns\Column::make('notes')->heading('Ghi chú'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('created_at')
                                    ->heading('Ngày tạo')
                                    ->formatStateUsing(fn ($state) => $state?->format('d/m/Y H:i')),
                            ])
                    ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Xuất đã chọn')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn () => 'tram-bien-ap-selected-' . date('Y-m-d'))
                        ]),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code', 'asc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
