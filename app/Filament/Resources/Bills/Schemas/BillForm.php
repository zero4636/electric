<?php

namespace App\Filament\Resources\Bills\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Carbon\Carbon;

class BillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hóa đơn')
                    ->columns(2)
                    ->components([
                        Select::make('organization_unit_id')
                            ->label('Đơn vị')
                            ->relationship('organizationUnit','name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset total amount when organization changes
                                $set('total_amount', null);
                            }),

                        Select::make('billing_month')
                            ->label('Tháng lập hóa đơn')
                            ->options(function ($record = null) {
                                $options = [];
                                $currentDate = Carbon::now();
                                
                                // Generate last 24 months + next 6 months for more coverage
                                for ($i = -24; $i <= 6; $i++) {
                                    $date = $currentDate->copy()->addMonths($i);
                                    $value = $date->format('Y-m-01'); // Always first day of month
                                    $label = $date->format('m/Y');
                                    $options[$value] = $label;
                                }
                                
                                // If editing existing bill, ensure its value is in options
                                if ($record && $record->billing_month) {
                                    $billValue = $record->billing_month->format('Y-m-01');
                                    $billLabel = $record->billing_month->format('m/Y');
                                    $options[$billValue] = $billLabel;
                                }
                                
                                // Sort options by date
                                ksort($options);
                                
                                return $options;
                            })
                            ->default(fn ($record = null) => $record ? null : Carbon::now()->format('Y-m-01'))
                            ->required()
                            ->live()
                            ->disabled(fn ($record) => $record !== null) // Disable when editing
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Trigger meter info update when month changes
                                $set('total_amount', null);
                            }),

                        DatePicker::make('due_date')
                            ->label('Hạn thanh toán')
                            ->required(),

                        TextInput::make('total_amount')
                            ->label('Tổng tiền')
                            ->numeric()
                            ->disabled()
                            ->placeholder('Sẽ được tính tự động từ các công tơ'),

                        Placeholder::make('meter_info')
                            ->label('Thông tin công tơ')
                            ->content(function ($get, $record) {
                                $organizationId = $get('organization_unit_id');
                                $billingMonth = $get('billing_month');
                                
                                if (!$organizationId) {
                                    return 'Chọn đơn vị để xem thông tin công tơ';
                                }
                                
                                // Show note about editing restrictions
                                $editNote = '';
                                if ($record) {
                                    $editNote = "\n💡 Lưu ý: Tháng lập hóa đơn không thể sửa sau khi đã tạo.\n";
                                }
                                
                                // Check existing bill for this period (only for new bills)
                                if ($billingMonth && !$record) {
                                    $existingBill = \App\Models\Bill::where('organization_unit_id', $organizationId)
                                        ->whereMonth('billing_month', date('m', strtotime($billingMonth)))
                                        ->whereYear('billing_month', date('Y', strtotime($billingMonth)))
                                        ->first();
                                    
                                    if ($existingBill) {
                                        return "⚠️ ĐÃ TỒN TẠI hóa đơn cho tháng này (ID: {$existingBill->id})";
                                    }
                                }
                                
                                // Check meters for this organization
                                $meters = \App\Models\ElectricMeter::where('organization_unit_id', $organizationId)->get();
                                if ($meters->count() == 0) {
                                    return $editNote . '❌ Đơn vị này KHÔNG CÓ công tơ nào';
                                }
                                
                                $meterInfo = "✅ Đơn vị này có {$meters->count()} công tơ";
                                
                                if ($billingMonth) {
                                    $month = date('m', strtotime($billingMonth));
                                    $year = date('Y', strtotime($billingMonth));
                                    
                                    $readingStats = [];
                                    $totalReadings = 0;
                                    
                                    foreach ($meters as $meter) {
                                        $readings = \App\Models\MeterReading::where('electric_meter_id', $meter->id)
                                            ->whereMonth('reading_date', $month)
                                            ->whereYear('reading_date', $year)
                                            ->count();
                                        $readingStats[] = "Công tơ {$meter->meter_number}: {$readings} chỉ số";
                                        $totalReadings += $readings;
                                    }
                                    
                                    $meterInfo .= "\n📊 Chỉ số tháng {$month}/{$year}:\n" . implode("\n", $readingStats);
                                    
                                    if ($totalReadings == 0) {
                                        $meterInfo .= "\n\n❌ KHÔNG THỂ tạo hóa đơn: Không có chỉ số nào trong tháng này!";
                                    } else {
                                        $meterInfo .= "\n\n✅ Có thể tạo hóa đơn ({$totalReadings} chỉ số)";
                                    }
                                }
                                
                                return $editNote . $meterInfo;
                            }),

                        Select::make('payment_status')
                            ->label('Trạng thái')
                            ->options([
                                'UNPAID' => 'Chưa thanh toán',
                                'PAID' => 'Đã thanh toán',
                                'OVERDUE' => 'Quá hạn',
                            ])
                            ->default('UNPAID')
                            ->required(),
                    ]),
            ]);
    }
}
