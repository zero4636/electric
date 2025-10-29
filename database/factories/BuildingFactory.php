<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        $codes = ['D5', 'A17', 'B1', 'D3', 'C10', 'SVĐ', 'C8', 'B7', 'D9', 'T'];
        
        return [
            'code' => fake()->unique()->randomElement($codes),
            'name' => 'Nhà ' . fake()->randomElement($codes),
            'address' => fake()->address(),
            'total_floors' => fake()->numberBetween(3, 12),
            'status' => 'ACTIVE',
        ];
    }
}
