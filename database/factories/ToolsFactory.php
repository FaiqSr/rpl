<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ToolsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool_code' => (string) Str::uuid(), // Sesuai 'uuid('tool_code')'
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['Hand Tool', 'Power Tool', 'Measurement', 'Safety']),
            'description' => fake()->optional()->sentence(),
            'stock' => fake()->numberBetween(0, 50),
        ];
    }
}
