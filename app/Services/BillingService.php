<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\ElectricMeter;
use App\Models\ElectricityTariff;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function createBillForMeter(ElectricMeter $meter, Carbon $billingMonth, Carbon $dueDate): ?Bill
    {
        return DB::transaction(function () use ($meter, $billingMonth, $dueDate) {
            // Kiểm tra đã có hóa đơn cho tháng này chưa
            $existingBill = Bill::where('organization_unit_id', $meter->organization_unit_id)
                ->where('billing_month', $billingMonth->copy()->startOfMonth())
                ->first();

            if ($existingBill) {
                // Kiểm tra meter này đã có trong bill_details chưa
                $existingDetail = BillDetail::where('bill_id', $existingBill->id)
                    ->where('electric_meter_id', $meter->id)
                    ->first();
                
                if ($existingDetail) {
                    throw new \Exception("Công tơ {$meter->meter_number} đã được tính trong hóa đơn tháng " . $billingMonth->format('m/Y'));
                }
            }

            $bill = Bill::firstOrCreate(
                [
                    'organization_unit_id' => $meter->organization_unit_id,
                    'billing_month' => $billingMonth->copy()->startOfMonth(),
                ],
                [
                    'due_date' => $dueDate,
                    'total_amount' => 0,
                    'payment_status' => 'UNPAID',
                ]
            );

            // Lấy chỉ số trong tháng thanh toán
            $endReading = MeterReading::where('electric_meter_id', $meter->id)
                ->whereBetween('reading_date', [
                    $billingMonth->copy()->startOfMonth(),
                    $billingMonth->copy()->endOfMonth()
                ])
                ->orderBy('reading_date', 'desc')
                ->first();

            // Nếu không có chỉ số trong tháng này, bỏ qua công tơ
            if (!$endReading) {
                return null;
            }

            // Tìm bill_detail gần nhất đã thanh toán của meter này
            $lastBillDetail = BillDetail::where('electric_meter_id', $meter->id)
                ->whereHas('bill', function($q) use ($billingMonth) {
                    $q->where('billing_month', '<', $billingMonth->copy()->startOfMonth());
                })
                ->orderBy('id', 'desc')
                ->first();

            if ($lastBillDetail) {
                // Có lịch sử thanh toán -> Lấy reading từ bill trước
                $lastBill = $lastBillDetail->bill;
                $startReading = MeterReading::where('electric_meter_id', $meter->id)
                    ->where('reading_date', '<=', $lastBill->billing_month->endOfMonth())
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                if (!$startReading) {
                    throw new \Exception("Không tìm thấy chỉ số đầu kỳ cho công tơ {$meter->meter_number}");
                }
            } else {
                // Chưa có lịch sử -> Lấy reading đầu tiên trước endReading
                $startReading = MeterReading::where('electric_meter_id', $meter->id)
                    ->where('reading_date', '<', $endReading->reading_date)
                    ->orderBy('reading_date', 'desc')
                    ->first();

                if (!$startReading) {
                    // Không có chỉ số đầu kỳ -> Coi như bắt đầu từ 0
                    $startReading = (object) [
                        'reading_value' => 0,
                        'reading_date' => $endReading->reading_date
                    ];
                }
            }

            $rawConsumption = ($endReading->reading_value - $startReading->reading_value) * $meter->hsn;

            if ($rawConsumption < 0) {
                throw new \Exception("Tiêu thụ âm cho công tơ {$meter->meter_number}");
            }

            if ($rawConsumption == 0) {
                throw new \Exception("Tiêu thụ bằng 0 cho công tơ {$meter->meter_number}");
            }

            $subsidizedApplied = min($rawConsumption, $meter->subsidized_kwh ?? 0);
            $chargeableKwh = max(0, $rawConsumption - $subsidizedApplied);

            // Tìm biểu giá theo ngày cuối tháng để lấy biểu giá mới nhất có hiệu lực trong tháng
            $tariff = ElectricityTariff::getActiveTariff($meter->tariff_type_id, $billingMonth->copy()->endOfMonth());

            if (!$tariff) {
                throw new \Exception("Không tìm thấy biểu giá cho loại công tơ ID: {$meter->tariff_type_id}");
            }

            $amount = $chargeableKwh * $tariff->price_per_kwh;

            BillDetail::updateOrCreate(
                [
                    'bill_id' => $bill->id,
                    'electric_meter_id' => $meter->id,
                ],
                [
                    'consumption' => $rawConsumption,
                    'subsidized_applied' => $subsidizedApplied,
                    'chargeable_kwh' => $chargeableKwh,
                    'price_per_kwh' => $tariff->price_per_kwh,
                    'hsn' => $meter->hsn,
                    'amount' => $amount,
                ]
            );

            $bill->update(['total_amount' => $bill->billDetails()->sum('amount')]);

            return $bill;
        });
    }

    public function createBillForOrganizationUnit(int $organizationUnitId, Carbon $billingMonth, Carbon $dueDate): array
    {
        $orgUnit = \App\Models\OrganizationUnit::with('children')->findOrFail($organizationUnitId);
        
        $meters = collect();
        
        // Case 1: UNIT - get meters from all CONSUMER children
        if ($orgUnit->type === 'UNIT') {
            $consumerIds = $orgUnit->children->pluck('id')->toArray();
            $meters = ElectricMeter::whereIn('organization_unit_id', $consumerIds)
                ->where('status', 'ACTIVE')
                ->get();
        } 
        // Case 2: Independent CONSUMER (HĐ tự do) - get meters directly
        elseif ($orgUnit->type === 'CONSUMER' && $orgUnit->parent_id === null) {
            $meters = ElectricMeter::where('organization_unit_id', $orgUnit->id)
                ->where('status', 'ACTIVE')
                ->get();
        }

        if ($meters->isEmpty()) {
            $entityType = $orgUnit->type === 'UNIT' ? 'đơn vị' : 'hợp đồng';
            throw new \Exception("Không tìm thấy công tơ hoạt động cho {$entityType} '{$orgUnit->name}'");
        }

        $billsByOrgUnit = [];
        $detailsCreated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($meters as $meter) {
            try {
                $bill = $this->createBillForMeter($meter, $billingMonth, $dueDate);
                
                // Nếu return null (không có chỉ số trong tháng), bỏ qua
                if ($bill === null) {
                    $skipped++;
                    continue;
                }
                
                $detailsCreated++;
                
                // Track bills by organization_unit_id
                if (!isset($billsByOrgUnit[$bill->organization_unit_id])) {
                    $billsByOrgUnit[$bill->organization_unit_id] = $bill;
                }
            } catch (\Exception $e) {
                $errors[] = "Công tơ {$meter->meter_number} ({$meter->organizationUnit->name}): {$e->getMessage()}";
            }
        }

        return [
            'bills' => array_values($billsByOrgUnit),
            'details_created' => $detailsCreated,
            'skipped' => $skipped,
            'total_meters' => $meters->count(),
            'errors' => $errors,
        ];
    }

    // Two-level model, no recursive descent needed
    // private function getAllDescendantIds(...) removed

    public function createBillsForMeters(array $meterIds, Carbon $billingMonth, Carbon $dueDate): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'bills' => [],
            'errors' => [],
        ];

        $meters = ElectricMeter::whereIn('id', $meterIds)->where('status', 'ACTIVE')->get();
 
        foreach ($meters as $meter) {
            try {
                $bill = $this->createBillForMeter($meter, $billingMonth, $dueDate);
                
                // Nếu return null (không có chỉ số trong tháng), bỏ qua
                if ($bill === null) {
                    $results['skipped']++;
                    continue;
                }
                
                $results['success']++;
                
                if (!in_array($bill->id, array_column($results['bills'], 'id'))) {
                    $results['bills'][] = $bill;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'meter_number' => $meter->meter_number,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
