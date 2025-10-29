<?php

namespace Database\Factories;

use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_unit_id' => OrganizationUnit::inRandomOrder()->first()->id ?? OrganizationUnit::factory(),
            'billing_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'total_amount' => $this->faker->randomFloat(2, 100, 5000),
            'status' => $this->faker->randomElement(['PENDING', 'PAID', 'CANCELLED']),
        ];
    }
}
