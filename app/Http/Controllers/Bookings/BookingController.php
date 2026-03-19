<?php

namespace App\Http\Controllers\Bookings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
            $data = $request->validated();

            $userId = $request->attributes->get('user_id');

            $exists = Booking::where('barber_id', $data['barber_id'])
                ->where('booking_date', $data['booking_date'])
                ->where('booking_time', $data['booking_time'])
                ->exists();

            if ($exists) {
                return $this->error('Horário já está ocupado', Response::HTTP_CONFLICT);
            }

            $booking = Booking::create([
                'user_id' => $userId,
                'barber_id' => $data['barber_id'],
                'service_id' => $data['service_id'],
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'status' => 'pending',
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
        //
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
