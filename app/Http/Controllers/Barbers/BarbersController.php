<?php

namespace App\Http\Controllers\Barbers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Barbers\CreateBarberRequest;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BarbersController extends Controller
{
    /**
     * Display a listing of the resource.
     * listar barbeiros
     */
    public function index()
    {
        $barbers = User::query()
            ->barber()
            ->active()
            ->select('id', 'name', 'email', 'phone')
            ->get();

        return $this->success('Barbeiros disponiveis', Response::HTTP_OK, [
            'barbers' => $barbers,
        ]);
    }

    /**
     * verifica disponibilidade
     */
    public function availability(Request $request, string $id)
    {
        $monthYear = $request->query('month');

        $timezone = 'America/Sao_Paulo';

        $baseDate = $monthYear ? Carbon::createFromFormat('Y-m', $monthYear, $timezone) : Carbon::now($timezone);

        $today = Carbon::now($timezone);

        if (
            $baseDate->year === $today->year &&
            $baseDate->month === $today->month
        ) {
            $start = $today->copy()->startOfDay();
        } else {
            $start = $baseDate->copy()->startOfMonth()->startOfDay();
        }

        $end = $baseDate->copy()->endOfMonth()->endOfDay();

        $bookings = Booking::query()
            ->where('barber_id', $id)
            ->whereBetween('booking_date', [$start, $end])
            ->select('booking_date', DB::raw('GROUP_CONCAT(booking_time) as hours'))
            ->groupBy('booking_date')
            ->orderBy('booking_date')
            ->get();

        $booked = $bookings->map(fn($b) => [
            'booking_date' => $b->booking_date,
            'booking_time' => explode(',', $b->hours),
        ]);

        return $this->success('Disponobilidade', Response::HTTP_OK, [
            'availability' => $booked,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * armazena um novo barbeiro
     */
    public function store(CreateBarberRequest $request)
    {
        // $userId = $request->attributes->get('user_id'); // futuramente dar auditoria de quem criou o barbeiro

        $data = $request->validated();

        $user = User::query()
            ->where('email', $data['email'])
            ->select('id', 'name', 'email', 'phone', 'about', 'specialties', 'role')
            ->first();

        if (! $user) {
            return $this->error('Usuário não encontrado', Response::HTTP_NOT_FOUND);
        }

        if ($user->role === 'barber') {
            return $this->error('Usuário já é um barbeiro', Response::HTTP_BAD_REQUEST);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'cpf' => $data['cpf'],
                'phone' => $data['phone'],
                'about' => $data['about'],
                'specialties' => $data['specialties'],
                'role' => 'barber',
            ]);
        });

        return $this->success('Barbeiro criado', Response::HTTP_CREATED, [
            'barber' => $user->only('id', 'name', 'email', 'phone', 'about', 'specialties'),
        ]);
    }

    /**
     * Display the specified resource.
     * ver um unico barbeiro
     */
    public function show(string $id)
    {
        $barber = User::query()
            ->active()
            ->barber()
            ->where('id', $id)
            ->select('id', 'name', 'email', 'phone', 'about', 'specialties', 'score')
            ->first();

        if (! $barber) {
            return $this->error('Barbeiro não encontrado', Response::HTTP_NOT_FOUND);
        }

        return $this->success("Barbeiro {$barber->name}", Response::HTTP_OK, [
            'barber' => $barber,
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
