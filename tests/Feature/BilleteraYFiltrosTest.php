<?php

use App\Models\DetalleQuiniela;
use App\Models\Jugada;
use App\Models\User;
use Database\Seeders\ClientesSeeder;
use Database\Seeders\JugadasSeeder;
use Database\Seeders\PreciosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PreciosSeeder::class);

    $this->admin = User::factory()->admin()->create([
        'email' => 'admin@agencia.com',
    ]);

    $this->cliente = User::factory()->cliente()->create([
        'saldo' => 10000.00,
    ]);
});

test('el usuario nuevo tiene saldo por defecto', function () {
    $user = User::factory()->cliente()->create();
    expect((float) $user->saldo)->toBe(10000.00);
});

test('el usuario puede recargar saldo en su billetera', function () {
    $response = $this->actingAs($this->cliente)
        ->post(route('billetera.recargar'), [
            'monto' => 5000,
        ]);

    $response->assertSessionHas('success');
    expect((float) $this->cliente->fresh()->saldo)->toBe(15000.00);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $this->cliente->id,
        'accion' => 'recarga_saldo',
    ]);
});

test('rechaza la jugada si el saldo es insuficiente', function () {
    $this->cliente->update(['saldo' => 200.00]);

    $payload = [
        'idempotency_token' => (string) Str::uuid(),
        'cart' => [
            [
                'modalidad' => 'quini6',
                'numeros' => [1, 2, 3, 4, 5, 6],
                'costo' => 1200.00,
            ],
        ],
    ];

    $response = $this->actingAs($this->cliente)
        ->post(route('jugadas.store'), $payload);

    $response->assertSessionHas('error');
    expect(Jugada::count())->toBe(0);
    expect((float) $this->cliente->fresh()->saldo)->toBe(200.00);
});

test('debita el saldo al confirmar una jugada exitosa', function () {
    $payload = [
        'idempotency_token' => (string) Str::uuid(),
        'cart' => [
            [
                'modalidad' => 'quini6',
                'numeros' => [5, 12, 23, 34, 40, 45],
                'costo' => 1200.00,
            ],
        ],
    ];

    $response = $this->actingAs($this->cliente)
        ->post(route('jugadas.store'), $payload);

    $response->assertRedirect(route('jugadas.index'));
    expect((float) $this->cliente->fresh()->saldo)->toBe(8800.00);
    expect(Jugada::count())->toBe(1);
});

test('acredita el premio en la billetera del cliente cuando el administrador liquida una jugada ganadora', function () {
    $jugada = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1200.00,
        'estado' => 'procesado',
        'resultado' => 'pendiente_sorteo',
        'monto_premio' => 0.0,
        'idempotency_token' => (string) Str::uuid(),
    ]);

    $saldoInicial = (float) $this->cliente->saldo;

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.jugadas.resultado', $jugada), [
            'resultado' => 'ganadora',
            'monto_premio' => 50000.00,
        ]);

    $response->assertSessionHas('success');
    expect((float) $this->cliente->fresh()->saldo)->toBe($saldoInicial + 50000.00);
    expect((float) $jugada->fresh()->monto_premio)->toBe(50000.00);
    expect($jugada->fresh()->resultado)->toBe('ganadora');
});

test('filtra jugadas por categoria en el panel del cliente', function () {
    // Jugada 1: Quiniela
    $jQuiniela = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 500.00,
        'estado' => 'procesado',
        'resultado' => 'pendiente_sorteo',
        'idempotency_token' => (string) Str::uuid(),
    ]);
    $jQuiniela->detallesQuiniela()->create([
        'numero' => '1234',
        'posicion' => 1,
        'jurisdiccion' => 'nacion',
        'importe' => 500.00,
    ]);

    // Jugada 2: Quini 6
    $jQuini = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1200.00,
        'estado' => 'procesado',
        'resultado' => 'pendiente_sorteo',
        'idempotency_token' => (string) Str::uuid(),
    ]);
    $jQuini->detallesQuini6()->create([
        'numeros' => [1, 2, 3, 4, 5, 6],
    ]);

    // Filtrar por Quiniela
    $resQuiniela = $this->actingAs($this->cliente)
        ->get(route('jugadas.index', ['categoria' => 'quiniela']));

    $resQuiniela->assertStatus(200);
    $resQuiniela->assertSee('#'.str_pad($jQuiniela->id, 8, '0', STR_PAD_LEFT));
    $resQuiniela->assertDontSee('#'.str_pad($jQuini->id, 8, '0', STR_PAD_LEFT));

    // Filtrar por Quini 6
    $resQuini = $this->actingAs($this->cliente)
        ->get(route('jugadas.index', ['categoria' => 'quini6']));

    $resQuini->assertStatus(200);
    $resQuini->assertSee('#'.str_pad($jQuini->id, 8, '0', STR_PAD_LEFT));
    $resQuini->assertDontSee('#'.str_pad($jQuiniela->id, 8, '0', STR_PAD_LEFT));
});

test('filtra jugadas por fecha puntual y por rango de fechas', function () {
    $fechaAntigua = Carbon::now()->subDays(10);
    $fechaReciente = Carbon::now()->subDays(2);

    $j1 = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1200.00,
        'estado' => 'procesado',
        'resultado' => 'pendiente_sorteo',
        'idempotency_token' => (string) Str::uuid(),
    ]);
    $j1->created_at = $fechaAntigua;
    $j1->save();

    $j2 = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1200.00,
        'estado' => 'procesado',
        'resultado' => 'pendiente_sorteo',
        'idempotency_token' => (string) Str::uuid(),
    ]);
    $j2->created_at = $fechaReciente;
    $j2->save();

    // Filtro por fecha puntual
    $resPuntual = $this->actingAs($this->cliente)
        ->get(route('jugadas.index', ['fecha' => $fechaAntigua->toDateString()]));

    $resPuntual->assertSee('#'.str_pad($j1->id, 8, '0', STR_PAD_LEFT));
    $resPuntual->assertDontSee('#'.str_pad($j2->id, 8, '0', STR_PAD_LEFT));

    // Filtro por rango
    $resRango = $this->actingAs($this->cliente)
        ->get(route('jugadas.index', [
            'fecha_desde' => Carbon::now()->subDays(3)->toDateString(),
            'fecha_hasta' => Carbon::now()->toDateString(),
        ]));

    $resRango->assertSee('#'.str_pad($j2->id, 8, '0', STR_PAD_LEFT));
    $resRango->assertDontSee('#'.str_pad($j1->id, 8, '0', STR_PAD_LEFT));
});

test('filtra jugadas por resultado y por estado en el panel del cliente', function () {
    $jGanadora = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1200.00,
        'estado' => 'procesado',
        'resultado' => 'ganadora',
        'monto_premio' => 50000.00,
        'idempotency_token' => (string) Str::uuid(),
    ]);

    $jPendiente = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 800.00,
        'estado' => 'pendiente',
        'resultado' => 'no_ganadora',
        'monto_premio' => 0.0,
        'idempotency_token' => (string) Str::uuid(),
    ]);

    // Filtro por resultado: ganadora
    $resGanadora = $this->actingAs($this->cliente)
        ->get(route('jugadas.index', ['resultado' => 'ganadora']));

    $resGanadora->assertSee('#'.str_pad($jGanadora->id, 8, '0', STR_PAD_LEFT));
    $resGanadora->assertDontSee('#'.str_pad($jPendiente->id, 8, '0', STR_PAD_LEFT));

    // Filtro por estado: pendiente
    $resPendiente = $this->actingAs($this->cliente)
        ->get(route('jugadas.index', ['estado' => 'pendiente']));

    $resPendiente->assertSee('#'.str_pad($jPendiente->id, 8, '0', STR_PAD_LEFT));
    $resPendiente->assertDontSee('#'.str_pad($jGanadora->id, 8, '0', STR_PAD_LEFT));
});

test('el panel de administracion filtra por categoria y fechas correctamente', function () {
    $jQuiniela = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 500.00,
        'estado' => 'procesado',
        'resultado' => 'pendiente_sorteo',
        'idempotency_token' => (string) Str::uuid(),
    ]);
    $jQuiniela->detallesQuiniela()->create([
        'numero' => '9999',
        'posicion' => 1,
        'jurisdiccion' => 'cordoba',
        'importe' => 500.00,
    ]);

    $jPoceada = Jugada::create([
        'user_id' => $this->cliente->id,
        'monto_total' => 1000.00,
        'estado' => 'procesado',
        'resultado' => 'pendiente_sorteo',
        'idempotency_token' => (string) Str::uuid(),
    ]);
    $jPoceada->detallesPoceada()->create([
        'numeros' => [1, 2, 3, 4, 5, 6, 7, 8],
    ]);

    // Filtrar admin por poceada
    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard', ['categoria' => 'poceada']));

    $response->assertStatus(200);
    $response->assertSee('#'.str_pad($jPoceada->id, 8, '0', STR_PAD_LEFT));
    $response->assertDontSee('#'.str_pad($jQuiniela->id, 8, '0', STR_PAD_LEFT));
});

test('el seeder de jugadas puebla boletas realistas de prueba', function () {
    $this->seed(JugadasSeeder::class);

    expect(Jugada::count())->toBeGreaterThanOrEqual(25);
    expect(Jugada::where('resultado', 'ganadora')->count())->toBeGreaterThan(0);
    expect(Jugada::where('resultado', 'no_ganadora')->count())->toBeGreaterThan(0);
    expect(DetalleQuiniela::count())->toBeGreaterThan(0);
});

test('el seeder de clientes crea usuarios con password y jugadas asociadas', function () {
    $this->seed(ClientesSeeder::class);

    $clientes = User::where('rol', 'cliente')->whereIn('email', [
        'juan.perez@ejemplo.com',
        'maria.gomez@ejemplo.com',
        'carlos.lopez@ejemplo.com',
        'laura.martinez@ejemplo.com',
        'lucas.rodriguez@ejemplo.com',
    ])->get();

    expect($clientes->count())->toBe(5);

    foreach ($clientes as $cliente) {
        expect(Hash::check('password', $cliente->password))->toBeTrue();
        expect($cliente->jugadas()->count())->toBeGreaterThanOrEqual(3);
    }
});
