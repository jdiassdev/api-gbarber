<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\BookingStatusEnum;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'barber_id'    => User::factory()->barber(),
            'service_id'   => Service::factory(),
            'booking_date' => now()->addDays(fake()->numberBetween(1, 30))->format('Y-m-d'),
            'booking_time' => fake()->randomElement(['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']),
            'status'       => BookingStatusEnum::PENDING->value,
        ];
    }

    public function canceled(): static
    {
        return $this->state(['status' => BookingStatusEnum::CANCELED->value]);
    }

    public function confirmed(): static
    {
        return $this->state(['status' => BookingStatusEnum::CONFIRMED->value]);
    }

    public function completed(): static
    {
        return $this->state(['status' => BookingStatusEnum::COMPLETED->value]);
    }
}
