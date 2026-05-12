<?php

declare(strict_types=1);

use App\Enum\BookingStatusEnum;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

// Helper local: gera token JWT e retorna header Authorization
function tokenFor(User $user): string
{
    return 'Bearer ' . JWTAuth::fromUser($user);
}

// ─── STORE ────────────────────────────────────────────────────────────────────

describe('POST /api/bookings', function () {

    it('cria um agendamento com sucesso', function () {
        $client  = User::factory()->create();
        $barber  = User::factory()->barber()->create();
        $service = Service::factory()->create();

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->postJson('/api/bookings', [
                'barber_id'    => $barber->id,
                'service_id'   => $service->id,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'booking_time' => '10:00',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.booking.status', BookingStatusEnum::PENDING->value);

        $this->assertDatabaseHas('bookings', [
            'barber_id'    => $barber->id,
            'service_id'   => $service->id,
            'booking_time' => '10:00',
            'status'       => BookingStatusEnum::PENDING->value,
        ]);
    });

    it('retorna 409 quando o horário já está ocupado', function () {
        $client  = User::factory()->create();
        $barber  = User::factory()->barber()->create();
        $service = Service::factory()->create();

        $date = now()->addDay()->format('Y-m-d');

        Booking::factory()->create([
            'barber_id'    => $barber->id,
            'service_id'   => $service->id,
            'booking_date' => $date,
            'booking_time' => '10:00',
            'status'       => BookingStatusEnum::PENDING->value,
        ]);

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->postJson('/api/bookings', [
                'barber_id'    => $barber->id,
                'service_id'   => $service->id,
                'booking_date' => $date,
                'booking_time' => '10:00',
            ]);

        $response->assertStatus(409)
            ->assertJsonPath('message', 'Horário já está ocupado');
    });

    it('permite agendar em horário de booking cancelado', function () {
        $client  = User::factory()->create();
        $barber  = User::factory()->barber()->create();
        $service = Service::factory()->create();

        $date = now()->addDay()->format('Y-m-d');

        Booking::factory()->canceled()->create([
            'barber_id'    => $barber->id,
            'service_id'   => $service->id,
            'booking_date' => $date,
            'booking_time' => '10:00',
        ]);

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->postJson('/api/bookings', [
                'barber_id'    => $barber->id,
                'service_id'   => $service->id,
                'booking_date' => $date,
                'booking_time' => '10:00',
            ]);

        $response->assertStatus(201);
    });

    it('rejeita barber_id que não é barbeiro', function () {
        $client      = User::factory()->create();
        $notABarber  = User::factory()->create(); // role = client
        $service     = Service::factory()->create();

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->postJson('/api/bookings', [
                'barber_id'    => $notABarber->id,
                'service_id'   => $service->id,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'booking_time' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.barber_id.0', 'Barbeiro inválido ou inativo');
    });

    it('rejeita barbeiro inativo', function () {
        $client  = User::factory()->create();
        $barber  = User::factory()->barber()->inactive()->create();
        $service = Service::factory()->create();

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->postJson('/api/bookings', [
                'barber_id'    => $barber->id,
                'service_id'   => $service->id,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'booking_time' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.barber_id.0', 'Barbeiro inválido ou inativo');
    });

    it('rejeita serviço inativo', function () {
        $client  = User::factory()->create();
        $barber  = User::factory()->barber()->create();
        $service = Service::factory()->inactive()->create();

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->postJson('/api/bookings', [
                'barber_id'    => $barber->id,
                'service_id'   => $service->id,
                'booking_date' => now()->addDay()->format('Y-m-d'),
                'booking_time' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.service_id.0', 'Serviço inválido ou inativo');
    });

    it('rejeita data no passado', function () {
        $client  = User::factory()->create();
        $barber  = User::factory()->barber()->create();
        $service = Service::factory()->create();

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->postJson('/api/bookings', [
                'barber_id'    => $barber->id,
                'service_id'   => $service->id,
                'booking_date' => now()->subDay()->format('Y-m-d'),
                'booking_time' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.booking_date.0', 'Data não pode ser no passado');
    });

    it('rejeita requisição sem autenticação', function () {
        $response = $this->postJson('/api/bookings', []);

        $response->assertStatus(401);
    });

});

// ─── SHOW ─────────────────────────────────────────────────────────────────────

describe('GET /api/bookings/{id}', function () {

    it('retorna dados do agendamento', function () {
        $booking = Booking::factory()->create();

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.booking.id', $booking->id);
    });

    it('retorna 404 para id inexistente', function () {
        $response = $this->getJson('/api/bookings/id-que-nao-existe');

        $response->assertStatus(404);
    });

    it('retorna 404 para agendamento cancelado', function () {
        $booking = Booking::factory()->canceled()->create();

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(404);
    });

});

// ─── CANCEL ───────────────────────────────────────────────────────────────────

describe('PATCH /api/bookings/{id}/cancel', function () {

    it('cliente cancela o próprio agendamento', function () {
        $client  = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $client->id]);

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.booking.status', BookingStatusEnum::CANCELED->value);

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => BookingStatusEnum::CANCELED->value,
        ]);
    });

    it('barbeiro cancela o agendamento da sua agenda', function () {
        $barber  = User::factory()->barber()->create();
        $booking = Booking::factory()->create(['barber_id' => $barber->id]);

        $response = $this->withHeader('Authorization', tokenFor($barber))
            ->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.booking.status', BookingStatusEnum::CANCELED->value);
    });

    it('não cancela agendamento de outro usuário', function () {
        $outro   = User::factory()->create();
        $booking = Booking::factory()->create();

        $response = $this->withHeader('Authorization', tokenFor($outro))
            ->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(404);
    });

    it('não cancela agendamento já cancelado', function () {
        $client  = User::factory()->create();
        $booking = Booking::factory()->canceled()->create(['user_id' => $client->id]);

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(422);
    });

    it('não cancela agendamento já concluído', function () {
        $client  = User::factory()->create();
        $booking = Booking::factory()->state(['status' => BookingStatusEnum::COMPLETED->value])
            ->create(['user_id' => $client->id]);

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(422);
    });

    it('retorna 401 sem autenticação', function () {
        $booking = Booking::factory()->create();

        $response = $this->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(401);
    });

});

// ─── MY BOOKINGS ──────────────────────────────────────────────────────────────

describe('GET /api/bookings/me/list', function () {

    it('retorna agendamentos do cliente com paginação', function () {
        $client  = User::factory()->create();
        $barber  = User::factory()->barber()->create();
        $service = Service::factory()->create();

        Booking::factory()->count(2)->create([
            'user_id'    => $client->id,
            'barber_id'  => $barber->id,
            'service_id' => $service->id,
        ]);

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->getJson('/api/bookings/me/list?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.bookings');
    });

    it('retorna agendamentos do barbeiro com paginação', function () {
        $barber  = User::factory()->barber()->create();
        $service = Service::factory()->create();

        Booking::factory()->count(3)->create([
            'barber_id'  => $barber->id,
            'service_id' => $service->id,
        ]);

        $response = $this->withHeader('Authorization', tokenFor($barber))
            ->getJson('/api/bookings/me/list');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.bookings');
    });

    it('não expõe role do usuário na resposta', function () {
        $client = User::factory()->create();

        $response = $this->withHeader('Authorization', tokenFor($client))
            ->getJson('/api/bookings/me/list');

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.user.role');
    });

    it('retorna 401 sem autenticação', function () {
        $response = $this->getJson('/api/bookings/me/list');

        $response->assertStatus(401);
    });

});
