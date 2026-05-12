<?php

declare(strict_types=1);

namespace App\Http\Requests\Bookings;

use App\Enum\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barber_id'    => [
                'required',
                Rule::exists('users', 'id')->where('role', UserRoleEnum::BARBER->value)->where('is_active', true),
            ],
            'service_id'   => [
                'required',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'barber_id.required' => 'Barbeiro é obrigatório',
            'barber_id.exists'   => 'Barbeiro inválido ou inativo',

            'service_id.required' => 'Serviço é obrigatório',
            'service_id.exists'   => 'Serviço inválido ou inativo',

            'booking_date.required'        => 'Data é obrigatória',
            'booking_date.after_or_equal'  => 'Data não pode ser no passado',

            'booking_time.required'    => 'Hora é obrigatória',
            'booking_time.date_format' => 'Hora deve estar no formato HH:mm',
        ];
    }
}
