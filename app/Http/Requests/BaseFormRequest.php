<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{

    // apenas para insomnia
    protected function prepareForValidation()
    {
        $this->headers->set('Accept', 'application/json');
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(response()->json([
            'message' => 'Existem erros, por favor verifique os campos e tente novamente',
            'errors' => $errors
        ], 422));
    }
}
