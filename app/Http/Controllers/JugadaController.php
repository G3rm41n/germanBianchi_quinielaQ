<?php

namespace App\Http\Controllers;

use App\Http\Requests\JugadaRequest;
use App\Mail\NuevaJugadaMail;
use App\Models\AuditLog;
use App\Models\Jugada;
use App\Models\PrecioHistorico;
use App\Models\User;
use App\Services\BalanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JugadaController extends Controller
{
    public function index(BalanceService $balanceService): View
    {
        $userId = (int) Auth::id();

        $jugadas = Auth::user()
            ->jugadas()
            ->with(['detallesQuiniela', 'detallesQuini6', 'detallesLotoPlus', 'detallesLoto5', 'detallesPoceada'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $totales = $balanceService->obtenerTotales($userId);
        $categorias = $balanceService->obtenerDesgloseCategorias($userId);

        return view('jugadas.index', compact('jugadas', 'totales', 'categorias'));
    }

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

    public function store(JugadaRequest $request): RedirectResponse
    {
        $user = Auth::user();

        // 1. Verificar idempotencia
        $jugadaExistente = Jugada::where('user_id', $user->id)
            ->where('idempotency_token', $request->idempotency_token)
            ->first();

        if ($jugadaExistente) {
            return redirect()->route('jugadas.index')
                ->with('success', '¡Tu boleta ya fue registrada con éxito!');
        }

        $cart = $request->input('cart');

        // 2. Procesar y guardar en transacción
        $jugada = null;
        DB::transaction(function () use ($request, $user, $cart, &$jugada) {
            $montoTotalBoleta = collect($cart)->sum('costo');

            $jugada = Jugada::create([
                'user_id' => $user->id,
                'monto_total' => $montoTotalBoleta,
                'estado' => 'pendiente',
                'idempotency_token' => $request->idempotency_token,
            ]);

            foreach ($cart as $item) {
                if ($item['modalidad'] === 'quiniela') {
                    foreach ($item['posiciones'] as $pos) {
                        $jugada->detallesQuiniela()->create([
                            'numero' => $item['numero'],
                            'posicion' => $pos,
                            'jurisdiccion' => $item['jurisdiccion'],
                            'importe' => $item['importe'],
                        ]);
                    }
                } elseif ($item['modalidad'] === 'quini6') {
                    $jugada->detallesQuini6()->create([
                        'numeros' => $item['numeros'],
                    ]);
                } elseif ($item['modalidad'] === 'lotoplus') {
                    $jugada->detallesLotoPlus()->create([
                        'numeros' => $item['numeros'],
                        'numero_plus' => $item['numero_plus'],
                    ]);
                } elseif ($item['modalidad'] === 'loto5') {
                    $jugada->detallesLoto5()->create([
                        'numeros' => $item['numeros'],
                    ]);
                } elseif ($item['modalidad'] === 'poceada') {
                    $jugada->detallesPoceada()->create([
                        'numeros' => $item['numeros'],
                    ]);
                }
            }
        });

        // 3. Fase 5: Notificación SMTP al administrador
        try {
            $admin = User::where('rol', 'admin')->first();
            if ($admin && $admin->email) {
                Mail::to($admin->email)->send(new NuevaJugadaMail($jugada));
            }
        } catch (\Exception $e) {
            $jugada->update(['estado' => 'error_envio']);

            AuditLog::create([
                'user_id' => $user->id,
                'accion' => 'smtp_failure',
                'descripcion' => 'Fallo al enviar notificación SMTP de la jugada '.$jugada->id.'. Error: '.$e->getMessage(),
                'ip' => request()->ip(),
            ]);
        }

        return redirect()->route('jugadas.index')
            ->with('success', '¡Tu boleta fue registrada exitosamente!');
    }

    public function downloadPdf(Jugada $jugada)
    {
        if ($jugada->user_id !== Auth::id() && Auth::user()->rol !== 'admin') {
            abort(403);
        }

        $jugada->load(['user', 'detallesQuiniela', 'detallesQuini6', 'detallesLotoPlus', 'detallesLoto5', 'detallesPoceada']);

        $usuario = $jugada->user;
        $logoPath = public_path('images/logo.jpg');

        $pdf = Pdf::loadView('pdf.comprobante', compact('jugada', 'usuario', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("comprobante_{$jugada->id}_{$jugada->created_at->format('YmdHis')}.pdf");
    }
}
