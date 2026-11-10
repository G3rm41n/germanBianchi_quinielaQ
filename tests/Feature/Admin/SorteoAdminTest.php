<?php

use App\Models\DetalleQuiniela;
use App\Models\Jugada;
use App\Models\Sorteo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->cliente = User::factory()->cliente()->create(['saldo' => 2000.00]);
});

it('protege las rutas de administracion de sorteos contra usuarios no autorizados', function () {
    // Invitado redirigido a login
    $this->get(route('admin.sorteos.index'))->assertRedirect(route('login'));

    // Cliente regular recibe 403 Forbidden
    $this->actingAs($this->cliente)
        ->get(route('admin.sorteos.index'))
        ->assertForbidden();
});

it('permite al admin listar y ver metricas de sorteos', function () {
    Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'extracto' => array_fill(1, 20, '1234'),
        'created_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.sorteos.index'))
        ->assertOk()
        ->assertSee('Sorteos y Escrutinio')
        ->assertSee('Quiniela')
        ->assertSee('Nación / Ciudad');
});

it('permite al admin crear un sorteo de Quiniela con 20 posiciones, turno y jurisdiccion', function () {
    $extracto = [];
    for ($i = 1; $i <= 20; $i++) {
        $extracto[$i] = str_pad((string) ($i * 111), 4, '0', STR_PAD_LEFT);
    }

    $response = $this->actingAs($this->admin)->post(route('admin.sorteos.store'), [
        'juego' => 'quiniela',
        'fecha_sorteo' => today()->format('Y-m-d'),
        'numero_sorteo' => 'Sorteo Matutina #1',
        'jurisdiccion' => 'provincia',
        'turno' => 'matutina',
        'extracto' => $extracto,
    ]);

    $sorteo = Sorteo::first();
    expect($sorteo)->not->toBeNull()
        ->and($sorteo->juego)->toBe('quiniela')
        ->and($sorteo->jurisdiccion)->toBe('provincia')
        ->and($sorteo->turno)->toBe('matutina')
        ->and($sorteo->estado)->toBe('pendiente')
        ->and(count($sorteo->extracto))->toBe(20);

    $response->assertRedirect(route('admin.sorteos.show', $sorteo));
});

it('permite al admin crear un sorteo de Quini 6 con tabla de premios personalizada', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.sorteos.store'), [
        'juego' => 'quini6',
        'fecha_sorteo' => today()->format('Y-m-d'),
        'numero_sorteo' => 'Concurso 3150',
        'quini6_numeros' => [5, 12, 19, 23, 31, 42],
        'quini6_premios' => [
            '6' => 50000000,
            '5' => 180000,
            '4' => 6000,
        ],
    ]);

    $sorteo = Sorteo::where('juego', 'quini6')->first();
    expect($sorteo)->not->toBeNull()
        ->and($sorteo->extracto['numeros'])->toBe([5, 12, 19, 23, 31, 42])
        ->and((float) $sorteo->tabla_premios['6'])->toBe(50000000.0);

    $response->assertRedirect(route('admin.sorteos.show', $sorteo));
});

it('muestra la previsualizacion de la simulacion y ejecuta la liquidacion via HTTP', function () {
    // 1. Crear boleta de Quiniela pendiente
    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today(),
        'resultado' => 'pendiente_sorteo',
    ]);
    DetalleQuiniela::create([
        'jugada_id' => $jugada->id,
        'numero' => '42',
        'posicion' => 1,
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'importe' => 100,
    ]);

    // 2. Crear sorteo donde sale el 42 a la cabeza
    $extracto = array_fill(1, 20, '1000');
    $extracto[1] = '8742';

    $sorteo = Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'extracto' => $extracto,
        'created_by' => $this->admin->id,
    ]);

    // Ver pantalla de simulación
    $this->actingAs($this->admin)
        ->get(route('admin.sorteos.show', $sorteo))
        ->assertOk()
        ->assertSee('Previsualización del Escrutinio')
        ->assertSee('$7.000,00');

    // Ejecutar escrutinio
    $this->actingAs($this->admin)
        ->post(route('admin.sorteos.escrutar', $sorteo))
        ->assertRedirect(route('admin.sorteos.show', $sorteo));

    $sorteo->refresh();
    $jugada->refresh();
    $this->cliente->refresh();

    expect($sorteo->estado)->toBe('escrutado')
        ->and($jugada->resultado)->toBe('ganadora')
        ->and((float) $jugada->monto_premio)->toBe(7000.0)
        ->and((float) $this->cliente->saldo)->toBe(9000.00); // 2000 inicial + 7000 premio
});

it('permite revertir un sorteo escrutado descontando saldos y restaurando boletas', function () {
    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today(),
        'resultado' => 'pendiente_sorteo',
    ]);
    DetalleQuiniela::create([
        'jugada_id' => $jugada->id,
        'numero' => '42',
        'posicion' => 1,
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'importe' => 100,
    ]);

    $extracto = array_fill(1, 20, '1000');
    $extracto[1] = '8742';

    $sorteo = Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'extracto' => $extracto,
        'created_by' => $this->admin->id,
    ]);

    // Escrutamos
    $this->actingAs($this->admin)->post(route('admin.sorteos.escrutar', $sorteo));
    expect((float) $this->cliente->fresh()->saldo)->toBe(9000.00);

    // Revertimos
    $this->actingAs($this->admin)
        ->post(route('admin.sorteos.revertir', $sorteo))
        ->assertRedirect(route('admin.sorteos.show', $sorteo));

    $sorteo->refresh();
    $jugada->refresh();
    $this->cliente->refresh();

    expect($sorteo->estado)->toBe('anulado')
        ->and($jugada->resultado)->toBe('pendiente_sorteo')
        ->and((float) $jugada->monto_premio)->toBe(0.0)
        ->and((float) $this->cliente->saldo)->toBe(2000.00);
});

it('permite acceso publico a la pizarra de sorteos', function () {
    Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'nacion',
        'turno' => 'vespertina',
        'extracto' => array_fill(1, 20, '5555'),
        'created_by' => $this->admin->id,
    ]);

    // Usuario invitado
    $this->get(route('sorteos.index'))
        ->assertOk()
        ->assertSee('Pizarra de Sorteos Oficiales')
        ->assertSee('Vespertina');
});

it('permite filtrar sorteos por juego y turno tanto en admin como en publico', function () {
    Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'extracto' => array_fill(1, 20, '1111'),
        'created_by' => $this->admin->id,
    ]);

    Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'provincia',
        'turno' => 'nocturna',
        'extracto' => array_fill(1, 20, '2222'),
        'created_by' => $this->admin->id,
    ]);

    // Filtrar por Matutina en admin
    $responseAdmin = $this->actingAs($this->admin)
        ->get(route('admin.sorteos.index', ['juego' => 'quiniela', 'turno' => 'matutina']));
    $responseAdmin->assertOk()
        ->assertSee('Nación / Ciudad')
        ->assertDontSee('Provincia Bs. As.');
    expect($responseAdmin->viewData('sorteos')->pluck('turno')->all())->toBe(['matutina']);

    // Filtrar por Nocturna en público
    $responsePublic = $this->get(route('sorteos.index', ['juego' => 'quiniela', 'turno' => 'nocturna']));
    $responsePublic->assertOk()
        ->assertSee('Provincia Bs. As.')
        ->assertDontSee('Nación / Ciudad');
    expect($responsePublic->viewData('sorteos')->pluck('turno')->all())->toBe(['nocturna']);
});
