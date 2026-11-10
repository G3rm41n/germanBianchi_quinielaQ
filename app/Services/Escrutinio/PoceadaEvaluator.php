<?php

namespace App\Services\Escrutinio;

use App\Models\Jugada;
use App\Models\Sorteo;

class PoceadaEvaluator
{
    /**
     * Evalúa una jugada de Poceada contra el sorteo.
     *
     * @return array{
     *     ganadora: bool,
     *     monto_premio: float,
     *     detalles: array<int, array<string, mixed>>
     * }
     */
    public function evaluar(Jugada $jugada, Sorteo $sorteo): array
    {
        $sorteados = array_map(function ($n) {
            return str_pad((string) (is_numeric($n) ? (int) $n : $n), 2, '0', STR_PAD_LEFT);
        }, (array) ($sorteo->extracto['numeros'] ?? $sorteo->extracto));

        $tablaPremios = $sorteo->tabla_premios ?? [];
        $montoPremioTotal = 0.0;
        $detallesEvaluados = [];

        foreach ($jugada->detallesPoceada as $detalle) {
            $numerosJugados = array_map(function ($n) {
                return str_pad((string) (is_numeric($n) ? (int) $n : $n), 2, '0', STR_PAD_LEFT);
            }, (array) $detalle->numeros);

            $acertados = array_values(array_intersect($numerosJugados, $sorteados));
            $cantidadAciertos = count($acertados);

            $premio = 0.0;
            if ($cantidadAciertos >= 6) {
                $premio = (float) ($tablaPremios[(string) $cantidadAciertos] ?? $tablaPremios[$cantidadAciertos] ?? 0.0);
            }

            $montoPremioTotal += $premio;

            $detallesEvaluados[] = [
                'detalle_id' => $detalle->id,
                'numeros_jugados' => $numerosJugados,
                'numeros_sorteados' => $sorteados,
                'aciertos' => $cantidadAciertos,
                'numeros_acertados' => $acertados,
                'premio' => round($premio, 2),
            ];
        }

        return [
            'ganadora' => $montoPremioTotal > 0,
            'monto_premio' => round($montoPremioTotal, 2),
            'detalles' => $detallesEvaluados,
        ];
    }
}
