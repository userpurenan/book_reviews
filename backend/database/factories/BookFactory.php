<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => "テスト",
            'url' => "test@test.com",
            'detail' => "テストです",
            'review' => "テスト",
            'reviewer' => "きよみや",
            'token' => "Bearer ".Str::random(220)
        ];
    }
}
