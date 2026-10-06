<?php

use App\Mail\NuevaJugadaMail;
use App\Models\Jugada;
use App\Models\User;
use App\Models\PrecioHistorico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->admin()->create([
        'email' => 'admin@agencia5801.local',
    ]);
    
    PrecioHistorico::create(['tipo_juego' => 'quini6', 'valor' => 1200, 'fecha_inicio' => today()]);
});

it('envio exitoso despacha el mail y mantiene estado pendiente', function () {
    Mail::fake();

    $token = (string) Str::uuid();

    $response = $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'cart_data' => json_encode([
            [
                'modalidad' => 'quini6',
                'numeros' => [1, 2, 3, 4, 5, 6],
                'costo' => 1200
            ]
        ])
    ]);

    $response->assertRedirect(route('jugadas.index'));

    Mail::assertSent(NuevaJugadaMail::class);

    $this->assertDatabaseHas('jugadas', [
        'idempotency_token' => $token,
        'estado' => 'pendiente',
    ]);
});

it('falla smtp cambia estado a error_envio y guarda audit_log', function () {
    // Forzamos un fallo en Mail mockeando el facade para arrojar excepcion
    Mail::shouldReceive('to')->andThrow(new \Exception('Connection refused by SMTP'));

    $token = (string) Str::uuid();

    $response = $this->actingAs($this->user)->post(route('jugadas.store'), [
        'idempotency_token' => $token,
        'cart_data' => json_encode([
            [
                'modalidad' => 'quini6',
                'numeros' => [1, 2, 3, 4, 5, 6],
                'costo' => 1200
            ]
        ])
    ]);

    $response->assertRedirect(route('jugadas.index'));

    $this->assertDatabaseHas('jugadas', [
        'idempotency_token' => $token,
        'estado' => 'error_envio',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'accion' => 'smtp_failure',
    ]);
});
