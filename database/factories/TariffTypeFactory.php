<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TariffType>
 */
class TariffTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            ['code' => 'RESIDENTIAL', 'name' => 'Dân cư', 'color' => 'success'],
            ['code' => 'COMMERCIAL', 'name' => 'Thương mại', 'color' => 'primary'],
            ['code' => 'INDUSTRIAL', 'name' => 'Công nghiệp', 'color' => 'warning'],
            ['code' => 'GOVERNMENT', 'name' => 'Cơ quan nhà nước', 'color' => 'info'],
        ];
        
        $type = fake()->randomElement($types);
        
        return [
            'code' => $type['code'],
            'name' => $type['name'],
            'description' => fake()->sentence(),
            'color' => $type['color'],
            'icon' => 'heroicon-o-bolt',
            'status' => fake()->randomElement(['ACTIVE', 'INACTIVE']),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}

