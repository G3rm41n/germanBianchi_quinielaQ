<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Jugada;
use App\Models\PrecioHistorico;
use App\Services\BalanceService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, BalanceService $balanceService)
    {
        $jugadas = Jugada::with(['user', 'detallesQuiniela', 'detallesQuini6', 'detallesLotoPlus', 'detallesLoto5', 'detallesPoceada'])
            ->filtrar($request->all())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $preciosVigentes = PrecioHistorico::vigente()->get();
        $preciosMap = $preciosVigentes->pluck('valor', 'tipo_juego')->toArray();

        $auditLogs = AuditLog::latest()->take(50)->get();

        $totales = $balanceService->obtenerTotales();
        $categorias = $balanceService->obtenerDesgloseCategorias();

        return view('admin.dashboard', compact('jugadas', 'preciosMap', 'auditLogs', 'totales', 'categorias'));
    }
}
