<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment_id' => (string) Str::uuid(),
            'article_id' => Article::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
            'parent_id' => null, // Default sebagai komentar utama (bukan balasan)
        ];
    }
}
