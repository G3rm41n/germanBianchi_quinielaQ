<?php

use App\Models\DetalleQuini6;
use App\Models\DetalleQuiniela;
use App\Models\Jugada;
use App\Models\PrecioHistorico;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cliente = User::factory()->cliente()->create();
    $this->admin = User::factory()->admin()->create();

    PrecioHistorico::create(['tipo_juego' => 'quini6', 'valor' => 1200, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'lotoplus', 'valor' => 1500, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'loto5', 'valor' => 800, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'poceada', 'valor' => 1000, 'fecha_inicio' => today()]);
});

it('calcula totales financieros correctamente con BalanceService', function () {
    $service = new BalanceService;

    // 1 jugada ganadora: apuesta $1000, premio $5000
    $jugada1 = Jugada::factory()->ganadora(5000)->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1000,
    ]);

    // 1 jugada no ganadora: apuesta $800, premio $0
    $jugada2 = Jugada::factory()->noGanadora()->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 800,
    ]);

    // 1 jugada pendiente: apuesta $1200
    $jugada3 = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1200,
        'resultado' => 'pendiente_sorteo',
        'monto_premio' => 0,
    ]);

    $totales = $service->obtenerTotales($this->cliente->id);

    expect($totales['total_invertido'])->toBe(3000.0)
        ->and($totales['total_ganancias'])->toBe(5000.0)
        ->and($totales['total_perdidas'])->toBe(800.0)
        ->and($totales['balance_neto'])->toBe(2000.0)
        ->and($totales['total_jugadas'])->toBe(3)
        ->and($totales['jugadas_ganadoras'])->toBe(1)
        ->and($totales['jugadas_no_ganadoras'])->toBe(1)
        ->and($totales['jugadas_pendientes'])->toBe(1);
});

it('desglosa jugadas por categorías en BalanceService', function () {
    $service = new BalanceService;

    // Jugada de Quiniela
    $jugadaQuiniela = Jugada::factory()->ganadora(7000)->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1000,
    ]);
    DetalleQuiniela::create([
        'jugada_id' => $jugadaQuiniela->id,
        'numero' => '1234',
        'posicion' => 1,
        'jurisdiccion' => 'nacion',
        'importe' => 1000,
    ]);

    // Jugada de Quini 6
    $jugadaQuini = Jugada::factory()->noGanadora()->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1200,
    ]);
    DetalleQuini6::create([
        'jugada_id' => $jugadaQuini->id,
        'numeros' => [1, 2, 3, 4, 5, 6],
    ]);

    $categorias = $service->obtenerDesgloseCategorias($this->cliente->id);

    expect($categorias['quiniela']['cantidad_jugadas'])->toBe(1)
        ->and($categorias['quiniela']['total_invertido'])->toBe(1000.0)
        ->and($categorias['quiniela']['total_ganancias'])->toBe(7000.0)
        ->and($categorias['quiniela']['balance_neto'])->toBe(6000.0)
        ->and($categorias['quini6']['cantidad_jugadas'])->toBe(1)
        ->and($categorias['quini6']['total_invertido'])->toBe(1200.0)
        ->and($categorias['quini6']['total_perdidas'])->toBe(1200.0)
        ->and($categorias['quini6']['balance_neto'])->toBe(-1200.0)
        ->and($categorias['loto5']['cantidad_jugadas'])->toBe(0);
});

it('muestra el balance y la tabla de categorías en la vista del cliente', function () {
    $jugada = Jugada::factory()->ganadora(10000)->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1500,
    ]);
    DetalleQuiniela::create([
        'jugada_id' => $jugada->id,
        'numero' => '4321',
        'posicion' => 1,
        'jurisdiccion' => 'provincia',
        'importe' => 1500,
    ]);

    $response = $this->actingAs($this->cliente)->get(route('jugadas.index'));

    $response->assertOk()
        ->assertSee('Mis Boletas y Balance')
        ->assertSee('Total Invertido')
        ->assertSee('Total Ganancias')
        ->assertSee('Total Pérdidas')
        ->assertSee('Balance Neto')
        ->assertSee('Desglose por Categorías de Juego')
        ->assertSee('Quiniela Tradicional')
        ->assertSee('10.000,00');
});

it('permite al admin actualizar el resultado y premio de una jugada', function () {
    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1000,
        'resultado' => 'pendiente_sorteo',
        'monto_premio' => 0,
    ]);

    $response = $this->actingAs($this->admin)->patch(route('admin.jugadas.resultado', $jugada), [
        'resultado' => 'ganadora',
        'monto_premio' => 8500.50,
    ]);

    $response->assertRedirect();
    $jugada->refresh();

    expect($jugada->resultado)->toBe('ganadora')
        ->and((float) $jugada->monto_premio)->toBe(8500.50);

    // Verifica que se generó log de auditoría
    $this->assertDatabaseHas('audit_logs', [
        'accion' => 'resultado_actualizado',
        'user_id' => $this->admin->id,
    ]);
});

it('no permite a un usuario cliente actualizar el resultado de una jugada', function () {
    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1000,
        'resultado' => 'pendiente_sorteo',
    ]);

    $response = $this->actingAs($this->cliente)->patch(route('admin.jugadas.resultado', $jugada), [
        'resultado' => 'ganadora',
        'monto_premio' => 5000,
    ]);

    $response->assertForbidden();
});

it('muestra el balance de la agencia y de cada usuario en el panel admin', function () {
    $jugada = Jugada::factory()->ganadora(15000)->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 2000,
    ]);

    // Dashboard global
    $resDashboard = $this->actingAs($this->admin)->get(route('admin.dashboard'));
    $resDashboard->assertOk()
        ->assertSee('Recaudación Bruta')
        ->assertSee('Premios Liquidados')
        ->assertSee('Ganancia Neta Agencia')
        ->assertSee('Rentabilidad por Categoría de Juego');

    // Ficha individual de cliente
    $resUser = $this->actingAs($this->admin)->get(route('admin.users.show', $this->cliente));
    $resUser->assertOk()
        ->assertSee('Jugadas de: '.$this->cliente->name)
        ->assertSee('Desglose por Categorías de este Cliente')
        ->assertSee('15.000,00');
});
