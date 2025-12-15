<?php

namespace App\Filament\Resources\Substations\Pages;

use App\Filament\Resources\Substations\SubstationResource;
use App\Imports\SubstationImport;
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

class ListSubstations extends ListRecords
{
    protected static string $resource = SubstationResource::class;
    protected static ?string $title = 'Danh sách Trạm biến áp';

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
                            'Mã *',
                            'Tên *',
                            'Vị trí',
                            'Công suất (kVA)',
                            'Cấp điện áp',
                            'Ngày lắp đặt (dd/mm/yyyy)',
                            'Trạng thái (ACTIVE/INACTIVE/MAINTENANCE)',
                            'Ghi chú'
                        ];
                        
                        $sheet->fromArray($headers, null, 'A1');
                        
                        // Style header
                        $headerStyle = $sheet->getStyle('A1:H1');
                        $headerStyle->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
                        $headerStyle->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('EF4444'); // Red
                        
                        // Sample data
                        $sheet->fromArray([
                            ['TBA-01', 'Trạm biến áp T3', 'Tầng 1 tòa T3', '630', '22kV/0.4kV', '01/01/2020', 'ACTIVE', ''],
                            ['TBA-02', 'Trạm biến áp D2', 'Tầng hầm D2', '1000', '22kV/0.4kV', '15/03/2021', 'ACTIVE', 'Trạm mới'],
                        ], null, 'A2');
                        
                        foreach (range('A', 'H') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }
                        
                        $writer = new Xlsx($spreadsheet);
                        $writer->save('php://output');
                    }, 'mau-tram-bien-ap.xlsx');
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
                        $import = new SubstationImport();
                        
                        Excel::import($import, $filePath);
                        
                        $errors = $import->getErrors();
                        $successCount = $import->getSuccessCount();
                        
                        if (!empty($errors)) {
                            Notification::make()
                                ->title('Import hoàn tất với lỗi')
                                ->body('Đã import ' . $successCount . ' trạm biến áp. ' . count($errors) . ' lỗi:<br>' . implode('<br>', array_slice($errors, 0, 5)))
                                ->warning()
                                ->duration(10000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import thành công!')
                                ->body('Đã import ' . $successCount . ' trạm biến áp.')
                                ->success()
                                ->send();
                        }
                        
                        Storage::delete($data['file']);
                        
                    } catch (\Exception $e) {
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

