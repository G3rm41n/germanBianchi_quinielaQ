<?php

namespace Database\Seeders;

use App\Models\PrecioHistorico;
use Illuminate\Database\Seeder;

class PreciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $precios = [
            ['tipo_juego' => 'quini6', 'valor' => 1200.00],
            ['tipo_juego' => 'lotoplus', 'valor' => 1500.00],
            ['tipo_juego' => 'loto5', 'valor' => 800.00],
            ['tipo_juego' => 'poceada', 'valor' => 1000.00],
        ];

        foreach ($precios as $precio) {
            PrecioHistorico::firstOrCreate(
                ['tipo_juego' => $precio['tipo_juego'], 'fecha_fin' => null],
                [
                    'valor' => $precio['valor'],
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => null,
                ]
            );
        }
    }
}
