<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:300',
            'price'       => 'required|numeric|min:0|decimal:0,2',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required'      => 'O nome do produto é obrigatório.',
            'name.max'           => 'O nome não pode ter mais de 255 caracteres.',
            'description.max'    => 'A descrição deve ser mais curta (máximo 300 caracteres).',
            'price.required'     => 'Informe o preço do produto.',
            'price.numeric'      => 'O preço deve ser um valor numérico.',
            'price.decimal'      => 'O preço deve ter no máximo duas casas decimais.',
            'stock.required'     => 'A quantidade em estoque é obrigatória.',
            'stock.integer'      => 'O estoque deve ser um número inteiro.',
            'image.image'        => 'O arquivo enviado deve ser uma imagem.',
            'image.max'          => 'A imagem não pode ser maior que 1MB.',
            'image.mimes'        => 'Formatos aceitos: JPEG, PNG, JPG e WEBP.',
        ];
    }
}
