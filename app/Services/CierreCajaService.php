<?php

namespace App\Services;

use App\Models\Jugada;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CierreCajaService
{
    /**
     * Obtiene el arqueo de caja completo para una fecha determinada.
     *
     * @param  string|null  $fecha  Fecha en formato Y-m-d (por defecto hoy)
     * @return array{
     *     fecha: Carbon,
     *     total_recaudado: float,
     *     total_premios: float,
     *     balance_neto: float,
     *     total_boletas: int,
     *     boletas_procesadas: int,
     *     boletas_pendientes: int,
     *     boletas_ganadoras: int,
     *     boletas_no_ganadoras: int,
     *     desglose_juegos: array<string, array{
     *         nombre: string,
     *         cantidad: int,
     *         recaudado: float,
     *         premios: float,
     *         balance: float
     *     }>,
     *     jugadas: Collection<int, Jugada>
     * }
     */
    public function obtenerArqueo(?string $fecha = null): array
    {
        $fechaCarbon = $fecha ? Carbon::parse($fecha) : today();

        $jugadas = Jugada::query()
            ->with(['user', 'detallesQuiniela', 'detallesQuini6', 'detallesLotoPlus', 'detallesLoto5', 'detallesPoceada'])
            ->whereDate('created_at', $fechaCarbon)
            ->latest('id')
            ->get();

        $totalRecaudado = (float) $jugadas->sum('monto_total');
        $totalPremios = (float) $jugadas->where('resultado', 'ganadora')->sum('monto_premio');
        $balanceNeto = $totalRecaudado - $totalPremios;

        $desgloseJuegos = [
            'quiniela' => ['nombre' => 'Quiniela Tradicional', 'cantidad' => 0, 'recaudado' => 0.0, 'premios' => 0.0, 'balance' => 0.0],
            'quini6' => ['nombre' => 'Quini 6', 'cantidad' => 0, 'recaudado' => 0.0, 'premios' => 0.0, 'balance' => 0.0],
            'lotoplus' => ['nombre' => 'Loto Plus', 'cantidad' => 0, 'recaudado' => 0.0, 'premios' => 0.0, 'balance' => 0.0],
            'loto5' => ['nombre' => 'Loto 5', 'cantidad' => 0, 'recaudado' => 0.0, 'premios' => 0.0, 'balance' => 0.0],
            'poceada' => ['nombre' => 'Quiniela Poceada', 'cantidad' => 0, 'recaudado' => 0.0, 'premios' => 0.0, 'balance' => 0.0],
        ];

        foreach ($jugadas as $jugada) {
            $modalidades = [];
            if ($jugada->detallesQuiniela->isNotEmpty()) {
                $modalidades[] = 'quiniela';
            }
            if ($jugada->detallesQuini6->isNotEmpty()) {
                $modalidades[] = 'quini6';
            }
            if ($jugada->detallesLotoPlus->isNotEmpty()) {
                $modalidades[] = 'lotoplus';
            }
            if ($jugada->detallesLoto5->isNotEmpty()) {
                $modalidades[] = 'loto5';
            }
            if ($jugada->detallesPoceada->isNotEmpty()) {
                $modalidades[] = 'poceada';
            }

            $countMods = count($modalidades);
            if ($countMods === 0) {
                continue;
            }

            $montoPorMod = (float) $jugada->monto_total / $countMods;
            $premioPorMod = (float) $jugada->monto_premio / $countMods;

            foreach ($modalidades as $mod) {
                $desgloseJuegos[$mod]['cantidad']++;
                $desgloseJuegos[$mod]['recaudado'] += $montoPorMod;
                if ($jugada->resultado === 'ganadora') {
                    $desgloseJuegos[$mod]['premios'] += $premioPorMod;
                }
            }
        }

        foreach ($desgloseJuegos as $key => $d) {
            $desgloseJuegos[$key]['balance'] = $d['recaudado'] - $d['premios'];
        }

        return [
            'fecha' => $fechaCarbon,
            'total_recaudado' => round($totalRecaudado, 2),
            'total_premios' => round($totalPremios, 2),
            'balance_neto' => round($balanceNeto, 2),
            'total_boletas' => $jugadas->count(),
            'boletas_procesadas' => $jugadas->where('estado', 'procesado')->count(),
            'boletas_pendientes' => $jugadas->where('estado', 'pendiente')->count(),
            'boletas_ganadoras' => $jugadas->where('resultado', 'ganadora')->count(),
            'boletas_no_ganadoras' => $jugadas->where('resultado', 'no_ganadora')->count(),
            'desglose_juegos' => $desgloseJuegos,
            'jugadas' => $jugadas,
        ];
    }
}
