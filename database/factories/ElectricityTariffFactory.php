<?php

namespace Database\Factories;

use App\Models\TariffType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ElectricityTariff>
 */
class ElectricityTariffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $endDate = $this->faker->optional(0.3)->dateTimeBetween($startDate, '+1 year');
        
        return [
            'tariff_type_id' => TariffType::factory(),
            'tariff_type' => $this->faker->randomElement(['RESIDENTIAL', 'COMMERCIAL', 'INDUSTRIAL']), // Legacy field
            'price_per_kwh' => $this->faker->numberBetween(1500, 3000), // VND/kWh
            'effective_from' => $startDate,
            'effective_to' => $endDate,
        ];
    }
    
    /**
     * Current active tariff
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => Carbon::now()->subMonths(6),
            'effective_to' => null,
        ]);
    }
    
    /**
     * Historical (expired) tariff
     */
    public function historical(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => Carbon::now()->subYear(),
            'effective_to' => Carbon::now()->subMonths(6),
        ]);
    }
    
    /**
     * Future tariff
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => Carbon::now()->addMonths(1),
            'effective_to' => null,
        ]);
    }
}
