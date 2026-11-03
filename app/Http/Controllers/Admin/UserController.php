<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Traemos los clientes (rol = cliente) ordenados por fecha de registro con filtros aplicados
        $users = User::where('rol', 'cliente')
            ->filtrarClientes($request->all())
            ->withCount('jugadas')
            ->withSum('jugadas', 'monto_total')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $filtrosActivos = collect(['buscar', 'min_jugadas', 'min_volumen', 'inicial_nombre', 'inicial_apellido'])
                ->filter(fn ($key) => $request->filled($key))
                ->count();

            return response()->json([
                'html' => view('admin.users._table', compact('users', 'filtrosActivos'))->render(),
                'total' => $users->total(),
                'export_url' => route('admin.users.exportar', $request->query()),
                'filtros_activos' => $filtrosActivos,
            ]);
        }

        return view('admin.users.index', compact('users'));
    }

    public function show(Request $request, User $user, BalanceService $balanceService)
    {
        // Traemos las jugadas del usuario específico con filtros aplicados
        $jugadas = $user->jugadas()
            ->with(['detallesQuiniela', 'detallesQuini6', 'detallesLotoPlus', 'detallesLoto5', 'detallesPoceada'])
            ->filtrar($request->all())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totales = $balanceService->obtenerTotales($user->id);
        $categorias = $balanceService->obtenerDesgloseCategorias($user->id);

        return view('admin.users.show', compact('user', 'jugadas', 'totales', 'categorias'));
    }
}
