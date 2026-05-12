<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\UserRoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'      => fake()->name(),
            'email'     => fake()->unique()->safeEmail(),
            'password'  => static::$password ??= Hash::make('password'),
            'phone'     => fake()->numerify('(##) #####-####'),
            'role'      => UserRoleEnum::CLIENT->value,
            'is_active' => true,
        ];
    }

    public function barber(): static
    {
        return $this->state([
            'role'        => UserRoleEnum::BARBER->value,
            'about'       => fake()->sentence(),
            'specialties' => 'Corte, Barba',
            'score'       => 4.5,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
