<?php

declare(strict_types=1);

namespace App\Http\Controllers\Barbers;

use App\Enum\BookingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Barbers\CreateBarberRequest;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BarbersController extends Controller
{
    public function index(): JsonResponse
    {
        $barbers = User::query()
            ->barber()
            ->active()
            ->select('id', 'name', 'email', 'phone')
            ->get();

        return $this->success('Barbeiros disponíveis', Response::HTTP_OK, [
            'barbers' => $barbers,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $barber = User::query()
            ->active()
            ->barber()
            ->where('id', $id)
            ->select('id', 'name', 'email', 'phone', 'about', 'specialties', 'score')
            ->first();

        if (!$barber) {
            return $this->error('Barbeiro não encontrado', Response::HTTP_NOT_FOUND);
        }

        return $this->success("Barbeiro {$barber->name}", Response::HTTP_OK, [
            'barber' => $barber,
        ]);
    }

    public function store(CreateBarberRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()
            ->where('email', $data['email'])
            ->select('id', 'name', 'email', 'phone', 'about', 'specialties', 'role')
            ->first();

        if (!$user) {
            return $this->error('Usuário não encontrado', Response::HTTP_NOT_FOUND);
        }

        if ($user->role === UserRoleEnum::BARBER->value) {
            return $this->error('Usuário já é um barbeiro', Response::HTTP_BAD_REQUEST);
        }

        DB::transaction(function () use ($user, $data): void {
            $user->update([
                'cpf'         => $data['cpf'],
                'phone'       => $data['phone'],
                'about'       => $data['about'],
                'specialties' => $data['specialties'],
                'role'        => UserRoleEnum::BARBER->value,
            ]);
        });

        return $this->success('Barbeiro criado', Response::HTTP_CREATED, [
            'barber' => $user->only('id', 'name', 'email', 'phone', 'about', 'specialties'),
        ]);
    }

    public function availability(Request $request, string $id): JsonResponse
    {
        $monthYear = $request->query('month');
        $timezone  = 'America/Sao_Paulo';

        $baseDate = $monthYear
            ? Carbon::createFromFormat('Y-m', $monthYear, $timezone)
            : Carbon::now($timezone);

        $today = Carbon::now($timezone);

        $start = ($baseDate->year === $today->year && $baseDate->month === $today->month)
            ? $today->copy()->startOfDay()
            : $baseDate->copy()->startOfMonth()->startOfDay();

        $end = $baseDate->copy()->endOfMonth()->endOfDay();

        $bookings = Booking::query()
            ->where('barber_id', $id)
            ->whereBetween('booking_date', [$start, $end])
            ->whereNotIn('status', [BookingStatusEnum::CANCELED->value])
            ->select('booking_date', DB::raw('GROUP_CONCAT(booking_time ORDER BY booking_time) as hours'))
            ->groupBy('booking_date')
            ->orderBy('booking_date')
            ->get();

        $availability = $bookings->map(fn(Booking $b): array => [
            'booking_date' => $b->booking_date,
            'booking_time' => explode(',', $b->hours),
        ]);

        return $this->success('Disponibilidade', Response::HTTP_OK, [
            'availability' => $availability,
        ]);
    }
}
