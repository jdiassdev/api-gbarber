<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'booking_date' => $this->booking_date,
            'booking_time' => $this->booking_time,
            'status'       => $this->status,
            'created_at'   => $this->created_at?->toDateTimeString(),

            'user'    => $this->whenLoaded('user', fn() => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),

            'barber'  => $this->whenLoaded('barber', fn() => [
                'id'          => $this->barber->id,
                'name'        => $this->barber->name,
                'about'       => $this->barber->about,
                'specialties' => $this->barber->specialties,
                'score'       => $this->barber->score,
            ]),

            'service' => $this->whenLoaded('service', fn() => [
                'id'               => $this->service->id,
                'name'             => $this->service->name,
                'price'            => $this->service->price,
                'duration_minutes' => $this->service->duration_minutes,
            ]),
        ];
    }
}
