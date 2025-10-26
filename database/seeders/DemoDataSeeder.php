<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\ElectricMeter;
use App\Models\ElectricityTariff;
use App\Models\MeterReading;
use App\Models\OrganizationUnit;
use App\Models\Substation;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Safety: don't run demo seeder in production
        if (app()->environment('production')) {
            $this->command?->info('Skipping DemoDataSeeder in production.');
            return;
        }

        $faker = Faker::create();

        // Create tariffs
        $tariffs = [
            ['tariff_type' => 'RESIDENTIAL', 'price_per_kwh' => 0.12],
            ['tariff_type' => 'COMMERCIAL', 'price_per_kwh' => 0.15],
            ['tariff_type' => 'INDUSTRIAL', 'price_per_kwh' => 0.10],
        ];

        foreach ($tariffs as $t) {
            ElectricityTariff::create(array_merge($t, [
                'effective_from' => Carbon::now()->subYear()->toDateString(),
                'effective_to' => null,
            ]));
        }

        // Create substations
        $subs = [];
        for ($i = 1; $i <= 3; $i++) {
            $subs[] = Substation::create([
                'name' => "Substation $i",
                'code' => "SS-{$i}",
                'location' => $faker->city(),
                'status' => 'ACTIVE',
            ]);
        }

        // Create organization units with hierarchical structure
        // Root organizations
        $orgs = [];
        for ($r = 1; $r <= 2; $r++) {
            $root = OrganizationUnit::create([
                'name' => "Tập đoàn $r",
                'code' => "GRP-{$r}",
                'type' => 'ORGANIZATION',
                'email' => $faker->companyEmail(),
                'contact_name' => $faker->name(),
                'contact_phone' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'status' => 'ACTIVE',
            ]);

            // Child units
            for ($c = 1; $c <= rand(2, 4); $c++) {
                $child = OrganizationUnit::create([
                    'parent_id' => $root->id,
                    'name' => "Đơn vị {$r}.{$c}",
                    'code' => "UNIT-{$r}{$c}",
                    'type' => 'UNIT',
                    'email' => $faker->companyEmail(),
                    'contact_name' => $faker->name(),
                    'contact_phone' => $faker->phoneNumber(),
                    'address' => $faker->address(),
                    'status' => (rand(1,10) > 2) ? 'ACTIVE' : 'INACTIVE',
                ]);

                // Some children have grandchildren (deeper hierarchy)
                if (rand(1, 10) > 6) {
                    for ($g = 1; $g <= rand(1, 3); $g++) {
                        OrganizationUnit::create([
                            'parent_id' => $child->id,
                            'name' => "Đơn vị {$r}.{$c}.{$g}",
                            'code' => "UNIT-{$r}{$c}{$g}",
                            'type' => 'UNIT',
                            'email' => $faker->companyEmail(),
                            'contact_name' => $faker->name(),
                            'contact_phone' => $faker->phoneNumber(),
                            'address' => $faker->address(),
                            'status' => 'ACTIVE',
                        ]);
                    }
                }
            }

            $orgs[] = $root;
        }

        // Also add some standalone consumers (customers) with no meters
        for ($k = 1; $k <= 3; $k++) {
            OrganizationUnit::create([
                'name' => "Khách hàng $k",
                'code' => "CUST-{$k}",
                'type' => 'CONSUMER',
                'email' => $faker->companyEmail(),
                'contact_name' => $faker->name(),
                'contact_phone' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'status' => (rand(1,10) > 3) ? 'ACTIVE' : 'INACTIVE',
            ]);
        }

        // Create meters and readings for many units, but skip some to create variation
        $allOrgs = OrganizationUnit::all();
        foreach ($allOrgs as $org) {
            // Skip some consumers so they have no meters
            if ($org->type === 'CONSUMER' && rand(1, 10) > 6) {
                continue;
            }

            $metersCount = ($org->type === 'ORGANIZATION') ? rand(3, 6) : rand(0, 3);
            for ($m = 0; $m < $metersCount; $m++) {
                $meterType = ['RESIDENTIAL','COMMERCIAL','INDUSTRIAL'][array_rand([0,1,2])];
                $hsn = $faker->randomFloat(2, 1.0, 3.0);

                $meter = ElectricMeter::create([
                    'meter_number' => strtoupper(uniqid('M')),
                    'organization_unit_id' => $org->id,
                    'substation_id' => $subs[array_rand($subs)]->id,
                    'meter_type' => $meterType,
                    'hsn' => $hsn,
                    'status' => (rand(1,10) > 8) ? 'INACTIVE' : 'ACTIVE',
                ]);

                // create monthly readings for last 6 months (simulate increasing consumption)
                $base = $faker->numberBetween(500, 3000);
                for ($month = 6; $month >= 1; $month--) {
                    $date = Carbon::now()->subMonths($month);
                    $value = $base + ($faker->numberBetween(50, 900) * (6 - $month + 1));

                    MeterReading::create([
                        'electric_meter_id' => $meter->id,
                        'reading_date' => $date->toDateString(),
                        'reading_value' => $value,
                        'hsn' => $hsn,
                    ]);
                }
            }
        }

        // Create bills per organization for recent months and mix statuses
        $billingMonths = [Carbon::now()->startOfMonth(), Carbon::now()->subMonth()->startOfMonth()];
        foreach ($allOrgs as $org) {
            // Skip organizations with no meters
            if ($org->electricMeters()->count() === 0) {
                continue;
            }

            foreach ($billingMonths as $i => $billDate) {
                $status = ['PENDING', 'PAID', 'CANCELLED'][array_rand([0,1,2])];
                // bias: older month more likely to be PAID
                if ($i === 1) {
                    $status = (rand(1,10) > 3) ? 'PAID' : $status;
                }

                $bill = Bill::create([
                    'organization_unit_id' => $org->id,
                    'billing_date' => $billDate->toDateString(),
                    'total_amount' => 0,
                    'status' => $status,
                ]);

                $total = 0;
                foreach ($org->electricMeters as $meter) {
                    $readings = $meter->meterReadings()->orderBy('reading_date','desc')->take(2)->get();
                    if ($readings->count() < 2) {
                        continue;
                    }
                    $latest = $readings[0];
                    $prev = $readings[1];
                    $consumption = max(0, $latest->reading_value - $prev->reading_value);

                    $tariff = ElectricityTariff::where('tariff_type', $meter->meter_type)->latest('effective_from')->first();
                    $price = $tariff ? $tariff->price_per_kwh : 0.10;

                    $amount = $consumption * $price * $meter->hsn;

                    BillDetail::create([
                        'bill_id' => $bill->id,
                        'electric_meter_id' => $meter->id,
                        'consumption' => $consumption,
                        'price_per_kwh' => $price,
                        'hsn' => $meter->hsn,
                        'amount' => $amount,
                    ]);

                    $total += $amount;
                }

                $bill->update(['total_amount' => $total]);
            }
        }
    }
}