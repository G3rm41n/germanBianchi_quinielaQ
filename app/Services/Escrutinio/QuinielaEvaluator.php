<?php

namespace App\Services\Escrutinio;

use App\Models\Jugada;
use App\Models\Sorteo;

class QuinielaEvaluator
{
    /**
     * Evalúa una jugada de Quiniela contra el sorteo.
     *
     * @return array{
     *     ganadora: bool,
     *     monto_premio: float,
     *     detalles: array<int, array<string, mixed>>
     * }
     */
    public function evaluar(Jugada $jugada, Sorteo $sorteo): array
    {
        $extractoNormalizado = $this->normalizarExtracto($sorteo->extracto);
        $montoPremioTotal = 0.0;
        $detallesEvaluados = [];

        foreach ($jugada->detallesQuiniela as $detalle) {
            // Verificar coincidencia de jurisdicción y turno
            if ($detalle->jurisdiccion !== $sorteo->jurisdiccion || $detalle->turno !== $sorteo->turno) {
                continue;
            }

            $posicionApostada = (int) $detalle->posicion;
            $numeroApostado = str_pad((string) $detalle->numero, strlen((string) $detalle->numero), '0', STR_PAD_LEFT);
            $cifras = strlen($numeroApostado);
            $importe = (float) $detalle->importe;

            // Multiplicador base oficial a la cabeza según la cantidad de cifras
            $multiplicadorCabeza = match ($cifras) {
                1 => 7,
                2 => 70,
                3 => 500,
                4 => 3500,
                default => 0,
            };

            // Filtrar posiciones del extracto a evaluar (de 1 a $posicionApostada)
            $posicionesAcertadas = [];
            for ($pos = 1; $pos <= $posicionApostada; $pos++) {
                if (! isset($extractoNormalizado[$pos])) {
                    continue;
                }

                $numeroSorteado = str_pad((string) $extractoNormalizado[$pos], 4, '0', STR_PAD_LEFT);
                $terminacion = substr($numeroSorteado, -$cifras);

                if ($terminacion === $numeroApostado) {
                    $posicionesAcertadas[] = [
                        'posicion' => $pos,
                        'numero_sorteado' => $numeroSorteado,
                    ];
                }
            }

            $repeticiones = count($posicionesAcertadas);
            $premioDetalle = 0.0;

            if ($repeticiones > 0 && $posicionApostada > 0) {
                if ($posicionApostada === 1) {
                    $premioDetalle = $importe * $multiplicadorCabeza;
                } else {
                    $premioDetalle = $importe * ($multiplicadorCabeza / $posicionApostada) * $repeticiones;
                }
            }

            $montoPremioTotal += $premioDetalle;

            $detallesEvaluados[] = [
                'detalle_id' => $detalle->id,
                'numero' => $numeroApostado,
                'cifras' => $cifras,
                'posicion_apostada' => $posicionApostada,
                'importe' => $importe,
                'aciertos' => $repeticiones,
                'posiciones_acertadas' => $posicionesAcertadas,
                'premio' => round($premioDetalle, 2),
            ];
        }

        return [
            'ganadora' => $montoPremioTotal > 0,
            'monto_premio' => round($montoPremioTotal, 2),
            'detalles' => $detallesEvaluados,
        ];
    }

    /**
     * Normaliza el extracto en un mapa [ 1 => '1234', 2 => '5678', ..., 20 => '9012' ].
     *
     * @param  array<mixed>  $extracto
     * @return array<int, string>
     */
    public function normalizarExtracto(array $extracto): array
    {
        $normalizado = [];

        foreach ($extracto as $key => $valor) {
            $pos = is_numeric($key) ? ((int) $key < 20 && isset($extracto[0]) ? (int) $key + 1 : (int) $key) : (int) preg_replace('/\D/', '', (string) $key);
            if ($pos >= 1 && $pos <= 20) {
                $normalizado[$pos] = str_pad((string) $valor, 4, '0', STR_PAD_LEFT);
            }
        }

        return $normalizado;
    }
}
