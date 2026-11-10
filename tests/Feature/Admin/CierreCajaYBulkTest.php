<?php

use App\Mail\NotificacionResultadoMail;
use App\Models\AuditLog;
use App\Models\DetalleQuiniela;
use App\Models\Jugada;
use App\Models\User;
use App\Services\CierreCajaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->cliente = User::factory()->cliente()->create(['saldo' => 5000.00]);
});

it('genera el texto de transcripcion estandarizado para terminales de loteria', function () {
    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'monto_total' => 500,
    ]);
    DetalleQuiniela::create([
        'jugada_id' => $jugada->id,
        'numero' => '4512',
        'posicion' => 5,
        'jurisdiccion' => 'nacion',
        'turno' => 'matutina',
        'importe' => 500,
    ]);

    $texto = $jugada->texto_transcripcion;

    expect($texto)->toContain('QUINIELA (NACION - MATUTINA)')
        ->and($texto)->toContain('Nº 4512 a los 5 ($500)')
        ->and($texto)->toContain($this->cliente->name)
        ->and($texto)->toContain("DNI {$this->cliente->dni}");
});

it('permite al admin procesar boletas en lote y notifica por email al usuario', function () {
    Mail::fake();

    $jugada1 = Jugada::factory()->create(['user_id' => $this->cliente->id, 'estado' => 'pendiente']);
    $jugada2 = Jugada::factory()->create(['user_id' => $this->cliente->id, 'estado' => 'pendiente']);

    $response = $this->actingAs($this->admin)->post(route('admin.jugadas.bulk'), [
        'ids' => [$jugada1->id, $jugada2->id],
        'estado' => 'procesado',
    ]);

    $response->assertRedirect();

    expect($jugada1->fresh()->estado)->toBe('procesado')
        ->and($jugada2->fresh()->estado)->toBe('procesado');

    Mail::assertSent(NotificacionResultadoMail::class, 2);

    expect(AuditLog::where('accion', 'jugadas_bulk_update')->exists())->toBeTrue();
});

it('permite procesar todas las jugadas pendientes del dia de hoy en un solo clic', function () {
    Mail::fake();

    $jugadaHoy = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today()->setHour(11),
        'estado' => 'pendiente',
    ]);

    $jugadaAyer = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today()->subDay(),
        'estado' => 'pendiente',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.jugadas.bulk_hoy'));

    $response->assertRedirect();

    expect($jugadaHoy->fresh()->estado)->toBe('procesado')
        ->and($jugadaAyer->fresh()->estado)->toBe('pendiente'); // No modifica la de ayer

    Mail::assertSent(NotificacionResultadoMail::class, 1);
});

it('notifica al usuario por email cuando el admin actualiza el resultado de su jugada', function () {
    Mail::fake();

    $jugada = Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'resultado' => 'pendiente_sorteo',
        'monto_premio' => 0,
    ]);

    $this->actingAs($this->admin)->patch(route('admin.jugadas.resultado', $jugada), [
        'resultado' => 'ganadora',
        'monto_premio' => 15000,
    ]);

    expect($jugada->fresh()->resultado)->toBe('ganadora')
        ->and((float) $jugada->fresh()->monto_premio)->toBe(15000.0)
        ->and((float) $this->cliente->fresh()->saldo)->toBe(20000.00); // 5000 + 15000

    Mail::assertSent(NotificacionResultadoMail::class, function ($mail) use ($jugada) {
        return $mail->jugada->id === $jugada->id;
    });
});

it('calcula estrictamente el arqueo contable con CierreCajaService', function () {
    $service = new CierreCajaService;

    // Boleta ganadora: apuesta $1000, premio $5000
    Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today(),
        'monto_total' => 1000,
        'resultado' => 'ganadora',
        'monto_premio' => 5000,
    ]);

    // Boleta no ganadora: apuesta $2000, premio $0
    Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today(),
        'monto_total' => 2000,
        'resultado' => 'no_ganadora',
        'monto_premio' => 0,
    ]);

    $arqueo = $service->obtenerArqueo(today()->format('Y-m-d'));

    expect($arqueo['total_recaudado'])->toBe(3000.0)
        ->and($arqueo['total_premios'])->toBe(5000.0)
        ->and($arqueo['balance_neto'])->toBe(-2000.0) // 3000 - 5000 = -2000
        ->and($arqueo['total_boletas'])->toBe(2);
});

it('permite al admin consultar la vista de Cierre de Caja y descargar la planilla PDF', function () {
    Jugada::factory()->create([
        'user_id' => $this->cliente->id,
        'created_at' => today(),
        'monto_total' => 1500,
    ]);

    // Vista HTML
    $this->actingAs($this->admin)
        ->get(route('admin.caja.index'))
        ->assertOk()
        ->assertSee('Cierre de Caja y Arqueo Diario')
        ->assertSee('Total Recaudado (Apuestas)')
        ->assertSee('Premios Pagados (Egresos)')
        ->assertSee('Balance Neto de Caja')
        ->assertSee('$1.500,00');

    // Descarga PDF
    $pdfResponse = $this->actingAs($this->admin)
        ->get(route('admin.caja.pdf', ['fecha' => today()->format('Y-m-d')]));

    $pdfResponse->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('bloquea el acceso a Cierre de Caja a clientes comunes', function () {
    $this->actingAs($this->cliente)
        ->get(route('admin.caja.index'))
        ->assertForbidden();

    $this->actingAs($this->cliente)
        ->get(route('admin.caja.pdf'))
        ->assertForbidden();
});
