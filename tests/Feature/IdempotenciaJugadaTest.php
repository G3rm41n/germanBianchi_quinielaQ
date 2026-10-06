<?php

use App\Models\Jugada;
use App\Models\PrecioHistorico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    PrecioHistorico::create(['tipo_juego' => 'quini6', 'valor' => 1200, 'fecha_inicio' => today()]);
});

it('doble submit con mismo token no crea duplicado', function () {
    $token = (string) Str::uuid();

    // Primer request
    $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'cart_data' => json_encode([
            [
                'modalidad' => 'quini6',
                'numeros' => [1, 2, 3, 4, 5, 6],
                'costo' => 1200
            ]
        ]),
    ]);

    // Segundo request
    $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'cart_data' => json_encode([
            [
                'modalidad' => 'quini6',
                'numeros' => [1, 2, 3, 4, 5, 6],
                'costo' => 1200
            ]
        ]),
    ]);

    // Verificar que solo haya 1 jugada
    $this->assertDatabaseCount('jugadas', 1);
});

it('token diferente crea jugada nueva', function () {
    $token1 = (string) Str::uuid();
    $token2 = (string) Str::uuid();

    // Primer request
    $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token1,
        'cart_data' => json_encode([
            [
                'modalidad' => 'quini6',
                'numeros' => [1, 2, 3, 4, 5, 6],
                'costo' => 1200
            ]
        ]),
    ]);

    // Segundo request
    $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token2,
        'cart_data' => json_encode([
            [
                'modalidad' => 'quini6',
                'numeros' => [7, 8, 9, 10, 11, 12],
                'costo' => 1200
            ]
        ]),
    ]);

    // Verificar que haya 2 jugadas
    $this->assertDatabaseCount('jugadas', 2);
});
