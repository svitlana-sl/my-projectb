<?php

namespace Database\Factories;

use App\Models\SitterService;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SitterServiceFactory extends Factory
{
    protected $model = SitterService::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'hourly_rate' => $this->faker->randomFloat(2, 5, 50),
            // Вибираємо випадковий існуючий service_type_id
            'service_type_id' => ServiceType::inRandomOrder()->first()?->id ?? ServiceType::factory(),
            // Вибираємо випадкового користувача або створюємо нового
            'sitter_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
        ];
    }
}
