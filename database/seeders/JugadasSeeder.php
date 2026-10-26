<?php

namespace Database\Seeders;

use App\Models\Jugada;
use App\Models\PrecioHistorico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class JugadasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = User::where('rol', 'cliente')->get();

        if ($clientes->isEmpty()) {
            $clientes = User::factory()->cliente()->count(3)->create();
        }

        $modalidadesDisponibles = ['quiniela', 'quini6', 'lotoplus', 'loto5', 'poceada'];
        $estadosDisponibles = ['procesado', 'procesado', 'procesado', 'pendiente', 'error_envio'];
        $resultadosDisponibles = ['ganadora', 'no_ganadora', 'no_ganadora', 'no_ganadora', 'pendiente_sorteo'];

        $precioQuini6 = PrecioHistorico::precioActual('quini6')?->valor ?? 1200;
        $precioLotoPlus = PrecioHistorico::precioActual('lotoplus')?->valor ?? 1500;
        $precioLoto5 = PrecioHistorico::precioActual('loto5')?->valor ?? 800;
        $precioPoceada = PrecioHistorico::precioActual('poceada')?->valor ?? 1000;

        for ($i = 1; $i <= 25; $i++) {
            $cliente = $clientes->random();
            $diasAtras = (int) (($i - 1) * 1.2); // Escalonado entre 0 y 30 días atrás
            $fecha = Carbon::now()->subDays($diasAtras)->subHours(rand(1, 18))->subMinutes(rand(1, 58));

            $estado = $estadosDisponibles[array_rand($estadosDisponibles)];
            $resultado = $resultadosDisponibles[array_rand($resultadosDisponibles)];

            $montoPremio = 0.0;
            if ($resultado === 'ganadora') {
                $premiosPosibles = [5000.0, 12500.0, 28000.0, 65000.0, 120000.0, 250000.0];
                $montoPremio = $premiosPosibles[array_rand($premiosPosibles)];
            }

            // Seleccionamos entre 1 y 2 modalidades para la boleta
            $mods = (array) array_rand(array_flip($modalidadesDisponibles), rand(1, 2));
            $montoTotal = 0.0;

            $jugada = new Jugada([
                'user_id' => $cliente->id,
                'monto_total' => 0,
                'monto_premio' => $montoPremio,
                'estado' => $estado,
                'resultado' => $resultado,
                'idempotency_token' => (string) Str::uuid(),
            ]);

            $jugada->created_at = $fecha;
            $jugada->updated_at = $fecha;
            $jugada->save();

            foreach ($mods as $mod) {
                if ($mod === 'quiniela') {
                    $posiciones = [1, 5, 10, 20];
                    $posElegida = $posiciones[array_rand($posiciones)];
                    $importe = 500.0;
                    $jurisdicciones = ['nacion', 'provincia', 'santa_fe', 'cordoba'];

                    for ($p = 1; $p <= $posElegida; $p++) {
                        $jugada->detallesQuiniela()->create([
                            'numero' => (string) rand(10, 9999),
                            'posicion' => $p,
                            'jurisdiccion' => $jurisdicciones[array_rand($jurisdicciones)],
                            'importe' => $importe,
                        ]);
                    }
                    $montoTotal += ($posElegida * $importe);
                } elseif ($mod === 'quini6') {
                    $numeros = collect(range(0, 45))->random(6)->sort()->values()->toArray();
                    $jugada->detallesQuini6()->create([
                        'numeros' => $numeros,
                    ]);
                    $montoTotal += $precioQuini6;
                } elseif ($mod === 'lotoplus') {
                    $numeros = collect(range(0, 45))->random(6)->sort()->values()->toArray();
                    $jugada->detallesLotoPlus()->create([
                        'numeros' => $numeros,
                        'numero_plus' => rand(0, 9),
                    ]);
                    $montoTotal += $precioLotoPlus;
                } elseif ($mod === 'loto5') {
                    $numeros = collect(range(0, 36))->random(5)->sort()->values()->toArray();
                    $jugada->detallesLoto5()->create([
                        'numeros' => $numeros,
                    ]);
                    $montoTotal += $precioLoto5;
                } elseif ($mod === 'poceada') {
                    $numeros = collect(range(0, 99))->random(8)->sort()->values()->toArray();
                    $jugada->detallesPoceada()->create([
                        'numeros' => $numeros,
                    ]);
                    $montoTotal += $precioPoceada;
                }
            }

            $jugada->update(['monto_total' => $montoTotal]);
        }
    }
}
