<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use App\Helpers\NumberToWords;
use App\Models\Bill;
use App\Models\ElectricMeter;
use App\Models\OrganizationUnit;
use App\Services\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;
    protected static ?string $title = 'Danh sách Hóa đơn';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_bills')
                ->label('Tạo hóa đơn tự động')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->form([
                    Select::make('billing_month')
                        ->label('Tháng')
                        ->options([
                            1 => 'Tháng 1', 2 => 'Tháng 2', 3 => 'Tháng 3',
                            4 => 'Tháng 4', 5 => 'Tháng 5', 6 => 'Tháng 6',
                            7 => 'Tháng 7', 8 => 'Tháng 8', 9 => 'Tháng 9',
                            10 => 'Tháng 10', 11 => 'Tháng 11', 12 => 'Tháng 12',
                        ])
                        ->required()
                        ->default(now()->month)
                        ->native(false),
                    
                    Select::make('billing_year')
                        ->label('Năm')
                        ->options(function () {
                            $currentYear = now()->year;
                            $years = [];
                            // 10 năm trước đến năm hiện tại
                            for ($y = $currentYear - 10; $y <= $currentYear; $y++) {
                                $years[$y] = $y;
                            }
                            return array_reverse($years, true);
                        })
                        ->required()
                        ->default(now()->year)
                        ->searchable()
                        ->native(false),

                    DatePicker::make('due_date')
                        ->label('Hạn thanh toán')
                        ->required()
                        ->default(now()->addDays(30))
                        ->native(false),

                    Select::make('organization_unit_id')
                        ->label('Đơn vị / Hợp đồng')
                        ->options(function () {
                            $units = OrganizationUnit::where('status', 'ACTIVE')
                                ->where('type', 'UNIT')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn($unit) => [$unit->id => "🏢 {$unit->name}"]);
                            
                            $independent = OrganizationUnit::where('status', 'ACTIVE')
                                ->where('type', 'CONSUMER')
                                ->whereNull('parent_id')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn($consumer) => [$consumer->id => "📋 {$consumer->name} (HĐ tự do)"]);
                            
                            return $units->union($independent);
                        })
                        ->searchable()
                        ->placeholder('Chọn đơn vị hoặc bỏ trống để tạo tất cả')
                        ->helperText('🏢 = Đơn vị chủ quản (tạo cho tất cả hộ tiêu thụ), 📋 = Hợp đồng tự do')
                        ->live()
                        ->native(false),

                    Select::make('electric_meter_ids')
                        ->label('Công tơ cụ thể (tùy chọn)')
                        ->options(function (callable $get) {
                            $orgUnitId = $get('organization_unit_id');
                            
                            if (!$orgUnitId) {
                                return [];
                            }

                            $orgUnit = OrganizationUnit::with('children')->find($orgUnitId);
                            if (!$orgUnit) {
                                return [];
                            }

                            // Case 1: UNIT - get meters from all CONSUMER children
                            if ($orgUnit->type === 'UNIT') {
                                $consumerIds = $orgUnit->children->pluck('id')->toArray();
                                return ElectricMeter::whereIn('organization_unit_id', $consumerIds)
                                    ->where('status', 'ACTIVE')
                                    ->with('organizationUnit')
                                    ->orderBy('meter_number')
                                    ->get()
                                    ->mapWithKeys(fn($meter) => [
                                        $meter->id => "{$meter->meter_number} ({$meter->organizationUnit->name})"
                                    ]);
                            }
                            
                            // Case 2: Independent CONSUMER - get meters directly
                            if ($orgUnit->type === 'CONSUMER' && $orgUnit->parent_id === null) {
                                return ElectricMeter::where('organization_unit_id', $orgUnit->id)
                                    ->where('status', 'ACTIVE')
                                    ->orderBy('meter_number')
                                    ->get()
                                    ->mapWithKeys(fn($meter) => [
                                        $meter->id => $meter->meter_number
                                    ]);
                            }
                            
                            return [];
                        })
                        ->searchable()
                        ->multiple()
                        ->placeholder('Bỏ trống = Tạo cho tất cả công tơ')
                        ->helperText('Chọn công tơ cụ thể hoặc bỏ trống để tạo cho tất cả')
                        ->visible(fn (callable $get) => $get('organization_unit_id') !== null)
                        ->native(false),
                ])
                ->action(function (array $data) {
                    $billingService = app(BillingService::class);
                    // Tạo billing month từ tháng và năm đã chọn
                    $billingMonth = Carbon::createFromDate($data['billing_year'], $data['billing_month'], 1)->startOfMonth();
                    $dueDate = Carbon::parse($data['due_date']);

                    try {
                        DB::beginTransaction();

                        // Nếu chọn công tơ cụ thể
                        if (!empty($data['electric_meter_ids'])) {
                            $results = $billingService->createBillsForMeters(
                                $data['electric_meter_ids'],
                                $billingMonth,
                                $dueDate
                            );

                            // Kiểm tra nếu không có công tơ nào được tạo
                            if ($results['success'] === 0 && $results['failed'] === 0) {
                                DB::rollBack();
                                Notification::make()
                                    ->title('Không có dữ liệu')
                                    ->body("Không tìm thấy chỉ số đọc trong tháng {$billingMonth->format('m/Y')} cho các công tơ đã chọn")
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $message = "Thành công: {$results['success']} công tơ";
                            if (($results['skipped'] ?? 0) > 0) {
                                $message .= ", bỏ qua {$results['skipped']} (không có chỉ số)";
                            }
                            if ($results['failed'] > 0) {
                                $message .= ", Lỗi: {$results['failed']} công tơ";
                            }

                            DB::commit();

                            Notification::make()
                                ->title('Tạo hóa đơn hoàn tất')
                                ->body($message)
                                ->success()
                                ->send();

                            // Hiển thị tối đa 5 lỗi đầu
                            foreach (array_slice($results['errors'], 0, 5) as $error) {
                                Notification::make()
                                    ->title($error['meter_number'])
                                    ->body($error['message'])
                                    ->warning()
                                    ->send();
                            }

                            if (count($results['errors']) > 5) {
                                Notification::make()
                                    ->body('Và ' . (count($results['errors']) - 5) . ' lỗi khác...')
                                    ->warning()
                                    ->send();
                            }

                        } else {
                            // Tạo cho toàn bộ đơn vị (và các đơn vị con)
                            if (isset($data['organization_unit_id'])) {
                                $result = $billingService->createBillForOrganizationUnit(
                                    $data['organization_unit_id'],
                                    $billingMonth,
                                    $dueDate
                                );
                            } else {
                                // Tạo cho tất cả đơn vị consumer
                                $consumers = OrganizationUnit::where('type', 'CONSUMER')
                                    ->where('status', 'ACTIVE')
                                    ->get();
                                
                                $totalCreated = 0;
                                $totalErrors = [];
                                
                                foreach ($consumers as $consumer) {
                                    try {
                                        $result = $billingService->createBillForOrganizationUnit(
                                            $consumer->id,
                                            $billingMonth,
                                            $dueDate
                                        );
                                        $totalCreated += $result['details_created'];
                                        $totalErrors = array_merge($totalErrors, $result['errors']);
                                    } catch (\Exception $e) {
                                        $totalErrors[] = "Lỗi tại {$consumer->name}: " . $e->getMessage();
                                    }
                                }
                                
                                $result = [
                                    'details_created' => $totalCreated,
                                    'total_meters' => $totalCreated + count($totalErrors),
                                    'errors' => $totalErrors
                                ];
                            }

                            // Kiểm tra nếu không có công tơ nào được tạo
                            if ($result['details_created'] === 0 && count($result['errors']) === 0) {
                                DB::rollBack();
                                Notification::make()
                                    ->title('Không có dữ liệu')
                                    ->body("Không tìm thấy chỉ số đọc trong tháng {$billingMonth->format('m/Y')} cho đơn vị này")
                                    ->warning()
                                    ->send();
                                return;
                            }

                            DB::commit();

                            $message = "Đã tạo {$result['details_created']}/{$result['total_meters']} công tơ thành công";
                            if (($result['skipped'] ?? 0) > 0) {
                                $message .= ", bỏ qua {$result['skipped']} (không có chỉ số)";
                            }
                            if (count($result['errors']) > 0) {
                                $message .= ", " . count($result['errors']) . " lỗi";
                            }

                            Notification::make()
                                ->title('Tạo hóa đơn hoàn tất')
                                ->body($message)
                                ->success()
                                ->send();

                            // Hiển thị tối đa 5 lỗi đầu
                            foreach (array_slice($result['errors'], 0, 5) as $error) {
                                Notification::make()
                                    ->body($error)
                                    ->warning()
                                    ->send();
                            }

                            if (count($result['errors']) > 5) {
                                Notification::make()
                                    ->body('Và ' . (count($result['errors']) - 5) . ' lỗi khác...')
                                    ->warning()
                                    ->send();
                            }
                        }

                    } catch (\Exception $e) {
                        DB::rollBack();

                        Notification::make()
                            ->title('Lỗi tạo hóa đơn')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
            Action::make('print_bulk_pdf')
                ->label('In PDF đơn vị tổ chức')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    Select::make('billing_month')
                        ->label('Tháng')
                        ->options([
                            1 => 'Tháng 1', 2 => 'Tháng 2', 3 => 'Tháng 3',
                            4 => 'Tháng 4', 5 => 'Tháng 5', 6 => 'Tháng 6',
                            7 => 'Tháng 7', 8 => 'Tháng 8', 9 => 'Tháng 9',
                            10 => 'Tháng 10', 11 => 'Tháng 11', 12 => 'Tháng 12',
                        ])
                        ->required()
                        ->default(now()->month)
                        ->native(false),
                    
                    Select::make('billing_year')
                        ->label('Năm')
                        ->options(function () {
                            $currentYear = now()->year;
                            $years = [];
                            for ($y = $currentYear - 10; $y <= $currentYear; $y++) {
                                $years[$y] = $y;
                            }
                            return array_reverse($years, true);
                        })
                        ->required()
                        ->default(now()->year)
                        ->searchable()
                        ->native(false),

                    Select::make('organization_unit_id')
                        ->label('Đơn vị chủ quản')
                        ->options(function () {
                            return OrganizationUnit::where('status', 'ACTIVE')
                                ->where('type', 'UNIT')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn($unit) => [$unit->id => $unit->name]);
                        })
                        ->searchable()
                        ->required()
                        ->placeholder('Chọn đơn vị')
                        ->helperText('In tất cả hóa đơn của các hộ tiêu thụ thuộc đơn vị này')
                        ->native(false),
                    
                    TextInput::make('bill_number_start')
                        ->label('Số phiếu bắt đầu')
                        ->default(fn () => rand(100, 999))
                        ->numeric()
                        ->required(),
                    
                    TextInput::make('signer_name')
                        ->label('Người ký (Phòng CSVC)')
                        ->placeholder('Hồ Thành Long'),
                ])
                ->action(function (array $data) {
                    $billingMonth = Carbon::createFromDate($data['billing_year'], $data['billing_month'], 1)->startOfMonth();
                    $orgUnit = OrganizationUnit::with('children')->find($data['organization_unit_id']);
                    
                    if (!$orgUnit) {
                        Notification::make()
                            ->title('Lỗi')
                            ->body('Không tìm thấy đơn vị')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Lấy tất cả hóa đơn của các hộ tiêu thụ con trong tháng này
                    $consumerIds = $orgUnit->children->pluck('id')->toArray();
                    $bills = Bill::whereIn('organization_unit_id', $consumerIds)
                        ->where('billing_month', $billingMonth)
                        ->with(['organizationUnit', 'billDetails.electricMeter.substation'])
                        ->get();
                    
                    if ($bills->isEmpty()) {
                        Notification::make()
                            ->title('Không có dữ liệu')
                            ->body("Không tìm thấy hóa đơn nào trong tháng {$billingMonth->format('m/Y')} cho đơn vị này")
                            ->warning()
                            ->send();
                        return;
                    }
                    
                    // Tạo PDF với tất cả hóa đơn
                    $allMeters = [];
                    $billNumber = (int) $data['bill_number_start'];
                    
                    foreach ($bills as $bill) {
                        $consumer = $bill->organizationUnit;
                        
                        foreach ($bill->billDetails as $detail) {
                            $meter = $detail->electricMeter;
                            
                            // Lấy chỉ số từ MeterReading
                            $endDate = $bill->billing_month->copy()->endOfMonth();
                            $startDate = $bill->billing_month->copy()->startOfMonth();
                            
                            $currentReading = \App\Models\MeterReading::where('electric_meter_id', $meter->id)
                                ->whereBetween('reading_date', [$startDate, $endDate])
                                ->orderBy('reading_date', 'desc')
                                ->first();
                            
                            $previousReading = $currentReading 
                                ? \App\Models\MeterReading::where('electric_meter_id', $meter->id)
                                    ->where('reading_date', '<', $currentReading->reading_date)
                                    ->orderBy('reading_date', 'desc')
                                    ->first()
                                : null;
                            
                            $allMeters[] = [
                                'name' => $consumer->name,
                                'code' => $consumer->code,
                                'location' => $meter->installation_location ?? ($consumer->building ?? $consumer->address),
                                'meter_number' => $meter->meter_number,
                                'current_reading' => $currentReading ? $currentReading->reading_value : 0,
                                'previous_reading' => $previousReading ? $previousReading->reading_value : 0,
                                'hsn' => $detail->hsn,
                                'consumption' => $detail->consumption,
                                'price' => $detail->price_per_kwh,
                                'amount' => $detail->amount,
                                'substation' => $meter->substation->name ?? '',
                                'subsidy' => $detail->subsidized_applied > 0 ? number_format($detail->subsidized_applied, 0, ',', '.') : '',
                            ];
                        }
                    }
                    
                    $totalAmount = $bills->sum('total_amount');
                    
                    $pdf = Pdf::loadView('pdf.organization-unit-bill', [
                        'organization' => $orgUnit,
                        'meters' => $allMeters,
                        'month' => $data['billing_month'],
                        'year' => $data['billing_year'],
                        'billNumber' => $billNumber,
                        'amountInWords' => NumberToWords::convert($totalAmount),
                        'signerName' => $data['signer_name'] ?? '',
                        'preparedBy' => auth()->user()->name ?? '',
                    ]);
                    
                    Notification::make()
                        ->title('In PDF thành công')
                        ->body("Đã tạo PDF cho {$bills->count()} hóa đơn")
                        ->success()
                        ->send();
                    
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->stream();
                    }, 'phieu-dien-' . $orgUnit->code . '-' . $data['billing_month'] . '-' . $data['billing_year'] . '.pdf');
                }),
        ];
    }
}
