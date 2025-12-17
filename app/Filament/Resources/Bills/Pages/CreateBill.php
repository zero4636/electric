<?php

namespace App\Filament\Resources\Bills\Pages;

use App\Filament\Resources\Bills\BillResource;
use App\Models\ElectricMeter;
use App\Models\MeterReading;
use App\Models\BillDetail;
use App\Models\ElectricityTariff;
use App\Services\BillingService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateBill extends CreateRecord
{
    protected static string $resource = BillResource::class;
    protected static ?string $title = 'Tạo Hóa đơn';

    protected function getRedirectUrl(): string
    {
        $pages = $this->getResource()::getPages();
        if (isset($pages['view'])) {
            return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Validate before creating
        $validationResult = $this->validateBillCreation($data);
        if (!$validationResult['valid']) {
            Notification::make()
                ->title('Không thể tạo hóa đơn')
                ->body($validationResult['message'])
                ->danger()
                ->send();
            
            $this->halt(); // Stop the creation process
        }
        
        // Create the bill first
        $bill = parent::handleRecordCreation($data);
        
        // Auto-generate bill details for all meters in the organization
        $result = $this->generateBillDetails($bill);
        
        // Recalculate total amount
        $bill->update(['total_amount' => $bill->billDetails->sum('amount')]);
        
        // Show creation summary
        $this->showCreationSummary($result);
        
        return $bill;
    }

    private function validateBillCreation(array $data): array
    {
        $organizationId = $data['organization_unit_id'];
        $billingMonth = $data['billing_month'];
        
        // Check if bill already exists for this period
        $existingBill = \App\Models\Bill::where('organization_unit_id', $organizationId)
            ->whereMonth('billing_month', date('m', strtotime($billingMonth)))
            ->whereYear('billing_month', date('Y', strtotime($billingMonth)))
            ->first();
        
        if ($existingBill) {
            return [
                'valid' => false,
                'message' => "Đã tồn tại hóa đơn cho tháng này (ID: {$existingBill->id}). Vui lòng chọn tháng khác hoặc chỉnh sửa hóa đơn hiện tại."
            ];
        }
        
        // Check if organization has meters
        $meters = \App\Models\ElectricMeter::where('organization_unit_id', $organizationId)->get();
        if ($meters->count() == 0) {
            return [
                'valid' => false,
                'message' => "Đơn vị này không có công tơ nào. Vui lòng thêm công tơ trước khi tạo hóa đơn."
            ];
        }
        
        // Check if any meter has readings for the billing period
        $month = date('m', strtotime($billingMonth));
        $year = date('Y', strtotime($billingMonth));
        
        $totalReadings = 0;
        foreach ($meters as $meter) {
            $readings = \App\Models\MeterReading::where('electric_meter_id', $meter->id)
                ->whereMonth('reading_date', $month)
                ->whereYear('reading_date', $year)
                ->count();
            $totalReadings += $readings;
        }
        
        if ($totalReadings == 0) {
            return [
                'valid' => false,
                'message' => "Không có chỉ số công tơ nào trong tháng {$month}/{$year}. Vui lòng nhập chỉ số trước khi tạo hóa đơn."
            ];
        }
        
        return ['valid' => true];
    }

    private function generateBillDetails($bill): array
    {
        $meters = ElectricMeter::where('organization_unit_id', $bill->organization_unit_id)->get();
        $month = $bill->billing_month->month;
        $year = $bill->billing_month->year;
        
        $result = [
            'total_meters' => $meters->count(),
            'meters_with_readings' => 0,
            'meters_without_readings' => 0,
            'meters_multiple_readings' => 0,
            'details' => []
        ];
        
        foreach ($meters as $meter) {
            // Get all readings for this billing period
            $readings = MeterReading::where('electric_meter_id', $meter->id)
                ->whereMonth('reading_date', $month)
                ->whereYear('reading_date', $year)
                ->orderBy('reading_date', 'desc')
                ->get();
            
            $readingCount = $readings->count();
            $meterInfo = [
                'meter_number' => $meter->meter_number,
                'reading_count' => $readingCount,
                'status' => '',
                'consumption' => 0,
                'amount' => 0
            ];
            
            if ($readingCount == 0) {
                // No readings for this period
                $result['meters_without_readings']++;
                $meterInfo['status'] = 'Không có chỉ số';
                
                // Create zero bill detail
                BillDetail::create([
                    'bill_id' => $bill->id,
                    'electric_meter_id' => $meter->id,
                    'consumption' => 0,
                    'price_per_kwh' => 3000,
                    'hsn' => 1.0,
                    'amount' => 0,
                ]);
                
            } else {
                $result['meters_with_readings']++;
                
                if ($readingCount > 1) {
                    $result['meters_multiple_readings']++;
                    $meterInfo['status'] = "Có {$readingCount} chỉ số - sử dụng chỉ số mới nhất";
                } else {
                    $meterInfo['status'] = 'Có 1 chỉ số';
                }
                
                // Use latest reading for billing
                $latestReading = $readings->first();
                $consumption = $latestReading->getConsumption();
                
                // Get tariff for this meter
                $tariff = ElectricityTariff::where('tariff_type_id', $meter->tariff_type_id ?? 1)
                    ->latest()
                    ->first();
                
                $pricePerKwh = $tariff ? $tariff->price_per_kwh : 3000;
                $amount = $consumption * $pricePerKwh;
                
                $meterInfo['consumption'] = $consumption;
                $meterInfo['amount'] = $amount;
                
                BillDetail::create([
                    'bill_id' => $bill->id,
                    'electric_meter_id' => $meter->id,
                    'consumption' => $consumption,
                    'price_per_kwh' => $pricePerKwh,
                    'hsn' => 1.0,
                    'amount' => $amount,
                ]);
            }
            
            $result['details'][] = $meterInfo;
        }
        
        return $result;
    }

    private function showCreationSummary(array $result): void
    {
        $message = "✅ Tạo hóa đơn thành công!\n\n";
        $message .= "📊 Tổng quan:\n";
        $message .= "• Tổng số công tơ: {$result['total_meters']}\n";
        $message .= "• Có chỉ số: {$result['meters_with_readings']}\n";
        $message .= "• Không có chỉ số: {$result['meters_without_readings']}\n";
        
        if ($result['meters_multiple_readings'] > 0) {
            $message .= "• Có nhiều chỉ số: {$result['meters_multiple_readings']}\n";
        }
        
        Notification::make()
            ->title('Tạo hóa đơn thành công')
            ->body($message)
            ->success()
            ->duration(10000)
            ->send();
    }
}
