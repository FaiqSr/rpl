<?php

namespace Database\Factories;

use App\Models\Tools;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ToolsDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool_detail_id' => (string) Str::uuid(),
            'tool_code' => Tools::factory(),
            'serial_number' => fake()->unique()->bothify('SN-#########'),
            'condition' => fake()->randomElement(['good', 'fair', 'damaged', 'maintenance']),
            'purchase_date' => fake()->optional()->date(),
            'price' => fake()->optional()->randomFloat(2, 50000, 1500000),
            'location' => fake()->randomElement(['Warehouse A', 'Shelf 1-B', 'Tool Crib']),
            'status' => fake()->boolean(20), // 80% false (available), 20% true (in use/unavailable)
        ];
    }
}
