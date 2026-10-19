<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NuevaJugadaMail;
use App\Models\AuditLog;
use App\Models\Jugada;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class JugadaAdminController extends Controller
{
    public function update(Request $request, Jugada $jugada)
    {
        $validated = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'procesado', 'error_envio'])],
        ]);

        $jugada->update($validated);

        return back()->with('success', 'Estado de la jugada actualizado correctamente.');
    }

    public function actualizarResultado(Request $request, Jugada $jugada)
    {
        $validated = $request->validate([
            'resultado' => ['required', Rule::in(['pendiente_sorteo', 'ganadora', 'no_ganadora'])],
            'monto_premio' => ['nullable', 'numeric', 'min:0'],
        ]);

        $montoPremio = $validated['resultado'] === 'ganadora' ? (float) ($validated['monto_premio'] ?? 0) : 0;

        $jugada->update([
            'resultado' => $validated['resultado'],
            'monto_premio' => $montoPremio,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'accion' => 'resultado_actualizado',
            'descripcion' => "Jugada #{$jugada->id} actualizada a resultado: {$validated['resultado']}, premio: \${$montoPremio}",
            'ip' => request()->ip() ?: '127.0.0.1',
        ]);

        return back()->with('success', 'Resultado y premio de la jugada actualizados correctamente.');
    }

    public function reenviarNotificacion(Jugada $jugada)
    {
        $admin = User::where('rol', 'admin')->first();

        if (! $admin || ! $admin->email) {
            return back()->with('error', 'No se encontró un email de administrador para reenviar.');
        }

        try {
            Mail::to($admin->email)->send(new NuevaJugadaMail($jugada));
            $jugada->update(['estado' => 'pendiente']);

            AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'reenvio_smtp_exitoso',
                'descripcion' => "Reenvío SMTP exitoso para la jugada #{$jugada->id} hacia {$admin->email}",
                'ip' => request()->ip() ?: '127.0.0.1',
            ]);

            return back()->with('success', 'Notificación reenviada exitosamente. El estado cambió a Pendiente.');
        } catch (\Exception $e) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'reenvio_smtp_failure',
                'descripcion' => "Reenvío SMTP fallido para la jugada #{$jugada->id}. Error: ".$e->getMessage(),
                'ip' => request()->ip() ?: '127.0.0.1',
            ]);

            return back()->with('error', 'El reenvío volvió a fallar. Revisa los logs de auditoría.');
        }
    }

    public function limpiarBaseDatos()
    {
        // Solo limpia transacciones
        Schema::disableForeignKeyConstraints();
        DB::table('detalles_quiniela')->delete();
        DB::table('detalles_quini6')->delete();
        DB::table('detalles_lotoplus')->delete();
        DB::table('detalles_loto5')->delete();
        DB::table('detalles_quiniela_poceada')->delete();
        DB::table('jugadas')->delete();
        DB::table('audit_logs')->delete();
        Schema::enableForeignKeyConstraints();

        return back()->with('success', 'La base de datos de jugadas y logs ha sido limpiada.');
    }
}
