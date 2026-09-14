<?php

namespace App\Http\Controllers;

use App\Http\Requests\JugadaRequest;
use App\Models\Jugada;
use App\Models\PrecioHistorico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JugadaController extends Controller
{
    /**
     * Mostrar historial de jugadas del usuario.
     */
    public function index(): View
    {
        $jugadas = Auth::user()
            ->jugadas()
            ->with(['detalleQuiniela', 'detalleQuini6', 'detalleLotoPlus', 'detalleLoto5', 'detallePoceada'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('jugadas.index', compact('jugadas'));
    }

    /**
     * Mostrar formulario de creación de jugada.
     */
    public function create(): View
    {
        $precios = [
            'quini6' => PrecioHistorico::precioActual('quini6')?->valor ?? 1200,
            'lotoplus' => PrecioHistorico::precioActual('lotoplus')?->valor ?? 1500,
            'loto5' => PrecioHistorico::precioActual('loto5')?->valor ?? 800,
            'poceada' => PrecioHistorico::precioActual('poceada')?->valor ?? 1000,
        ];

        $token = (string) Str::uuid();

        return view('jugadas.create', compact('precios', 'token'));
    }

    /**
     * Procesar y guardar la nueva jugada.
     */
    public function store(JugadaRequest $request): RedirectResponse
    {
        $user = Auth::user();

        // 1. Verificar idempotencia
        $jugadaExistente = Jugada::where('user_id', $user->id)
            ->where('idempotency_token', $request->idempotency_token)
            ->first();

        if ($jugadaExistente) {
            return redirect()->route('jugadas.index')
                ->with('success', '¡Tu jugada ya fue registrada con éxito!');
        }

        // 2. Determinar precio y guardar en transacción
        DB::transaction(function () use ($request, $user) {
            $modalidad = $request->modalidad;
            
            $montoTotal = 0;
            $precioUnitario = 0;

            if ($modalidad === 'quiniela') {
                $montoTotal = $request->importe;
                $precioUnitario = $request->importe; // En quiniela, el precio unitario es el importe jugado
            } else {
                $precioActivo = PrecioHistorico::precioActual($modalidad);
                $precioUnitario = $precioActivo ? $precioActivo->valor : 0;
                $montoTotal = $precioUnitario;
            }

            $jugada = Jugada::create([
                'user_id' => $user->id,
                'modalidad' => $modalidad,
                'monto_total' => $montoTotal,
                'precio_unitario_snapshot' => $precioUnitario,
                'estado' => 'pendiente',
                'idempotency_token' => $request->idempotency_token,
            ]);

            // 3. Guardar detalle según modalidad
            if ($modalidad === 'quiniela') {
                $jugada->detalleQuiniela()->create([
                    'numero' => $request->numero,
                    'posicion' => $request->posicion,
                    'jurisdiccion' => $request->jurisdiccion,
                    'importe' => $request->importe,
                ]);
            } elseif ($modalidad === 'quini6') {
                $jugada->detalleQuini6()->create([
                    'numeros' => $request->numeros_quini6,
                ]);
            } elseif ($modalidad === 'lotoplus') {
                $jugada->detalleLotoPlus()->create([
                    'numeros' => $request->numeros_lotoplus,
                    'numero_plus' => $request->numero_plus,
                ]);
            } elseif ($modalidad === 'loto5') {
                $jugada->detalleLoto5()->create([
                    'numeros' => $request->numeros_loto5,
                ]);
            } elseif ($modalidad === 'poceada') {
                $jugada->detallePoceada()->create([
                    'numeros' => $request->numeros_poceada,
                ]);
            }

            // [TODO Fase 5] Despachar NuevaJugadaMail a la cola...
        });

        return redirect()->route('jugadas.index')
            ->with('success', '¡Tu jugada fue registrada exitosamente!');
    }

    /**
     * Descargar comprobante en PDF (Fase 4).
     */
    public function downloadPdf(Jugada $jugada)
    {
        if ($jugada->user_id !== Auth::id()) {
            abort(403);
        }

        abort(501, 'La generación de PDF estará disponible en la Fase 4.');
    }
}
