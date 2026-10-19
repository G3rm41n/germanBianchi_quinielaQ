<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JugadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('cart_data')) {
            $this->merge([
                'cart' => json_decode($this->cart_data, true),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'idempotency_token' => ['required', 'uuid'],
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.modalidad' => ['required', 'in:quiniela,quini6,lotoplus,loto5,poceada'],

            // Quiniela
            'cart.*.numero' => ['required_if:cart.*.modalidad,quiniela', 'nullable', 'string', 'regex:/^\d{1,4}$/'],
            'cart.*.posiciones' => ['required_if:cart.*.modalidad,quiniela', 'nullable', 'array', 'min:1'],
            'cart.*.posiciones.*' => ['integer', 'between:1,20'],
            'cart.*.jurisdiccion' => ['required_if:cart.*.modalidad,quiniela', 'nullable', 'in:nacion,provincia,santa_fe,cordoba'],
            'cart.*.importe' => ['required_if:cart.*.modalidad,quiniela', 'nullable', 'numeric', 'min:1'],

            // Quini 6
            'cart.*.numeros' => ['required_unless:cart.*.modalidad,quiniela', 'nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'idempotency_token.required' => 'Ocurrió un error con el token de seguridad.',
            'cart.required' => 'Tu boleta está vacía. Agregá al menos una jugada.',
            'cart.min' => 'Tu boleta está vacía. Agregá al menos una jugada.',
        ];
    }
}
