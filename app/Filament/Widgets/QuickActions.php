<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ElectricMeters\ElectricMeterResource;
use App\Filament\Resources\MeterReadings\MeterReadingResource;
use App\Filament\Resources\OrganizationUnits\OrganizationUnitResource;
use App\Imports\ComprehensiveBillingImport;
use App\Exports\ComprehensiveBillingExport;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class QuickActions extends Widget implements HasForms, HasActions
{
    use CanPoll;
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.widgets.quick-actions';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function canShowImportButton(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    // Quick Action 1: Create Organization Unit
    public function createOrgUnitAction(): Action
    {
        return Action::make('createOrgUnit')
            ->label('Đơn vị/Hộ tiêu thụ')
            ->icon('heroicon-o-user-group')
            ->color('primary')
            ->extraAttributes(['class' => 'w-full'])
            ->url(OrganizationUnitResource::getUrl('create'));
    }

    // Quick Action 2: Create Meter
    public function createMeterAction(): Action
    {
        return Action::make('createMeter')
            ->label('Công tơ điện')
            ->icon('heroicon-o-light-bulb')
            ->color('success')
            ->extraAttributes(['class' => 'w-full'])
            ->url(ElectricMeterResource::getUrl('create'));
    }

    // Quick Action 3: Create Reading
    public function createReadingAction(): Action
    {
        return Action::make('createReading')
            ->label('Ghi chỉ số')
            ->icon('heroicon-o-pencil-square')
            ->color('info')
            ->extraAttributes(['class' => 'w-full'])
            ->url(MeterReadingResource::getUrl('create'));
    }

    // Import Action
    public function importAction(): Action
    {
        return Action::make('import')
            ->label('Import tổng hợp')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->extraAttributes(['class' => 'w-full'])
            ->modalHeading('Import tổng hợp dữ liệu')
            ->modalDescription('Đồng thời: Đơn vị, Hộ tiêu thụ, Công tơ, Chỉ số')
            ->modalSubmitActionLabel('Import ngay')
            ->modalWidth('xl')
            ->form([
                FileUpload::make('file')
                    ->label('Chọn file')
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->maxSize(5120) // 5MB
                    ->helperText('Hỗ trợ: CSV, XLSX, XLS • Tối đa: 5MB')
                    ->disk('local')
                    ->directory('temp-imports')
                    ->visibility('private'),
            ])
            ->action(function (array $data) {
                try {
                    $filePath = storage_path('app/' . $data['file']);
                    
                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->title('Lỗi!')
                            ->body('File không tồn tại.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $import = new ComprehensiveBillingImport();
                    Excel::import($import, $filePath);

                    $stats = $import->getStats();

                    // Log activity
                    activity()
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'stats' => $stats,
                            'file_name' => basename($data['file']),
                        ])
                        ->log('Import tổng hợp dữ liệu');

                    // Delete temp file
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }

                    Notification::make()
                        ->title('Import thành công!')
                        ->body(sprintf(
                            'Đã tạo: %d đơn vị, %d công tơ, %d chỉ số',
                            $stats['organizations_created'] ?? 0,
                            $stats['meters_created'] ?? 0,
                            $stats['readings_created'] ?? 0
                        ))
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import thất bại!')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    // Export Action
    public function exportAction(): Action
    {
        return Action::make('export')
            ->label('Export tổng hợp')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->extraAttributes(['class' => 'w-full'])
            ->modalHeading('Export dữ liệu tổng hợp')
            ->modalDescription('Xuất dữ liệu chỉ số công tơ theo tháng')
            ->modalSubmitActionLabel('Xuất Excel')
            ->modalWidth('md')
            ->form([
                Select::make('month')
                    ->label('Tháng')
                    ->options([
                        1 => 'Tháng 1',
                        2 => 'Tháng 2',
                        3 => 'Tháng 3',
                        4 => 'Tháng 4',
                        5 => 'Tháng 5',
                        6 => 'Tháng 6',
                        7 => 'Tháng 7',
                        8 => 'Tháng 8',
                        9 => 'Tháng 9',
                        10 => 'Tháng 10',
                        11 => 'Tháng 11',
                        12 => 'Tháng 12',
                    ])
                    ->default(now()->month)
                    ->required()
                    ->native(false),
                
                Select::make('year')
                    ->label('Năm')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                            $years[$i] = "Năm {$i}";
                        }
                        return $years;
                    })
                    ->default(now()->year)
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data) {
                try {
                    $month = $data['month'];
                    $year = $data['year'];
                    $fileName = "export-chiso-thang-{$month}-nam-{$year}-" . date('His') . '.xlsx';
                    
                    // Log activity
                    activity()
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'file_name' => $fileName,
                            'month' => $month,
                            'year' => $year,
                        ])
                        ->log('Export dữ liệu tổng hợp');

                    Notification::make()
                        ->title('Xuất dữ liệu thành công!')
                        ->body("Đã xuất dữ liệu tháng {$month}/{$year}")
                        ->success()
                        ->send();

                    return Excel::download(new ComprehensiveBillingExport($month, $year), $fileName);
                    
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Lỗi khi xuất dữ liệu!')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function downloadTemplate()
    {
        $templatePath = storage_path('app/templates/import-tong-hop-template.csv');
        
        if (!file_exists($templatePath)) {
            Notification::make()
                ->title('Lỗi!')
                ->body('File mẫu không tồn tại.')
                ->danger()
                ->send();
            return;
        }

        return response()->download(
            $templatePath,
            'mau-import-tong-hop-' . date('Y-m-d') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
