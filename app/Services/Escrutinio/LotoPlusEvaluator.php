<?php

namespace App\Services\Escrutinio;

use App\Models\Jugada;
use App\Models\Sorteo;

class LotoPlusEvaluator
{
    /**
     * Evalúa una jugada de Loto Plus contra el sorteo.
     *
     * @return array{
     *     ganadora: bool,
     *     monto_premio: float,
     *     detalles: array<int, array<string, mixed>>
     * }
     */
    public function evaluar(Jugada $jugada, Sorteo $sorteo): array
    {
        $extracto = $sorteo->extracto;
        $sorteados = array_map('intval', (array) ($extracto['numeros'] ?? []));
        $numeroPlusSorteado = isset($extracto['numero_plus']) ? (int) $extracto['numero_plus'] : null;
        $tablaPremios = $sorteo->tabla_premios ?? [];
        $montoPremioTotal = 0.0;
        $detallesEvaluados = [];

        foreach ($jugada->detallesLotoPlus as $detalle) {
            $numerosJugados = array_map('intval', (array) $detalle->numeros);
            $acertados = array_values(array_intersect($numerosJugados, $sorteados));
            $cantidadAciertos = count($acertados);
            $acertoPlus = ($numeroPlusSorteado !== null && (int) $detalle->numero_plus === $numeroPlusSorteado);

            $premio = 0.0;
            if ($cantidadAciertos >= 4) {
                $premioBase = (float) ($tablaPremios[(string) $cantidadAciertos] ?? $tablaPremios[$cantidadAciertos] ?? 0.0);
                $premio = $premioBase;

                // Si acierta el número Plus y se configuró un bonus/premio plus
                if ($acertoPlus && ! empty($tablaPremios['plus_bonus'])) {
                    $premio += (float) $tablaPremios['plus_bonus'];
                }
            } elseif ($acertoPlus && ! empty($tablaPremios['solo_plus'])) {
                // Premio si solo acertó el número Plus
                $premio = (float) $tablaPremios['solo_plus'];
            }

            $montoPremioTotal += $premio;

            $detallesEvaluados[] = [
                'detalle_id' => $detalle->id,
                'numeros_jugados' => $numerosJugados,
                'numeros_sorteados' => $sorteados,
                'aciertos' => $cantidadAciertos,
                'numeros_acertados' => $acertados,
                'numero_plus_jugado' => (int) $detalle->numero_plus,
                'numero_plus_sorteado' => $numeroPlusSorteado,
                'acerto_plus' => $acertoPlus,
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
