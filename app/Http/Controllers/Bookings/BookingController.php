<?php

namespace App\Http\Controllers\Bookings;

use App\Enum\BookingStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBookingRequest $request)
    {

        try {
            $request['user_id'] = $request->attributes->get('user_id');

            $data = $request->validated();

            $bkExist = Booking::query()
                ->where('barber_id', $data['barber_id'])
                ->where('booking_date', $data['booking_date'])
                ->where('booking_time', $data['booking_time'])
                ->whereNotIn('status', [BookingStatusEnum::CANCELED->value])
                ->exists();

            if ($bkExist) {
                return $this->error('Horário já está ocupado', Response::HTTP_CONFLICT);
            }

            $booking = Booking::create([
                'user_id' => $data['user_id'],
                'barber_id' => $data['barber_id'],
                'service_id' => $data['service_id'],
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'status' => BookingStatusEnum::PENDING->value,
            ]);

            return $this->success('Agendamento criado', Response::HTTP_CREATED, [
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $booking = Booking::active()
            ->where('id', $id)
            ->select('id', 'user_id', 'barber_id', 'service_id', 'booking_date', 'booking_time', 'status')
            ->first();

        if (!$booking) {
            return $this->error('Agendamento não encontrado', Response::HTTP_NOT_FOUND);
        }

        return $this->success('Agendamento achado', Response::HTTP_OK, [
            'booking' => $booking
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
