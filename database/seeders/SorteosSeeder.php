<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\DetalleQuini6;
use App\Models\DetalleQuiniela;
use App\Models\Jugada;
use App\Models\Sorteo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SorteosSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('rol', 'admin')->first();
        if (! $admin) {
            $admin = User::factory()->admin()->create();
        }

        $clientes = User::where('rol', 'cliente')->get();
        if ($clientes->isEmpty()) {
            $clientes = User::factory()->cliente()->count(3)->create();
        }

        $cliente1 = $clientes->first();
        $cliente2 = $clientes->count() > 1 ? $clientes->get(1) : $cliente1;

        // =========================================================================
        // 1. SORTEOS PASADOS YA ESCRUTADOS (HISTÓRICO)
        // =========================================================================

        // Sorteo 1: Quiniela Nación Matutina de ayer (Escrutado)
        $extractoAyer = [
            1 => '4825', 2 => '1934', 3 => '5678', 4 => '9012', 5 => '3456',
            6 => '7890', 7 => '2345', 8 => '6789', 9 => '0123', 10 => '4567',
            11 => '8901', 12 => '2345', 13 => '6789', 14 => '0123', 15 => '4567',
            16 => '8901', 17 => '2345', 18 => '6789', 19 => '0123', 20 => '4567',
        ];

        $sorteoQuinielaAyer = Sorteo::create([
            'juego' => 'quiniela',
            'fecha_sorteo' => today()->subDay(),
            'numero_sorteo' => 'Sorteo Oficial 4589',
            'jurisdiccion' => 'nacion',
            'turno' => 'matutina',
            'extracto' => $extractoAyer,
            'estado' => 'escrutado',
            'total_boletas_evaluadas' => 4,
            'total_boletas_ganadoras' => 1,
            'total_premios_liquidados' => 7000.00,
            'created_by' => $admin->id,
            'escrutado_en' => today()->subDay()->setHour(14),
        ]);

        // Sorteo 2: Quini 6 Concurso #3150 (Escrutado)
        $sorteoQuini6 = Sorteo::create([
            'juego' => 'quini6',
            'fecha_sorteo' => today()->subDays(3),
            'numero_sorteo' => 'Concurso 3150',
            'extracto' => ['numeros' => [4, 11, 18, 25, 33, 40]],
            'tabla_premios' => [
                '6' => 60000000,
                '5' => 200000,
                '4' => 8000,
            ],
            'estado' => 'escrutado',
            'total_boletas_evaluadas' => 3,
            'total_boletas_ganadoras' => 1,
            'total_premios_liquidados' => 8000.00,
            'created_by' => $admin->id,
            'escrutado_en' => today()->subDays(3)->setHour(22),
        ]);

        // Sorteo 3: Loto 5 de hace 5 días (Escrutado)
        Sorteo::create([
            'juego' => 'loto5',
            'fecha_sorteo' => today()->subDays(5),
            'numero_sorteo' => 'Concurso 1089',
            'extracto' => ['numeros' => [2, 14, 19, 27, 35]],
            'tabla_premios' => [
                '5' => 1500000,
                '4' => 45000,
                '3' => 3000,
            ],
            'estado' => 'escrutado',
            'total_boletas_evaluadas' => 2,
            'total_boletas_ganadoras' => 0,
            'total_premios_liquidados' => 0.00,
            'created_by' => $admin->id,
            'escrutado_en' => today()->subDays(5)->setHour(21),
        ]);

        // =========================================================================
        // 2. JUGADAS DE HOY PREPARADAS PARA PROBAR (PENDIENTES DE DESPACHO Y SORTEO)
        // =========================================================================

        // Boleta 1: Quiniela Nación Matutina de hoy -> Acertará el '42' a la cabeza
        $jugada1 = Jugada::create([
            'user_id' => $cliente1->id,
            'monto_total' => 1000.00,
            'estado' => 'pendiente', // Para probar marcar procesada en lote
            'resultado' => 'pendiente_sorteo',
            'monto_premio' => 0.00,
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => today()->setHour(9)->setMinute(15),
            'updated_at' => today()->setHour(9)->setMinute(15),
        ]);
        DetalleQuiniela::create([
            'jugada_id' => $jugada1->id,
            'numero' => '42',
            'posicion' => 1, // A la cabeza
            'jurisdiccion' => 'nacion',
            'turno' => 'matutina',
            'importe' => 500.00,
        ]);
        DetalleQuiniela::create([
            'jugada_id' => $jugada1->id,
            'numero' => '42',
            'posicion' => 5, // A los 5
            'jurisdiccion' => 'nacion',
            'turno' => 'matutina',
            'importe' => 500.00,
        ]);

        // Boleta 2: Quiniela Nación Matutina de hoy -> Número '89' (No acertará)
        $jugada2 = Jugada::create([
            'user_id' => $cliente2->id,
            'monto_total' => 500.00,
            'estado' => 'pendiente',
            'resultado' => 'pendiente_sorteo',
            'monto_premio' => 0.00,
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => today()->setHour(10)->setMinute(5),
            'updated_at' => today()->setHour(10)->setMinute(5),
        ]);
        DetalleQuiniela::create([
            'jugada_id' => $jugada2->id,
            'numero' => '89',
            'posicion' => 1,
            'jurisdiccion' => 'nacion',
            'turno' => 'matutina',
            'importe' => 500.00,
        ]);

        // Boleta 3: Quini 6 de hoy -> 4 aciertos [3, 15, 22, 28, 35, 41]
        $jugada3 = Jugada::create([
            'user_id' => $cliente1->id,
            'monto_total' => 1200.00,
            'estado' => 'pendiente',
            'resultado' => 'pendiente_sorteo',
            'monto_premio' => 0.00,
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => today()->setHour(11)->setMinute(20),
            'updated_at' => today()->setHour(11)->setMinute(20),
        ]);
        DetalleQuini6::create([
            'jugada_id' => $jugada3->id,
            'numeros' => [3, 15, 22, 28, 35, 41],
        ]);

        // Boleta 4: Quiniela Provincia Nocturna de hoy -> Pendiente
        $jugada4 = Jugada::create([
            'user_id' => $cliente2->id,
            'monto_total' => 1500.00,
            'estado' => 'pendiente',
            'resultado' => 'pendiente_sorteo',
            'monto_premio' => 0.00,
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => today()->setHour(11)->setMinute(45),
            'updated_at' => today()->setHour(11)->setMinute(45),
        ]);
        DetalleQuiniela::create([
            'jugada_id' => $jugada4->id,
            'numero' => '7321',
            'posicion' => 10,
            'jurisdiccion' => 'provincia',
            'turno' => 'nocturna',
            'importe' => 1500.00,
        ]);

        // =========================================================================
        // 3. SORTEO PENDIENTE DE HOY (LISTO PARA QUE EL ADMIN LO SIMULE Y ESCRUTE)
        // =========================================================================

        // Sorteo de Quiniela Nación Matutina de HOY con el '42' a la cabeza
        $extractoHoy = [
            1 => '8742', // ¡Termina en 42! Acierto para Boleta 1
            2 => '1142', // También aparece en pos 2 (suma repetición para 'a los 5')
            3 => '5520',
            4 => '9831',
            5 => '4305',
            6 => '7119',
            7 => '2248',
            8 => '6013',
            9 => '0937',
            10 => '4482',
            11 => '8190',
            12 => '2571',
            13 => '6344',
            14 => '0218',
            15 => '4965',
            16 => '8723',
            17 => '2019',
            18 => '6548',
            19 => '0312',
            20 => '4780',
        ];

        Sorteo::create([
            'juego' => 'quiniela',
            'fecha_sorteo' => today(),
            'numero_sorteo' => 'Sorteo Oficial 4590',
            'jurisdiccion' => 'nacion',
            'turno' => 'matutina',
            'extracto' => $extractoHoy,
            'estado' => 'pendiente', // ⏳ ¡Listo para probar escrutinio en vivo!
            'created_by' => $admin->id,
        ]);

        // Sorteo de Quini 6 de HOY pendiente con tabla de premios
        Sorteo::create([
            'juego' => 'quini6',
            'fecha_sorteo' => today(),
            'numero_sorteo' => 'Concurso 3151',
            'extracto' => ['numeros' => [3, 15, 22, 28, 30, 44]], // Comparte 4 números con Boleta 3
            'tabla_premios' => [
                '6' => 80000000,
                '5' => 250000,
                '4' => 10000,
            ],
            'estado' => 'pendiente', // ⏳ ¡Listo para probar escrutinio en vivo!
            'created_by' => $admin->id,
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'accion' => 'seeder_sorteos_ejecutado',
            'descripcion' => 'Seeder de sorteos oficiales y boletas de prueba para escrutinio y cierre de caja ejecutado correctamente.',
            'ip' => '127.0.0.1',
        ]);
    }
}
