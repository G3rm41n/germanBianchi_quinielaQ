<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Traemos los clientes (rol = cliente) ordenados por fecha de registro
        $users = User::where('rol', 'cliente')
            ->withCount('jugadas')
            ->withSum('jugadas', 'monto_total')
            ->latest()
            ->paginate(20);

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
