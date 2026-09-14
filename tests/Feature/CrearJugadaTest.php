<?php

use App\Models\PrecioHistorico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    // Crear precios para las modalidades
    PrecioHistorico::create(['tipo_juego' => 'quini6', 'valor' => 1200, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'lotoplus', 'valor' => 1500, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'loto5', 'valor' => 800, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'poceada', 'valor' => 1000, 'fecha_inicio' => today()]);
});

it('invitado no puede crear jugada', function () {
    $this->get(route('jugadas.create'))->assertRedirect(route('login'));
    $this->post(route('jugadas.store'))->assertRedirect(route('login'));
});

it('puede ver formulario de nueva jugada', function () {
    $this->actingAs($this->user)
         ->get(route('jugadas.create'))
         ->assertStatus(200)
         ->assertSee('Quiniela Tradicional')
         ->assertSee('Quini 6');
});

it('puede ver historial de sus jugadas', function () {
    $this->actingAs($this->user)
         ->get(route('jugadas.index'))
         ->assertStatus(200)
         ->assertSee('Aún no hay jugadas');
});

it('puede crear jugada quiniela valida', function () {
    $token = (string) Str::uuid();

    $response = $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'modalidad' => 'quiniela',
        'numero' => '1234',
        'posicion' => 10,
        'jurisdiccion' => 'nacion',
        'importe' => 500,
    ]);

    $response->assertRedirect(route('jugadas.index'));

    $this->assertDatabaseHas('jugadas', [
        'user_id' => $this->user->id,
        'modalidad' => 'quiniela',
        'monto_total' => 500,
        'estado' => 'pendiente',
    ]);

    $this->assertDatabaseHas('detalles_quiniela', [
        'numero' => '1234',
        'posicion' => 10,
        'jurisdiccion' => 'nacion',
        'importe' => 500,
    ]);
});

it('puede crear jugada quini6 valida', function () {
    $token = (string) Str::uuid();

    $response = $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'modalidad' => 'quini6',
        'numeros_quini6' => [1, 2, 3, 4, 5, 6],
    ]);

    $response->assertRedirect(route('jugadas.index'));

    $this->assertDatabaseHas('jugadas', [
        'user_id' => $this->user->id,
        'modalidad' => 'quini6',
        'monto_total' => 1200, // el valor que le dimos al precio historico
    ]);

    $this->assertDatabaseHas('detalles_quini6', [
        'numeros' => json_encode([1, 2, 3, 4, 5, 6]),
    ]);
});

it('puede crear jugada lotoplus valida', function () {
    $token = (string) Str::uuid();

    $response = $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'modalidad' => 'lotoplus',
        'numeros_lotoplus' => [1, 2, 3, 4, 5, 6],
        'numero_plus' => 5,
    ]);

    $response->assertRedirect(route('jugadas.index'));

    $this->assertDatabaseHas('jugadas', [
        'user_id' => $this->user->id,
        'modalidad' => 'lotoplus',
        'monto_total' => 1500,
    ]);
});

it('falla si quini6 tiene menos de 6 numeros', function () {
    $token = (string) Str::uuid();

    $response = $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'modalidad' => 'quini6',
        'numeros_quini6' => [1, 2, 3, 4, 5],
    ]);

    $response->assertSessionHasErrors('numeros_quini6');
});

it('falla si quini6 tiene numeros repetidos', function () {
    $token = (string) Str::uuid();

    $response = $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'modalidad' => 'quini6',
        'numeros_quini6' => [1, 1, 2, 3, 4, 5],
    ]);

    $response->assertSessionHasErrors('numeros_quini6.0');
});
