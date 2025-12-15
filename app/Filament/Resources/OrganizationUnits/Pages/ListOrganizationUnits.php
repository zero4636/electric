<?php

namespace App\Filament\Resources\OrganizationUnits\Pages;

use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use App\Imports\OrganizationUnitImport;
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
use PhpOffice\PhpSpreadsheet\Style\Font;

class ListOrganizationUnits extends ListRecords
{
    protected static string $resource = OrganizationUnitResource::class;
    protected static ?string $title = 'Danh sách Đơn vị tổ chức';

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
                        
                        // Header row
                        $headers = [
                            'Mã *',
                            'Tên *',
                            'Loại * (UNIT/CONSUMER)',
                            'Mã đơn vị cấp trên',
                            'Nhà/Tòa',
                            'Địa chỉ',
                            'Người liên hệ',
                            'Số điện thoại',
                            'Email',
                            'Trạng thái (ACTIVE/INACTIVE)',
                            'Ghi chú'
                        ];
                        
                        $sheet->fromArray($headers, null, 'A1');
                        
                        // Style header
                        $headerStyle = $sheet->getStyle('A1:K1');
                        $headerStyle->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
                        $headerStyle->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('4F46E5'); // Indigo
                        
                        // Sample data
                        $sheet->fromArray([
                            ['HQBK', 'Hội Quỹ Bách Khoa', 'UNIT', '', '', '', '', '', '', 'ACTIVE', ''],
                            ['HQBK-VP', 'Văn phòng Hội Quỹ', 'CONSUMER', 'HQBK', 'T3', 'Tầng 2', 'Nguyễn Văn A', '0123456789', 'vphoiquy@hust.edu.vn', 'ACTIVE', ''],
                        ], null, 'A2');
                        
                        // Auto size columns
                        foreach (range('A', 'K') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }
                        
                        $writer = new Xlsx($spreadsheet);
                        $writer->save('php://output');
                    }, 'mau-don-vi-to-chuc.xlsx');
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
                        ->maxSize(5120) // 5MB
                        ->helperText('Hỗ trợ: .xlsx, .xls, .csv (Tối đa 5MB)')
                ])
                ->action(function (array $data) {
                    try {
                        $filePath = Storage::path($data['file']);
                        $import = new OrganizationUnitImport();
                        
                        Excel::import($import, $filePath);
                        
                        $errors = $import->getErrors();
                        $successCount = $import->getSuccessCount();
                        
                        if (!empty($errors)) {
                            Notification::make()
                                ->title('Import hoàn tất với lỗi')
                                ->body('Đã import ' . $successCount . ' bản ghi. ' . count($errors) . ' lỗi:<br>' . implode('<br>', array_slice($errors, 0, 5)))
                                ->warning()
                                ->duration(10000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import thành công!')
                                ->body('Đã import ' . $successCount . ' đơn vị tổ chức.')
                                ->success()
                                ->send();
                        }
                        
                        // Clean up
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
