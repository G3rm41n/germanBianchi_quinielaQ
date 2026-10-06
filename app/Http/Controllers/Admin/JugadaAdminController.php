<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jugada;
use App\Mail\NuevaJugadaMail;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

    public function reenviarNotificacion(Jugada $jugada)
    {
        $admin = \App\Models\User::where('rol', 'admin')->first();

        if (!$admin || !$admin->email) {
            return back()->with('error', 'No se encontró un email de administrador para reenviar.');
        }

        try {
            Mail::to($admin->email)->send(new NuevaJugadaMail($jugada));
            $jugada->update(['estado' => 'pendiente']);
            
            AuditLog::create([
                'accion' => 'reenvio_smtp_exitoso',
                'jugada_id' => $jugada->id,
                'detalles' => json_encode(['email' => $admin->email])
            ]);

            return back()->with('success', 'Notificación reenviada exitosamente. El estado cambió a Pendiente.');
        } catch (\Exception $e) {
            AuditLog::create([
                'accion' => 'reenvio_smtp_failure',
                'jugada_id' => $jugada->id,
                'detalles' => json_encode(['error' => $e->getMessage()])
            ]);

            return back()->with('error', 'El reenvío volvió a fallar. Revisa los logs de auditoría.');
        }
    }

    public function limpiarBaseDatos()
    {
        // Solo limpia transacciones
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        DB::table('detalles_quiniela')->delete();
        DB::table('detalles_quini6')->delete();
        DB::table('detalles_lotoplus')->delete();
        DB::table('detalles_loto5')->delete();
        DB::table('detalles_quiniela_poceada')->delete();
        DB::table('jugadas')->delete();
        DB::table('audit_logs')->delete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        return back()->with('success', 'La base de datos de jugadas y logs ha sido limpiada.');
    }
}
