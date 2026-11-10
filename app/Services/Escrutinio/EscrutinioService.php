<?php

namespace App\Services\Escrutinio;

use App\Mail\NotificacionResultadoMail;
use App\Models\AuditLog;
use App\Models\Jugada;
use App\Models\Sorteo;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EscrutinioService
{
    public function __construct(
        public QuinielaEvaluator $quinielaEvaluator,
        public Quini6Evaluator $quini6Evaluator,
        public LotoPlusEvaluator $lotoPlusEvaluator,
        public Loto5Evaluator $loto5Evaluator,
        public PoceadaEvaluator $poceadaEvaluator,
    ) {}

    /**
     * Busca las jugadas elegibles pendientes para un sorteo determinado.
     *
     * @return Collection<int, Jugada>
     */
    public function buscarJugadasPendientes(Sorteo $sorteo): Collection
    {
        $query = Jugada::query()
            ->with(['user', 'detallesQuiniela', 'detallesQuini6', 'detallesLotoPlus', 'detallesLoto5', 'detallesPoceada'])
            ->whereDate('created_at', $sorteo->fecha_sorteo)
            ->where('resultado', 'pendiente_sorteo')
            ->where('estado', '!=', 'error_envio');

        match ($sorteo->juego) {
            'quiniela' => $query->whereHas('detallesQuiniela', function ($q) use ($sorteo) {
                $q->where('jurisdiccion', $sorteo->jurisdiccion)
                    ->where('turno', $sorteo->turno);
            }),
            'quini6' => $query->whereHas('detallesQuini6'),
            'lotoplus' => $query->whereHas('detallesLotoPlus'),
            'loto5' => $query->whereHas('detallesLoto5'),
            'poceada' => $query->whereHas('detallesPoceada'),
            default => null,
        };

        return $query->get();
    }

    /**
     * Evalúa una jugada individual según la modalidad del sorteo.
     *
     * @return array{
     *     ganadora: bool,
     *     monto_premio: float,
     *     detalles: array<int, array<string, mixed>>
     * }
     */
    public function evaluarJugada(Jugada $jugada, Sorteo $sorteo): array
    {
        return match ($sorteo->juego) {
            'quiniela' => $this->quinielaEvaluator->evaluar($jugada, $sorteo),
            'quini6' => $this->quini6Evaluator->evaluar($jugada, $sorteo),
            'lotoplus' => $this->lotoPlusEvaluator->evaluar($jugada, $sorteo),
            'loto5' => $this->loto5Evaluator->evaluar($jugada, $sorteo),
            'poceada' => $this->poceadaEvaluator->evaluar($jugada, $sorteo),
            default => ['ganadora' => false, 'monto_premio' => 0.0, 'detalles' => []],
        };
    }

    /**
     * Simula el escrutinio de un sorteo sin modificar la base de datos ni los saldos.
     *
     * @return array{
     *     total_boletas: int,
     *     total_ganadoras: int,
     *     total_no_ganadoras: int,
     *     total_premios: float,
     *     boletas: array<int, array{
     *         jugada: Jugada,
     *         ganadora: bool,
     *         monto_premio: float,
     *         detalles: array<int, array<string, mixed>>
     *     }>
     * }
     */
    public function simular(Sorteo $sorteo): array
    {
        $jugadas = $this->buscarJugadasPendientes($sorteo);

        $totalGanadoras = 0;
        $totalNoGanadoras = 0;
        $totalPremios = 0.0;
        $boletasEvaluadas = [];

        foreach ($jugadas as $jugada) {
            $resultado = $this->evaluarJugada($jugada, $sorteo);

            if ($resultado['ganadora']) {
                $totalGanadoras++;
                $totalPremios += $resultado['monto_premio'];
            } else {
                $totalNoGanadoras++;
            }

            $boletasEvaluadas[] = [
                'jugada' => $jugada,
                'ganadora' => $resultado['ganadora'],
                'monto_premio' => $resultado['monto_premio'],
                'detalles' => $resultado['detalles'],
            ];
        }

        return [
            'total_boletas' => $jugadas->count(),
            'total_ganadoras' => $totalGanadoras,
            'total_no_ganadoras' => $totalNoGanadoras,
            'total_premios' => round($totalPremios, 2),
            'boletas' => $boletasEvaluadas,
        ];
    }

    /**
     * Ejecuta el escrutinio definitivo, actualiza las jugadas y acredita saldos de forma atómica.
     *
     * @return array{
     *     total_boletas: int,
     *     total_ganadoras: int,
     *     total_premios: float
     * }
     */
    public function escrutar(Sorteo $sorteo, User $admin): array
    {
        return DB::transaction(function () use ($sorteo, $admin) {
            $simulacion = $this->simular($sorteo);

            foreach ($simulacion['boletas'] as $item) {
                /** @var Jugada $jugada */
                $jugada = $item['jugada'];
                $esGanadora = $item['ganadora'];
                $montoPremio = (float) $item['monto_premio'];

                $jugada->update([
                    'sorteo_id' => $sorteo->id,
                    'resultado' => $esGanadora ? 'ganadora' : 'no_ganadora',
                    'monto_premio' => $montoPremio,
                    'detalle_escrutinio' => $item['detalles'],
                ]);

                if ($esGanadora && $montoPremio > 0) {
                    $jugada->user->acreditarSaldo($montoPremio);
                }

                if ($jugada->user?->email) {
                    try {
                        Mail::to($jugada->user->email)->send(new NotificacionResultadoMail($jugada, 'resultado'));
                    } catch (\Throwable) {
                        // Ignorar fallo de envío SMTP en lote
                    }
                }
            }

            $sorteo->update([
                'estado' => 'escrutado',
                'total_boletas_evaluadas' => $simulacion['total_boletas'],
                'total_boletas_ganadoras' => $simulacion['total_ganadoras'],
                'total_premios_liquidados' => $simulacion['total_premios'],
                'escrutado_en' => now(),
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'accion' => 'sorteo_escrutado',
                'descripcion' => "Sorteo #{$sorteo->id} ({$sorteo->nombre_juego}) escrutado exitosamente. {$simulacion['total_ganadoras']} ganadoras de {$simulacion['total_boletas']} evaluadas. Premios liquidados: \${$simulacion['total_premios']}.",
                'ip' => request()->ip() ?: '127.0.0.1',
            ]);

            return [
                'total_boletas' => $simulacion['total_boletas'],
                'total_ganadoras' => $simulacion['total_ganadoras'],
                'total_premios' => $simulacion['total_premios'],
            ];
        });
    }

    /**
     * Revierte un sorteo previamente escrutado: descuenta premios acreditados y restaura jugadas.
     */
    public function revertir(Sorteo $sorteo, User $admin): bool
    {
        if ($sorteo->estado !== 'escrutado') {
            return false;
        }

        return DB::transaction(function () use ($sorteo, $admin) {
            $jugadas = $sorteo->jugadas()->with('user')->get();

            foreach ($jugadas as $jugada) {
                if ($jugada->resultado === 'ganadora' && $jugada->monto_premio > 0) {
                    $jugada->user->decrement('saldo', $jugada->monto_premio);
                }

                $jugada->update([
                    'resultado' => 'pendiente_sorteo',
                    'monto_premio' => 0.0,
                    'sorteo_id' => null,
                    'detalle_escrutinio' => null,
                ]);
            }

            $sorteo->update([
                'estado' => 'anulado',
                'total_boletas_evaluadas' => 0,
                'total_boletas_ganadoras' => 0,
                'total_premios_liquidados' => 0.0,
                'escrutado_en' => null,
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'accion' => 'sorteo_revertido',
                'descripcion' => "Sorteo #{$sorteo->id} ({$sorteo->nombre_juego}) revertido. Se anularon premios y se restauraron {$jugadas->count()} boletas a pendiente de sorteo.",
                'ip' => request()->ip() ?: '127.0.0.1',
            ]);

            return true;
        });
    }
}
