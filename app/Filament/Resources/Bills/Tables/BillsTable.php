<?php

namespace App\Filament\Resources\Bills\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class BillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('billing_month')
                    ->label('Tháng')
                    ->date('m/Y')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('organizationUnit.name')
                    ->label('Đơn vị')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('details_count')
                    ->label('Số công tơ')
                    ->counts('billDetails')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
                TextColumn::make('total_amount')
                    ->label('Tổng tiền')
                    ->money('VND', true)
                    ->sortable()
                    ->weight('bold')
                    ->alignRight(),
                TextColumn::make('payment_status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'UNPAID' => 'warning',
                        'PARTIAL' => 'info',
                        'PAID' => 'success',
                        'OVERDUE' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'UNPAID' => 'Chưa TT',
                        'PARTIAL' => 'TT 1 phần',
                        'PAID' => 'Đã TT',
                        'OVERDUE' => 'Quá hạn',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Hạn TT')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('payment_status')
                    ->form([
                        Select::make('payment_status')
                            ->label('Trạng thái')
                            ->options([
                                'UNPAID' => 'Chưa thanh toán',
                                'PARTIAL' => 'Thanh toán 1 phần',
                                'PAID' => 'Đã thanh toán',
                                'OVERDUE' => 'Quá hạn',
                            ])
                    ])
                    ->query(function ($query, $data) {
                        return $query->when($data['payment_status'] ?? null, fn($q, $s) => $q->where('payment_status', $s));
                    }),

                Filter::make('billing_month')
                    ->form([
                        DatePicker::make('from')->label('Từ tháng')->displayFormat('m/Y'),
                        DatePicker::make('until')->label('Đến tháng')->displayFormat('m/Y'),
                    ])
                    ->query(function ($query, $data) {
                        return $query->when(isset($data['from']), fn($q) => $q->where('billing_month', '>=', $data['from']))
                                     ->when(isset($data['until']), fn($q) => $q->where('billing_month', '<=', $data['until']));
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Xuất Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn () => 'hoa-don-dien-' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                            ->withColumns([
                                \pxlrbt\FilamentExcel\Columns\Column::make('billing_month')
                                    ->heading('Tháng')
                                    ->formatStateUsing(fn ($state) => $state?->format('m/Y')),
                                \pxlrbt\FilamentExcel\Columns\Column::make('organizationUnit.name')->heading('Đơn vị/Hộ tiêu thụ'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('organizationUnit.building')->heading('Nhà/Tòa'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('details_count')->heading('Số công tơ'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('total_amount')
                                    ->heading('Tổng tiền (VNĐ)')
                                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')),
                                \pxlrbt\FilamentExcel\Columns\Column::make('payment_status')
                                    ->heading('Trạng thái')
                                    ->formatStateUsing(fn ($state) => match($state) {
                                        'UNPAID' => 'Chưa thanh toán',
                                        'PARTIAL' => 'Thanh toán 1 phần',
                                        'PAID' => 'Đã thanh toán',
                                        'OVERDUE' => 'Quá hạn',
                                        default => $state
                                    }),
                                \pxlrbt\FilamentExcel\Columns\Column::make('due_date')
                                    ->heading('Hạn thanh toán')
                                    ->formatStateUsing(fn ($state) => $state?->format('d/m/Y')),
                                \pxlrbt\FilamentExcel\Columns\Column::make('created_at')
                                    ->heading('Ngày tạo')
                                    ->formatStateUsing(fn ($state) => $state?->format('d/m/Y H:i')),
                            ])
                    ])
                    ->after(function () {
                        activity()
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'file_name' => 'hoa-don-dien-' . date('Y-m-d') . '.xlsx',
                                'export_type' => 'all',
                            ])
                            ->log('Xuất danh sách hóa đơn');
                    }),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo Hóa đơn mới'),
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Xuất đã chọn')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn () => 'hoa-don-dien-selected-' . date('Y-m-d'))
                        ])
                        ->after(function ($records) {
                            activity()
                                ->causedBy(auth()->user())
                                ->withProperties([
                                    'file_name' => 'hoa-don-dien-selected-' . date('Y-m-d') . '.xlsx',
                                    'export_type' => 'selected',
                                    'record_count' => $records->count(),
                                ])
                                ->log('Xuất hóa đơn đã chọn');
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->payment_status !== 'PAID'),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->payment_status !== 'PAID'),
            ]);
    }
}
