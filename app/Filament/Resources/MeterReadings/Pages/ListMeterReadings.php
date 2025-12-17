<?php

namespace App\Filament\Resources\MeterReadings\Pages;

use App\Filament\Resources\MeterReadings\MeterReadingResource;
use App\Imports\MeterReadingImport;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ListMeterReadings extends ListRecords
{
    protected static string $resource = MeterReadingResource::class;
    protected static ?string $title = 'Danh sách Chỉ số công tơ';
    
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
                            'Ngày ghi * (dd/mm/yyyy)',
                            'Chỉ số *',
                            'Người ghi',
                            'Ghi chú'
                        ];
                        
                        $sheet->fromArray($headers, null, 'A1');
                        
                        // Style header
                        $headerStyle = $sheet->getStyle('A1:E1');
                        $headerStyle->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
                        $headerStyle->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F59E0B'); // Amber
                        
                        // Sample data
                        $sheet->fromArray([
                            ['CTO-001', '01/12/2025', '1250.5', 'Nguyễn Văn A', ''],
                            ['CTO-002', '01/12/2025', '3450.0', 'Nguyễn Văn A', ''],
                        ], null, 'A2');
                        
                        foreach (range('A', 'E') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }
                        
                        $writer = new Xlsx($spreadsheet);
                        $writer->save('php://output');
                    }, 'mau-chi-so-cong-to.xlsx');
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
                        $import = new MeterReadingImport();
                        
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
                            ->log('Import chỉ số công tơ');
                        
                        if (!empty($errors)) {
                            Notification::make()
                                ->title('Import hoàn tất với lỗi')
                                ->body('Đã import ' . $successCount . ' chỉ số. ' . count($errors) . ' lỗi:<br>' . implode('<br>', array_slice($errors, 0, 5)))
                                ->warning()
                                ->duration(10000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import thành công!')
                                ->body('Đã import ' . $successCount . ' chỉ số công tơ.')
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
                            ->log('Import chỉ số công tơ thất bại');
                        
                        Notification::make()
                            ->title('Lỗi import!')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

