<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JugadaRequest extends FormRequest
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
            'idempotency_token' => ['required', 'uuid'],
            'modalidad' => ['required', 'in:quiniela,quini6,lotoplus,loto5,poceada'],

            // Quiniela
            'numero' => ['required_if:modalidad,quiniela', 'nullable', 'string', 'regex:/^\d{1,4}$/'],
            'posicion' => ['required_if:modalidad,quiniela', 'nullable', 'integer', 'between:1,20'],
            'jurisdiccion' => ['required_if:modalidad,quiniela', 'nullable', 'in:nacion,provincia,santa_fe,cordoba'],
            'importe' => ['required_if:modalidad,quiniela', 'nullable', 'numeric', 'min:1'],

            // Quini 6
            'numeros_quini6' => ['required_if:modalidad,quini6', 'nullable', 'array', 'size:6'],
            'numeros_quini6.*' => ['integer', 'between:0,45', 'distinct'],

            // Loto Plus
            'numeros_lotoplus' => ['required_if:modalidad,lotoplus', 'nullable', 'array', 'size:6'],
            'numeros_lotoplus.*' => ['integer', 'between:0,45', 'distinct'],
            'numero_plus' => ['required_if:modalidad,lotoplus', 'nullable', 'integer', 'between:0,9'],

            // Loto 5
            'numeros_loto5' => ['required_if:modalidad,loto5', 'nullable', 'array', 'size:5'],
            'numeros_loto5.*' => ['integer', 'between:0,36', 'distinct'],

            // Poceada
            'numeros_poceada' => ['required_if:modalidad,poceada', 'nullable', 'array', 'size:8'],
            'numeros_poceada.*' => ['integer', 'between:0,99', 'distinct'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'idempotency_token.required' => 'Ocurrió un error con el token de seguridad. Por favor, recarga la página.',
            'modalidad.required' => 'Debes seleccionar un juego.',
            'modalidad.in' => 'El juego seleccionado no es válido.',

            'numero.required_if' => 'El número es obligatorio para la Quiniela.',
            'numero.regex' => 'El número de la Quiniela debe tener entre 1 y 4 cifras.',
            'posicion.required_if' => 'La posición es obligatoria para la Quiniela.',
            'posicion.between' => 'La posición debe estar entre 1 y 20.',
            'jurisdiccion.required_if' => 'La jurisdicción es obligatoria para la Quiniela.',
            'jurisdiccion.in' => 'La jurisdicción seleccionada no es válida.',
            'importe.required_if' => 'El importe es obligatorio para la Quiniela.',
            'importe.min' => 'El importe debe ser mayor a 0.',

            'numeros_quini6.required_if' => 'Debes seleccionar exactamente 6 números para el Quini 6.',
            'numeros_quini6.size' => 'Debes seleccionar exactamente 6 números para el Quini 6.',
            'numeros_quini6.*.between' => 'Los números del Quini 6 deben estar entre 00 y 45.',
            'numeros_quini6.*.distinct' => 'No puedes repetir números en el Quini 6.',

            'numeros_lotoplus.required_if' => 'Debes seleccionar exactamente 6 números para el Loto Plus.',
            'numeros_lotoplus.size' => 'Debes seleccionar exactamente 6 números para el Loto Plus.',
            'numeros_lotoplus.*.between' => 'Los números del Loto Plus deben estar entre 00 y 45.',
            'numeros_lotoplus.*.distinct' => 'No puedes repetir números en el Loto Plus.',
            'numero_plus.required_if' => 'El Número Plus es obligatorio para el Loto Plus.',
            'numero_plus.between' => 'El Número Plus debe estar entre 0 y 9.',

            'numeros_loto5.required_if' => 'Debes seleccionar exactamente 5 números para el Loto 5.',
            'numeros_loto5.size' => 'Debes seleccionar exactamente 5 números para el Loto 5.',
            'numeros_loto5.*.between' => 'Los números del Loto 5 deben estar entre 00 y 36.',
            'numeros_loto5.*.distinct' => 'No puedes repetir números en el Loto 5.',

            'numeros_poceada.required_if' => 'Debes seleccionar exactamente 8 números para la Quiniela Poceada.',
            'numeros_poceada.size' => 'Debes seleccionar exactamente 8 números para la Quiniela Poceada.',
            'numeros_poceada.*.between' => 'Los números de la Quiniela Poceada deben estar entre 00 y 99.',
            'numeros_poceada.*.distinct' => 'No puedes repetir números en la Quiniela Poceada.',
        ];
    }
}
