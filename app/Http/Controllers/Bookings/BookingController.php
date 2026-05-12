<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bookings;

use App\Enum\BookingStatusEnum;
use App\Exceptions\BookingConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = $request->attributes->get('user_id');

            $booking = $this->bookingService->create($data);

            return $this->success('Agendamento criado', Response::HTTP_CREATED, [
                'booking' => new BookingResource($booking),
            ]);
        } catch (BookingConflictException $e) {
            return $this->error($e->getMessage(), Response::HTTP_CONFLICT);
        }
    }

    public function show(string $id): JsonResponse
    {
        $booking = Booking::active()
            ->where('id', $id)
            ->select('id', 'user_id', 'barber_id', 'service_id', 'booking_date', 'booking_time', 'status')
            ->first();

        if (!$booking) {
            return $this->error('Agendamento não encontrado', Response::HTTP_NOT_FOUND);
        }

        return $this->success('Agendamento encontrado', Response::HTTP_OK, [
            'booking' => new BookingResource($booking),
        ]);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $userId = $request->attributes->get('user_id');

        $booking = Booking::where('id', $id)
            ->where(fn($q) => $q->where('user_id', $userId)->orWhere('barber_id', $userId))
            ->first();

        if (!$booking) {
            return $this->error('Agendamento não encontrado', Response::HTTP_NOT_FOUND);
        }

        $cancelable = [BookingStatusEnum::PENDING->value, BookingStatusEnum::CONFIRMED->value];

        if (!in_array($booking->status, $cancelable, strict: true)) {
            return $this->error('Agendamento não pode ser cancelado', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $booking->update(['status' => BookingStatusEnum::CANCELED->value]);

        return $this->success('Agendamento cancelado', Response::HTTP_OK, [
            'booking' => new BookingResource($booking),
        ]);
    }

    public function myBookings(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user_id');

        $user = User::where('id', $userId)
            ->where('is_active', true)
            ->first(['id', 'name', 'role']);

        if (!$user) {
            return $this->error('Usuário não encontrado', Response::HTTP_NOT_FOUND);
        }

        $perPage  = $request->integer('per_page', 15);
        $bookings = match ($user->role) {
            'barber' => $this->getBarberBookings($userId, $perPage),
            'client' => $this->getClientBookings($userId, $perPage),
            default  => collect(),
        };

        return $this->success('', Response::HTTP_OK, [
            'user'     => ['id' => $user->id, 'name' => $user->name],
            'bookings' => BookingResource::collection($bookings),
        ]);
    }

    private function getBarberBookings(string $userId, int $perPage)
    {
        return Booking::where('barber_id', $userId)
            ->with([
                'user:id,name,phone,email',
                'service:id,name,price,duration_minutes',
            ])
            ->orderByDesc('booking_date')
            ->orderByDesc('booking_time')
            ->paginate($perPage, ['id', 'user_id', 'service_id', 'booking_date', 'booking_time', 'status', 'created_at']);
    }

    private function getClientBookings(string $userId, int $perPage)
    {
        return Booking::where('user_id', $userId)
            ->with([
                'barber:id,name,score,specialties,about',
                'service:id,name,price,duration_minutes',
            ])
            ->orderByDesc('booking_date')
            ->orderByDesc('booking_time')
            ->paginate($perPage, ['id', 'barber_id', 'service_id', 'booking_date', 'booking_time', 'status', 'created_at']);
    }
}
