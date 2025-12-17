<?php

namespace App\Filament\Resources\ElectricMeters\Pages;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use App\Imports\ElectricMeterImport;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ListElectricMeters extends ListRecords
{
    protected static string $resource = ElectricMeterResource::class;
    protected static ?string $title = 'Danh sách Công tơ điện';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Tải file mẫu')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $spreadsheet = new Spreadsheet();
                        $sheet = $spreadsheet->getActiveSheet();
                        
                        $headers = [
                            'Mã công tơ *',
                            'Mã hộ tiêu thụ *',
                            'Loại công tơ * (1_PHASE/3_PHASE)',
                            'Loại hình * (RESIDENTIAL/COMMERCIAL/INDUSTRIAL)',
                            'Mã trạm biến áp',
                            'Vị trí đặt',
                            'HSN',
                            'Bao cấp kWh',
                            'Trạng thái (ACTIVE/INACTIVE)',
                            'Ghi chú'
                        ];
                        
                        $sheet->fromArray($headers, null, 'A1');
                        
                        // Style header
                        $headerStyle = $sheet->getStyle('A1:J1');
                        $headerStyle->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
                        $headerStyle->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('10B981'); // Green
                        
                        // Sample data
                        $sheet->fromArray([
                            ['CTO-001', 'HQBK-VP', '1_PHASE', 'RESIDENTIAL', 'TBA-01', 'Tầng 2, phòng 201', '1.0', '50', 'ACTIVE', ''],
                            ['CTO-002', 'HQBK-VP', '3_PHASE', 'COMMERCIAL', 'TBA-01', 'Tầng 3, phòng 301', '1.5', '0', 'ACTIVE', ''],
                        ], null, 'A2');
                        
                        foreach (range('A', 'J') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }
                        
                        $writer = new Xlsx($spreadsheet);
                        $writer->save('php://output');
                    }, 'mau-cong-to-dien.xlsx');
                }),
                
            Action::make('import')
                ->label('Nhập từ Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv'
                        ])
                        ->maxSize(5120)
                        ->helperText('Hỗ trợ: .xlsx, .xls, .csv (Tối đa 5MB)')
                ])
                ->action(function (array $data) {
                    try {
                        $filePath = Storage::path($data['file']);
                        $fileName = basename($data['file']);
                        $import = new ElectricMeterImport();
                        
                        Excel::import($import, $filePath);
                        
                        $errors = $import->getErrors();
                        $successCount = $import->getSuccessCount();
                        
                        // Log import activity
                        activity()
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'file_name' => $fileName,
                                'success_count' => $successCount,
                                'error_count' => count($errors),
                                'errors' => !empty($errors) ? array_slice($errors, 0, 10) : null,
                            ])
                            ->log('Import công tơ điện');
                        
                        if (!empty($errors)) {
                            Notification::make()
                                ->title('Import hoàn tất với lỗi')
                                ->body('Đã import ' . $successCount . ' công tơ. ' . count($errors) . ' lỗi:<br>' . implode('<br>', array_slice($errors, 0, 5)))
                                ->warning()
                                ->duration(10000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import thành công!')
                                ->body('Đã import ' . $successCount . ' công tơ điện.')
                                ->success()
                                ->send();
                        }
                        
                        Storage::delete($data['file']);
                        
                    } catch (\Exception $e) {
                        // Log failed import
                        activity()
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'file_name' => basename($data['file'] ?? 'unknown'),
                                'error_message' => $e->getMessage(),
                            ])
                            ->log('Import công tơ điện thất bại');
                        
                        Notification::make()
                            ->title('Lỗi import!')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
            CreateAction::make()
                ->label('Tạo mới'),
        ];
    }
}
