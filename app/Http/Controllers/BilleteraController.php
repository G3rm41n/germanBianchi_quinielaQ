<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BilleteraController extends Controller
{
    /**
     * Recarga saldo simulado a la cuenta del usuario.
     */
    public function recargar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monto' => ['required', 'numeric', 'min:100', 'max:1000000'],
        ]);

        $user = Auth::user();
        $monto = (float) $validated['monto'];

        $user->acreditarSaldo($monto);

        AuditLog::create([
            'user_id' => $user->id,
            'accion' => 'recarga_saldo',
            'descripcion' => "Recarga de saldo simulada por \${$monto}. Saldo resultante: \${$user->fresh()->saldo}",
            'ip' => $request->ip() ?: '127.0.0.1',
        ]);

        return back()->with('success', '¡Recarga exitosa! Se han acreditado $'.number_format($monto, 2, ',', '.').' a tu cuenta.');
    }
}
