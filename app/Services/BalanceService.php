<?php

namespace App\Services;

use App\Models\Jugada;

class BalanceService
{
    /**
     * Obtiene los totales consolidados financieros.
     *
     * @param  int|null  $userId  ID del usuario (null para toda la agencia)
     * @return array{
     *     total_invertido: float,
     *     total_ganancias: float,
     *     total_perdidas: float,
     *     balance_neto: float,
     *     total_jugadas: int,
     *     jugadas_ganadoras: int,
     *     jugadas_no_ganadoras: int,
     *     jugadas_pendientes: int
     * }
     */
    public function obtenerTotales(?int $userId = null): array
    {
        $query = Jugada::query();

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $jugadas = $query->get();

        $totalInvertido = (float) $jugadas->sum('monto_total');
        $totalGanancias = (float) $jugadas->where('resultado', 'ganadora')->sum('monto_premio');
        $totalPerdidas = (float) $jugadas->where('resultado', 'no_ganadora')->sum('monto_total');
        $balanceNeto = $totalGanancias - $totalInvertido;

        return [
            'total_invertido' => $totalInvertido,
            'total_ganancias' => $totalGanancias,
            'total_perdidas' => $totalPerdidas,
            'balance_neto' => $balanceNeto,
            'total_jugadas' => $jugadas->count(),
            'jugadas_ganadoras' => $jugadas->where('resultado', 'ganadora')->count(),
            'jugadas_no_ganadoras' => $jugadas->where('resultado', 'no_ganadora')->count(),
            'jugadas_pendientes' => $jugadas->where('resultado', 'pendiente_sorteo')->count(),
        ];
    }

    /**
     * Obtiene el desglose agrupado por cada una de las 5 categorías de juego.
     *
     * @param  int|null  $userId  ID del usuario (null para toda la agencia)
     * @return array<string, array{
     *     nombre: string,
     *     icono: string,
     *     cantidad_jugadas: int,
     *     total_invertido: float,
     *     total_ganancias: float,
     *     total_perdidas: float,
     *     balance_neto: float
     * }>
     */
    public function obtenerDesgloseCategorias(?int $userId = null): array
    {
        $query = Jugada::with([
            'detallesQuiniela',
            'detallesQuini6',
            'detallesLotoPlus',
            'detallesLoto5',
            'detallesPoceada',
        ]);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $jugadas = $query->get();

        $categorias = [
            'quiniela' => [
                'nombre' => 'Quiniela Tradicional',
                'icono' => '🎰',
                'cantidad_jugadas' => 0,
                'total_invertido' => 0.0,
                'total_ganancias' => 0.0,
                'total_perdidas' => 0.0,
                'balance_neto' => 0.0,
            ],
            'quini6' => [
                'nombre' => 'Quini 6',
                'icono' => '6️⃣',
                'cantidad_jugadas' => 0,
                'total_invertido' => 0.0,
                'total_ganancias' => 0.0,
                'total_perdidas' => 0.0,
                'balance_neto' => 0.0,
            ],
            'lotoplus' => [
                'nombre' => 'Loto Plus',
                'icono' => '➕',
                'cantidad_jugadas' => 0,
                'total_invertido' => 0.0,
                'total_ganancias' => 0.0,
                'total_perdidas' => 0.0,
                'balance_neto' => 0.0,
            ],
            'loto5' => [
                'nombre' => 'Loto 5',
                'icono' => '5️⃣',
                'cantidad_jugadas' => 0,
                'total_invertido' => 0.0,
                'total_ganancias' => 0.0,
                'total_perdidas' => 0.0,
                'balance_neto' => 0.0,
            ],
            'poceada' => [
                'nombre' => 'Quiniela Poceada',
                'icono' => '🏆',
                'cantidad_jugadas' => 0,
                'total_invertido' => 0.0,
                'total_ganancias' => 0.0,
                'total_perdidas' => 0.0,
                'balance_neto' => 0.0,
            ],
        ];

        foreach ($jugadas as $jugada) {
            $modalidadesPresentes = [];

            if ($jugada->detallesQuiniela->isNotEmpty()) {
                $modalidadesPresentes['quiniela'] = true;
            }

            if ($jugada->detallesQuini6->isNotEmpty()) {
                $modalidadesPresentes['quini6'] = true;
            }

            if ($jugada->detallesLotoPlus->isNotEmpty()) {
                $modalidadesPresentes['lotoplus'] = true;
            }

            if ($jugada->detallesLoto5->isNotEmpty()) {
                $modalidadesPresentes['loto5'] = true;
            }

            if ($jugada->detallesPoceada->isNotEmpty()) {
                $modalidadesPresentes['poceada'] = true;
            }

            $totalModalidades = count($modalidadesPresentes);
            if ($totalModalidades === 0) {
                continue;
            }

            $costoPorModalidad = (float) $jugada->monto_total / $totalModalidades;
            $premioPorModalidad = (float) $jugada->monto_premio / $totalModalidades;

            foreach (array_keys($modalidadesPresentes) as $modKey) {
                $categorias[$modKey]['cantidad_jugadas']++;
                $categorias[$modKey]['total_invertido'] += $costoPorModalidad;

                if ($jugada->resultado === 'ganadora') {
                    $categorias[$modKey]['total_ganancias'] += $premioPorModalidad;
                } elseif ($jugada->resultado === 'no_ganadora') {
                    $categorias[$modKey]['total_perdidas'] += $costoPorModalidad;
                }
            }
        }

        foreach ($categorias as $key => $datos) {
            $categorias[$key]['balance_neto'] = $datos['total_ganancias'] - $datos['total_invertido'];
        }

        return $categorias;
    }
}
