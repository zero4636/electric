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
    /**
     * Generate bill for organization unit for a specific period
     *
     * @param int $organizationUnitId
     * @param Carbon $billingDate
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @return Bill
     */
    public function generateBill(
        int $organizationUnitId,
        Carbon $billingDate,
        Carbon $fromDate,
        Carbon $toDate
    ): Bill {
        return DB::transaction(function () use ($organizationUnitId, $billingDate, $fromDate, $toDate) {
            // Create bill
            $bill = Bill::create([
                'organization_unit_id' => $organizationUnitId,
                'billing_date' => $billingDate,
                'total_amount' => 0,
                'status' => 'PENDING',
            ]);

            // Get all electric meters for this organization
            $meters = ElectricMeter::where('organization_unit_id', $organizationUnitId)
                ->where('status', 'ACTIVE')
                ->get();

            foreach ($meters as $meter) {
                $this->createBillDetailForMeter($bill, $meter, $fromDate, $toDate);
            }

            // Update total
            $bill->updateTotal();

            return $bill;
        });
    }

    /**
     * Create bill detail for a specific meter
     *
     * @param Bill $bill
     * @param ElectricMeter $meter
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @return BillDetail|null
     */
    protected function createBillDetailForMeter(
        Bill $bill,
        ElectricMeter $meter,
        Carbon $fromDate,
        Carbon $toDate
    ): ?BillDetail {
        // Get readings in period
        $startReading = MeterReading::where('electric_meter_id', $meter->id)
            ->where('reading_date', '<=', $fromDate)
            ->orderBy('reading_date', 'desc')
            ->first();

        $endReading = MeterReading::where('electric_meter_id', $meter->id)
            ->where('reading_date', '<=', $toDate)
            ->orderBy('reading_date', 'desc')
            ->first();

        if (!$startReading || !$endReading || $startReading->id === $endReading->id) {
            return null;
        }

        // Calculate consumption
        $consumption = ($endReading->reading_value - $startReading->reading_value) * $meter->hsn;

        if ($consumption <= 0) {
            return null;
        }

        // Get appropriate tariff
        $tariff = ElectricityTariff::getActiveTariff($meter->meter_type, $toDate);

        if (!$tariff) {
            throw new \Exception("No active tariff found for meter type: {$meter->meter_type}");
        }

        // Create bill detail
        return BillDetail::create([
            'bill_id' => $bill->id,
            'electric_meter_id' => $meter->id,
            'consumption' => $consumption,
            'price_per_kwh' => $tariff->price_per_kwh,
            'hsn' => $meter->hsn,
            'amount' => $consumption * $tariff->price_per_kwh,
        ]);
    }

    /**
     * Generate bills for all organization units for a month
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    public function generateMonthlyBills(int $year, int $month): array
    {
        $billingDate = Carbon::create($year, $month, 1)->endOfMonth();
        $fromDate = Carbon::create($year, $month, 1)->subMonth();
        $toDate = Carbon::create($year, $month, 1)->endOfMonth();

        $organizationUnits = \App\Models\OrganizationUnit::where('status', 'ACTIVE')
            ->where('type', 'CONSUMER')
            ->get();

        $results = [];

        foreach ($organizationUnits as $unit) {
            try {
                $bill = $this->generateBill($unit->id, $billingDate, $fromDate, $toDate);
                $results[] = [
                    'success' => true,
                    'organization_unit_id' => $unit->id,
                    'bill_id' => $bill->id,
                    'amount' => $bill->total_amount,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'organization_unit_id' => $unit->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Mark bill as paid
     *
     * @param int $billId
     * @return Bill
     */
    public function markAsPaid(int $billId): Bill
    {
        $bill = Bill::findOrFail($billId);
        $bill->update(['status' => 'PAID']);
        return $bill;
    }

    /**
     * Cancel bill
     *
     * @param int $billId
     * @return Bill
     */
    public function cancelBill(int $billId): Bill
    {
        $bill = Bill::findOrFail($billId);
        $bill->update(['status' => 'CANCELLED']);
        return $bill;
    }
}
