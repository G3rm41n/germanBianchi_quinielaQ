<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Jugada;
use App\Models\PrecioHistorico;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Jugada::with(['user', 'detallesQuiniela', 'detallesQuini6', 'detallesLotoPlus', 'detallesLoto5', 'detallesPoceada'])->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $jugadas = $query->paginate(20)->withQueryString();

        $preciosVigentes = PrecioHistorico::vigente()->get();
        $preciosMap = $preciosVigentes->pluck('valor', 'tipo_juego')->toArray();

        $auditLogs = AuditLog::latest()->take(50)->get();

        return view('admin.dashboard', compact('jugadas', 'preciosMap', 'auditLogs'));
    }
}
