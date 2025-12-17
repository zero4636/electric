<?php

namespace App\Filament\Resources\OrganizationUnits\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class OrganizationUnitsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium')
                    ->placeholder('—'),
                    
                TextColumn::make('name')
                    ->label('Tên đơn vị/Hộ tiêu thụ')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(50),
                    
                BadgeColumn::make('type')
                    ->label('Loại')
                    ->colors([
                        'primary' => 'ORGANIZATION',
                        'success' => 'UNIT',
                        'warning' => 'CONSUMER',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'UNIT' => 'Đơn vị',
                        'CONSUMER' => 'Hộ tiêu thụ',
                        default => $state,
                    })
                    ->sortable(),
                    
                TextColumn::make('parent.name')
                    ->label('Đơn vị cấp trên')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('—')
                    ->toggleable(),
                    
                TextColumn::make('building')
                    ->label('Nhà/Tòa')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(),
                    
                TextColumn::make('contact_name')
                    ->label('Người liên hệ')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('contact_phone')
                    ->label('SĐT liên hệ')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->icon('heroicon-o-phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('address')
                    ->label('Địa chỉ')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('electric_meters_count')
                    ->label('Mã công tơ')
                    ->counts('electricMeters')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
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
                SelectFilter::make('type')
                    ->label('Loại đơn vị')
                    ->options([
                        'UNIT' => 'Đơn vị',
                        'CONSUMER' => 'Hộ tiêu thụ',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('parent_id')
                    ->label('Đơn vị cấp trên')
                    ->options(function () {
                        return \App\Models\OrganizationUnit::query()
                            ->whereNull('parent_id')
                            ->orWhereHas('children')
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                    
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
                            ->withFilename(fn () => 'don-vi-to-chuc-' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                            ->withColumns([
                                \pxlrbt\FilamentExcel\Columns\Column::make('code')->heading('Mã'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('name')->heading('Tên đơn vị/Hộ tiêu thụ'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('type')
                                    ->heading('Loại')
                                    ->formatStateUsing(fn ($state) => match($state) {
                                        'UNIT' => 'Đơn vị',
                                        'CONSUMER' => 'Hộ tiêu thụ',
                                        default => $state
                                    }),
                                \pxlrbt\FilamentExcel\Columns\Column::make('parent.name')->heading('Đơn vị cấp trên'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('building')->heading('Nhà/Tòa'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('address')->heading('Địa chỉ'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('contact_name')->heading('Người liên hệ'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('contact_phone')->heading('SĐT'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('email')->heading('Email'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('status')
                                    ->heading('Trạng thái')
                                    ->formatStateUsing(fn ($state) => $state === 'ACTIVE' ? 'Hoạt động' : 'Ngừng'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('created_at')
                                    ->heading('Ngày tạo')
                                    ->formatStateUsing(fn ($state) => $state?->format('d/m/Y H:i')),
                            ])
                    ])
                    ->after(function () {
                        activity()
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'file_name' => 'don-vi-to-chuc-' . date('Y-m-d') . '.xlsx',
                                'export_type' => 'all',
                            ])
                            ->log('Xuất danh sách đơn vị/hộ tiêu thụ');
                    }),
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
                                ->withFilename(fn () => 'don-vi-to-chuc-selected-' . date('Y-m-d'))
                        ])
                        ->after(function ($records) {
                            activity()
                                ->causedBy(auth()->user())
                                ->withProperties([
                                    'file_name' => 'don-vi-to-chuc-selected-' . date('Y-m-d') . '.xlsx',
                                    'export_type' => 'selected',
                                    'record_count' => $records->count(),
                                ])
                                ->log('Xuất đơn vị/hộ tiêu thụ đã chọn');
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
