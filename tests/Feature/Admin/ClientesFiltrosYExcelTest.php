<?php

use App\Models\Jugada;
use App\Models\PrecioHistorico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();

    PrecioHistorico::create(['tipo_juego' => 'quini6', 'valor' => 1200, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'lotoplus', 'valor' => 1500, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'loto5', 'valor' => 800, 'fecha_inicio' => today()]);
    PrecioHistorico::create(['tipo_juego' => 'poceada', 'valor' => 1000, 'fecha_inicio' => today()]);
});

it('permite filtrar clientes por inicial del nombre', function () {
    User::factory()->create(['name' => 'Carlos Gómez', 'rol' => 'cliente']);
    User::factory()->create(['name' => 'Ana Martínez', 'rol' => 'cliente']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['inicial_nombre' => 'C']))
        ->assertStatus(200);

    $response->assertSee('Carlos Gómez');
    $response->assertDontSee('Ana Martínez');
});

it('permite filtrar clientes por inicial del apellido', function () {
    User::factory()->create(['name' => 'Lucas Silva', 'rol' => 'cliente']);
    User::factory()->create(['name' => 'Pedro Álvarez', 'rol' => 'cliente']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['inicial_apellido' => 'S']))
        ->assertStatus(200);

    $response->assertSee('Lucas Silva');
    $response->assertDontSee('Pedro Álvarez');
});

it('permite filtrar clientes por cantidad minima de jugadas', function () {
    $clienteConJugadas = User::factory()->create(['name' => 'Cliente Frecuente', 'rol' => 'cliente']);
    $clienteSinJugadas = User::factory()->create(['name' => 'Cliente Inactivo', 'rol' => 'cliente']);

    Jugada::factory()->count(3)->create(['user_id' => $clienteConJugadas->id]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['min_jugadas' => 2]))
        ->assertStatus(200);

    $response->assertSee('Cliente Frecuente');
    $response->assertDontSee('Cliente Inactivo');
});

it('permite filtrar clientes por volumen minimo apostado', function () {
    $clienteGranVolumen = User::factory()->create(['name' => 'Apostador Mayor', 'rol' => 'cliente']);
    $clienteBajoVolumen = User::factory()->create(['name' => 'Apostador Menor', 'rol' => 'cliente']);

    Jugada::factory()->create(['user_id' => $clienteGranVolumen->id, 'monto_total' => 15000]);
    Jugada::factory()->create(['user_id' => $clienteBajoVolumen->id, 'monto_total' => 500]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['min_volumen' => 5000]))
        ->assertStatus(200);

    $response->assertSee('Apostador Mayor');
    $response->assertDontSee('Apostador Menor');
});

it('permite descargar la plantilla de ejemplo de excel', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.users.plantilla'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect($response->headers->get('Content-Disposition'))->toContain('plantilla_clientes_jugadas_ejemplo.xlsx');
});

it('permite exportar clientes y jugadas a un archivo excel', function () {
    $cliente = User::factory()->create(['name' => 'Cliente Exportable', 'rol' => 'cliente']);
    Jugada::factory()->create(['user_id' => $cliente->id, 'monto_total' => 2000]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.users.exportar'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect($response->headers->get('Content-Disposition'))->toContain('clientes_jugadas_');
});

it('permite importar clientes y sus jugadas desde un archivo excel', function () {
    // Generar archivo Excel de prueba en memoria
    $spreadsheet = new Spreadsheet;

    // Hoja 1: Clientes
    $sheetClientes = $spreadsheet->getActiveSheet();
    $sheetClientes->setTitle('Clientes');
    $sheetClientes->fromArray([
        ['ID_Cliente', 'Nombre', 'Apellido', 'DNI', 'Email', 'Saldo_Inicial'],
        ['CLI-TEST-1', 'Martín', 'Palermo', '23999888', 'martin.palermo@test.com', 25000.00],
    ]);

    // Hoja 2: Jugadas
    $sheetJugadas = $spreadsheet->createSheet();
    $sheetJugadas->setTitle('Jugadas');
    $sheetJugadas->fromArray([
        ['ID_Cliente_Ref', 'Modalidad', 'Numeros_O_Detalle', 'Jurisdiccion', 'Posicion', 'Importe_O_Costo', 'Estado', 'Resultado', 'Premio'],
        ['CLI-TEST-1', 'quiniela', '1909', 'nacion', 1, 1500.00, 'procesado', 'ganadora', 52500.00],
        ['CLI-TEST-1', 'quini6', '01, 09, 10, 19, 23, 40', '', '', 1200.00, 'procesado', 'no_ganadora', 0.00],
    ]);

    $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'clientes_test.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->admin)
        ->post(route('admin.users.importar'), [
            'archivo' => $uploadedFile,
        ]);

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('success');

    // Verificar que el usuario fue creado
    $user = User::where('email', 'martin.palermo@test.com')->first();
    expect($user)->not->toBeNull();
    expect($user->dni)->toBe('23999888');
    expect($user->name)->toBe('Martín Palermo');
    expect((float) $user->saldo)->toBe(25000.00);

    // Verificar que se crearon sus jugadas
    expect($user->jugadas()->count())->toBe(2);

    $quiniela = $user->jugadas()->whereHas('detallesQuiniela')->first();
    expect($quiniela)->not->toBeNull();
    expect((float) $quiniela->monto_total)->toBe(1500.00);
    expect($quiniela->resultado)->toBe('ganadora');
    expect((float) $quiniela->monto_premio)->toBe(52500.00);
    expect($quiniela->detallesQuiniela()->count())->toBe(1);

    $quini6 = $user->jugadas()->whereHas('detallesQuini6')->first();
    expect($quini6)->not->toBeNull();
    expect((float) $quini6->monto_total)->toBe(1200.00);
    expect($quini6->resultado)->toBe('no_ganadora');

    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
});

it('rechaza archivos no permitidos en la importacion', function () {
    $tempFile = UploadedFile::fake()->create('documento.txt', 100, 'text/plain');

    $response = $this->actingAs($this->admin)
        ->post(route('admin.users.importar'), [
            'archivo' => $tempFile,
        ]);

    $response->assertSessionHasErrors(['archivo']);
});
