<?php

use App\Models\Jugada;
use App\Models\PrecioHistorico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cliente = User::factory()->create();
    $this->admin = User::factory()->admin()->create();

    PrecioHistorico::create(['tipo_juego' => 'quini6', 'valor' => 1200, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'lotoplus', 'valor' => 1500, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'loto5', 'valor' => 800, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'poceada', 'valor' => 1000, 'fecha_inicio' => today()]);
});

it('redirecciona invitados', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('bloquea a clientes', function () {
    $this->actingAs($this->cliente)
        ->get(route('admin.dashboard'))
        ->assertStatus(403);
});

it('permite acceso a admins', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertStatus(200)
        ->assertSee('Panel de Control');
});

it('permite cambiar estado de jugada', function () {
    $jugada = Jugada::factory()->create(['estado' => 'pendiente']);

    $this->actingAs($this->admin)
        ->patch(route('admin.jugadas.estado', $jugada), [
            'estado' => 'procesado',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('jugadas', [
        'id' => $jugada->id,
        'estado' => 'procesado',
    ]);
});

it('permite cambiar precios y guarda historico', function () {
    $this->assertDatabaseCount('precios_historicos', 4); // Creados en beforeEach

    $this->actingAs($this->admin)
        ->put(route('admin.precios.update', 'quini6'), [
            'valor' => 1800,
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('precios_historicos', 5);

    // El vigente debe ser el nuevo
    $nuevo = PrecioHistorico::vigente()->where('tipo_juego', 'quini6')->first();
    expect((float) $nuevo->valor)->toBe(1800.00);
});

it('limpia base de datos', function () {
    Jugada::factory()->count(3)->create();
    $this->assertDatabaseCount('jugadas', 3);

    $this->actingAs($this->admin)
        ->delete(route('admin.limpiar'))
        ->assertRedirect();

    $this->assertDatabaseCount('jugadas', 0);
});

it('permite ver la lista de usuarios', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertStatus(200)
        ->assertSee('Gestión de Clientes');
});

it('permite ver las jugadas de un usuario especifico', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.show', $this->cliente))
        ->assertStatus(200)
        ->assertSee('Jugadas de: '.$this->cliente->name);
});
