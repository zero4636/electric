<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\ElectricMeter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillDetail>
 */
class BillDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $meter = ElectricMeter::inRandomOrder()->first() ?? ElectricMeter::factory();
        $consumption = $this->faker->randomFloat(2, 50, 500);
        $pricePerKwh = $this->faker->randomFloat(2, 2000, 5000);
        $hsn = $meter->hsn ?? 1;

        return [
            'bill_id' => Bill::inRandomOrder()->first()->id ?? Bill::factory(),
            'electric_meter_id' => $meter->id,
            'consumption' => $consumption,
            'price_per_kwh' => $pricePerKwh,
            'hsn' => $hsn,
            'amount' => $consumption * $pricePerKwh * $hsn,
        ];
    }
}
