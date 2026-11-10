<?php

use App\Models\AuditLog;
use App\Models\DetalleLoto5;
use App\Models\DetalleQuini6;
use App\Models\DetalleQuiniela;
use App\Models\Jugada;
use App\Models\Sorteo;
use App\Models\User;
use App\Services\Escrutinio\EscrutinioService;
use App\Services\Escrutinio\Loto5Evaluator;
use App\Services\Escrutinio\LotoPlusEvaluator;
use App\Services\Escrutinio\PoceadaEvaluator;
use App\Services\Escrutinio\Quini6Evaluator;
use App\Services\Escrutinio\QuinielaEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cliente = User::factory()->cliente()->create(['saldo' => 5000.00]);
    $this->admin = User::factory()->admin()->create();

    $this->quinielaEvaluator = new QuinielaEvaluator;
    $this->quini6Evaluator = new Quini6Evaluator;
    $this->lotoPlusEvaluator = new LotoPlusEvaluator;
    $this->loto5Evaluator = new Loto5Evaluator;
    $this->poceadaEvaluator = new PoceadaEvaluator;

    $this->escrutinioService = new EscrutinioService(
        $this->quinielaEvaluator,
        $this->quini6Evaluator,
        $this->lotoPlusEvaluator,
        $this->loto5Evaluator,
        $this->poceadaEvaluator,
    );
});

it('evalua acierto a la cabeza en quiniela con multiplicador exacto', function () {
    $sorteo = Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'extracto' => [
            1 => '4825',
            2 => '1234',
            3 => '5678',
            4 => '9012',
            5 => '3456',
        ],
        'created_by' => $this->admin->id,
    ]);

    // Jugada 1: 2 cifras a la cabeza ($100 al 25) -> Debe ganar 100 * 70 = $7000
    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today()->setHour(10),
    ]);
    DetalleQuiniela::create([
        'jugada_id' => $jugada->id,
        'numero' => '25',
        'posicion' => 1,
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'importe' => 100,
    ]);

    $res = $this->quinielaEvaluator->evaluar($jugada, $sorteo);

    expect($res['ganadora'])->toBeTrue()
        ->and($res['monto_premio'])->toBe(7000.0)
        ->and($res['detalles'][0]['aciertos'])->toBe(1);
});

it('evalua aciertos a los 5 en quiniela con repeticiones proporcionales', function () {
    $sorteo = Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'provincia',
        'turno' => 'nocturna',
        'extracto' => [
            1 => '1145',
            2 => '2245',
            3 => '9999',
            4 => '8888',
            5 => '7777',
        ],
        'created_by' => $this->admin->id,
    ]);

    // Apuesta $100 al '45' a los 5. Aparece 2 veces (pos 1 y 2).
    // Factor por repetición = (70 / 5) = 14x. Con 2 repeticiones: 100 * 14 * 2 = $2800.
    $jugada = Jugada::factory()->create(['user_id' => $this->cliente->id]);
    DetalleQuiniela::create([
        'jugada_id' => $jugada->id,
        'numero' => '45',
        'posicion' => 5,
        'jurisdiccion' => 'provincia',
        'turno' => 'nocturna',
        'importe' => 100,
    ]);

    $res = $this->quinielaEvaluator->evaluar($jugada, $sorteo);

    expect($res['ganadora'])->toBeTrue()
        ->and($res['monto_premio'])->toBe(2800.0)
        ->and($res['detalles'][0]['aciertos'])->toBe(2);
});

it('no premia si la jurisdiccion o el turno de la quiniela no coinciden', function () {
    $sorteo = Sorteo::create([
        'juego' => 'quiniela',
        'fecha_sorteo' => today(),
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'extracto' => [1 => '4825'],
        'created_by' => $this->admin->id,
    ]);

    $jugada = Jugada::factory()->create(['user_id' => $this->cliente->id]);
    DetalleQuiniela::create([
        'jugada_id' => $jugada->id,
        'numero' => '25',
        'posicion' => 1,
        'jurisdiccion' => 'provincia', // Distinta jurisdicción
        'turno' => 'matutina',
        'importe' => 100,
    ]);

    $res = $this->quinielaEvaluator->evaluar($jugada, $sorteo);

    expect($res['ganadora'])->toBeFalse()
        ->and($res['monto_premio'])->toBe(0.0);
});

it('evalua aciertos en Quini 6 segun tabla de premios personalizada', function () {
    $sorteo = Sorteo::create([
        'juego' => 'quini6',
        'fecha_sorteo' => today(),
        'extracto' => ['numeros' => [4, 12, 18, 25, 33, 40]],
        'tabla_premios' => [
            '6' => 60000000,
            '5' => 200000,
            '4' => 8000,
        ],
        'created_by' => $this->admin->id,
    ]);

    // Jugada con 5 aciertos: [4, 12, 18, 25, 33, 45] (falla el 45)
    $jugada = Jugada::factory()->create(['user_id' => $this->cliente->id]);
    DetalleQuini6::create([
        'jugada_id' => $jugada->id,
        'numeros' => [4, 12, 18, 25, 33, 45],
    ]);

    $res = $this->quini6Evaluator->evaluar($jugada, $sorteo);

    expect($res['ganadora'])->toBeTrue()
        ->and($res['monto_premio'])->toBe(200000.0)
        ->and($res['detalles'][0]['aciertos'])->toBe(5);
});

it('simula escrutinio sin modificar la base de datos', function () {
    $sorteo = Sorteo::create([
        'juego' => 'loto5',
        'fecha_sorteo' => today(),
        'extracto' => ['numeros' => [5, 10, 15, 20, 25]],
        'tabla_premios' => [
            '5' => 500000,
            '4' => 30000,
            '3' => 2000,
        ],
        'created_by' => $this->admin->id,
    ]);

    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today(),
        'resultado' => 'pendiente_sorteo',
    ]);
    DetalleLoto5::create([
        'jugada_id' => $jugada->id,
        'numeros' => [5, 10, 15, 30, 35], // 3 aciertos
    ]);

    $simulacion = $this->escrutinioService->simular($sorteo);

    expect($simulacion['total_boletas'])->toBe(1)
        ->and($simulacion['total_ganadoras'])->toBe(1)
        ->and($simulacion['total_premios'])->toBe(2000.0);

    // Verificar que la base de datos no fue tocada
    $jugada->refresh();
    expect($jugada->resultado)->toBe('pendiente_sorteo')
        ->and((float) $jugada->monto_premio)->toBe(0.0)
        ->and((float) $this->cliente->fresh()->saldo)->toBe(5000.00);
});

it('ejecuta escrutinio definitivo, acredita saldo y registra en audit_logs', function () {
    $sorteo = Sorteo::create([
        'juego' => 'quini6',
        'fecha_sorteo' => today(),
        'extracto' => ['numeros' => [1, 2, 3, 4, 5, 6]],
        'tabla_premios' => ['4' => 10000],
        'created_by' => $this->admin->id,
    ]);

    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today(),
        'resultado' => 'pendiente_sorteo',
        'monto_premio' => 0,
    ]);
    DetalleQuini6::create([
        'jugada_id' => $jugada->id,
        'numeros' => [1, 2, 3, 4, 20, 30], // 4 aciertos
    ]);

    $saldoInicial = (float) $this->cliente->saldo; // 5000.00

    $res = $this->escrutinioService->escrutar($sorteo, $this->admin);

    expect($res['total_boletas'])->toBe(1)
        ->and($res['total_ganadoras'])->toBe(1)
        ->and($res['total_premios'])->toBe(10000.0);

    // Boleta actualizada
    $jugada->refresh();
    expect($jugada->resultado)->toBe('ganadora')
        ->and((float) $jugada->monto_premio)->toBe(10000.0)
        ->and($jugada->sorteo_id)->toBe($sorteo->id)
        ->and($jugada->detalle_escrutinio)->not->toBeNull();

    // Saldo acreditado al cliente
    expect((float) $this->cliente->fresh()->saldo)->toBe($saldoInicial + 10000.0);

    // Sorteo marcado como escrutado
    $sorteo->refresh();
    expect($sorteo->estado)->toBe('escrutado')
        ->and($sorteo->total_boletas_ganadoras)->toBe(1)
        ->and((float) $sorteo->total_premios_liquidados)->toBe(10000.0)
        ->and($sorteo->escrutado_en)->not->toBeNull();

    // Audit log registrado
    expect(AuditLog::where('accion', 'sorteo_escrutado')->exists())->toBeTrue();
});

it('revierte un sorteo escrutado, ajusta saldos y devuelve boletas a pendiente', function () {
    $sorteo = Sorteo::create([
        'juego' => 'quini6',
        'fecha_sorteo' => today(),
        'extracto' => ['numeros' => [1, 2, 3, 4, 5, 6]],
        'tabla_premios' => ['4' => 10000],
        'created_by' => $this->admin->id,
    ]);

    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today(),
        'resultado' => 'pendiente_sorteo',
        'monto_premio' => 0,
    ]);
    DetalleQuini6::create([
        'jugada_id' => $jugada->id,
        'numeros' => [1, 2, 3, 4, 20, 30],
    ]);

    // 1. Escrutamos
    $this->escrutinioService->escrutar($sorteo, $this->admin);
    expect((float) $this->cliente->fresh()->saldo)->toBe(15000.00);

    // 2. Revertimos
    $revertido = $this->escrutinioService->revertir($sorteo, $this->admin);
    expect($revertido)->toBeTrue();

    // Saldo restaurado (se descuentan los 10000 acreditados por error)
    expect((float) $this->cliente->fresh()->saldo)->toBe(5000.00);

    // Boleta restaurada a pendiente_sorteo
    $jugada->refresh();
    expect($jugada->resultado)->toBe('pendiente_sorteo')
        ->and((float) $jugada->monto_premio)->toBe(0.0)
        ->and($jugada->sorteo_id)->toBeNull()
        ->and($jugada->detalle_escrutinio)->toBeNull();

    // Sorteo marcado como anulado
    $sorteo->refresh();
    expect($sorteo->estado)->toBe('anulado');

    // Audit log de reversión registrado
    expect(AuditLog::where('accion', 'sorteo_revertido')->exists())->toBeTrue();
});
