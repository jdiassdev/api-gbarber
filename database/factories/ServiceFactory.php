<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name'             => fake()->randomElement(['Corte', 'Barba', 'Corte + Barba', 'Hidratação']),
            'duration_minutes' => fake()->randomElement([30, 45, 60]),
            'price'            => fake()->randomFloat(2, 20, 150),
            'is_active'        => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
