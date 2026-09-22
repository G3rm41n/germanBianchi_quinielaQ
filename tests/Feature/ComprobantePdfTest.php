<?php

use App\Models\Jugada;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['rol' => 'cliente']);
    $this->admin = User::factory()->create(['rol' => 'admin']);
    $this->otherUser = User::factory()->create(['rol' => 'cliente']);

    $this->jugada = Jugada::create([
        'user_id' => $this->user->id,
        'modalidad' => 'quiniela',
        'monto_total' => 1000,
        'precio_unitario_snapshot' => 1000,
        'estado' => 'pendiente',
        'idempotency_token' => 'test-uuid-123',
    ]);
    
    // El PDF requiere el detalle para renderizar
    $this->jugada->detalleQuiniela()->create([
        'numero' => '1234',
        'posicion' => 10,
        'jurisdiccion' => 'nacion',
        'importe' => 1000,
    ]);
});

it('invitado no puede descargar pdf', function () {
    $this->get("/jugadas/{$this->jugada->id}/pdf")
         ->assertRedirect(route('login'));
});

it('cliente puede descargar su propia jugada en pdf', function () {
    $this->actingAs($this->user)
         ->get("/jugadas/{$this->jugada->id}/pdf")
         ->assertStatus(200)
         ->assertHeader('Content-Type', 'application/pdf');
});

it('cliente no puede descargar jugada de otro', function () {
    $this->actingAs($this->otherUser)
         ->get("/jugadas/{$this->jugada->id}/pdf")
         ->assertStatus(403);
});

it('admin puede descargar cualquier jugada en pdf', function () {
    $this->actingAs($this->admin)
         ->get("/jugadas/{$this->jugada->id}/pdf")
         ->assertStatus(200)
         ->assertHeader('Content-Type', 'application/pdf');
});
