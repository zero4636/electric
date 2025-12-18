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
            ['code' => 'SINH_HOAT', 'name' => 'Sinh hoạt', 'color' => 'success'],
            ['code' => 'SAN_XUAT', 'name' => 'Sản xuất', 'color' => 'warning'],
            ['code' => 'KINH_DOANH', 'name' => 'Kinh doanh', 'color' => 'primary'],
            ['code' => 'HANH_CHINH_SU_NGHIEP', 'name' => 'Hành chính sự nghiệp', 'color' => 'info'],
            ['code' => 'CHIEU_SANG_CONG_CONG', 'name' => 'Chiếu sáng công cộng', 'color' => 'gray'],
        ];
        
        $type = fake()->randomElement($types);
        
        return [
            'code' => $type['code'],
            'name' => $type['name'],
            'description' => fake()->sentence(),
            'color' => $type['color'],
            'icon' => 'heroicon-o-bolt',
            'status' => 'ACTIVE',
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}

