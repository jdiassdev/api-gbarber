<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
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
            'email' => 'required|max:100|email|min:5|exists:users,email',
            'password' =>  'required|max:255|min:6'
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => 'O email é obrigatorio',
            'email.max' => 'O email precisar ter no maximo 100 caracteres',
            'email.min' => 'O email precisar ter no minimo 5 caracteres',
            'email.email' => 'O email precisar valido, do tipo email',
            'email.exists' => 'O email não existe',

            'password.required' =>  'Senha é obrigatorio',
            'password.min' =>  'Senha precisar ter no minimo 6 caracteres',
        ];
    }
}
