<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\BookingStatusEnum;
use App\Exceptions\BookingConflictException;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $conflict = Booking::query()
                ->where('barber_id', $data['barber_id'])
                ->where('booking_date', $data['booking_date'])
                ->where('booking_time', $data['booking_time'])
                ->whereNotIn('status', [BookingStatusEnum::CANCELED->value])
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw new BookingConflictException();
            }

            return Booking::create([
                'user_id'      => $data['user_id'],
                'barber_id'    => $data['barber_id'],
                'service_id'   => $data['service_id'],
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'status'       => BookingStatusEnum::PENDING->value,
            ]);
        });
    }
}
