<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class RegisterRequest extends BaseFormRequest
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
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|min:5|max:100|unique:users,email',
            'password' => 'required|min:6|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.min' => 'O nome precisa ter no mínimo 3 caracteres',
            'name.max' => 'O nome pode ter no máximo 100 caracteres',

            'email.required' => 'O email é obrigatório',
            'email.unique' => 'O email já está em uso',
            'email.email' => 'O email deve ser válido',
            'email.min' => 'O email precisa ter no mínimo 5 caracteres',
            'email.max' => 'O email pode ter no máximo 100 caracteres',

            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha precisa ter no mínimo 6 caracteres',
            'password.max' => 'A senha pode ter no máximo 255 caracteres',
        ];
    }
}
