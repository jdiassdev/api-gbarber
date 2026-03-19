<?php

namespace App\Http\Requests\Bookings;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',

            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Usuário é obrigatório',
            'user_id.exists' => 'Usuário inválido',

            'barber_id.required' => 'Barbeiro é obrigatório',
            'barber_id.exists' => 'Barbeiro inválido',

            'service_id.required' => 'Serviço é obrigatório',
            'service_id.exists' => 'Serviço inválido',

            'booking_date.required' => 'Data é obrigatória',
            'booking_date.after_or_equal' => 'Data não pode ser no passado',

            'booking_time.required' => 'Hora é obrigatória',
            'booking_time.date_format' => 'Hora deve estar no formato HH:mm',
        ];
    }
}
