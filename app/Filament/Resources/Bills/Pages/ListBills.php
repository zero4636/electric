<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use App\Models\ElectricMeter;
use App\Models\OrganizationUnit;
use App\Services\BillingService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
                    DatePicker::make('billing_month')
                        ->label('Tháng lập hóa đơn')
                        ->displayFormat('m/Y')
                        ->format('Y-m-01')
                        ->required()
                        ->default(now()->startOfMonth())
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
                        ->required()
                        ->placeholder('Chọn đơn vị hoặc hợp đồng tự do')
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
                    $billingMonth = Carbon::parse($data['billing_month']);
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

                            $message = "Thành công: {$results['success']} công tơ";
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
                            $result = $billingService->createBillForOrganizationUnit(
                                $data['organization_unit_id'],
                                $billingMonth,
                                $dueDate
                            );

                            DB::commit();

                            $message = "Đã tạo {$result['details_created']}/{$result['total_meters']} công tơ thành công";
                            if (count($result['errors']) > 0) {
                                $message .= " (" . count($result['errors']) . " lỗi)";
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
        ];
    }
}
