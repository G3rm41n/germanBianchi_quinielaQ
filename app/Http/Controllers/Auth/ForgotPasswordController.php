<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class ForgotPasswordController extends Controller
{
    /**
     * Muestra el formulario para solicitar el enlace de recuperación de contraseña.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Muestra la pantalla de confirmación informando que el correo fue despachado.
     */
    public function sent(): View
    {
        return view('auth.password-sent');
    }

    /**
     * Procesa la solicitud y envía el correo con el enlace de restablecimiento.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Debes ingresar tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return redirect()->route('password.sent')->with('email_sent', $request->email);
            }

            $mensajeError = match ($status) {
                Password::RESET_THROTTLED => 'Ya enviamos un enlace de recuperación recientemente. Por favor, aguarda un minuto antes de volver a solicitar otro.',
                Password::INVALID_USER => 'No encontramos ningún usuario registrado con ese correo electrónico.',
                default => __($status),
            };

            return back()->withInput()->withErrors(['email' => $mensajeError]);
        } catch (Throwable $e) {
            Log::error('Fallo al enviar correo de recuperación de contraseña: '.$e->getMessage());

            // Eliminar token fallido para evitar quedar bloqueado por throttling
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            $mensajeUsuario = 'Ocurrió un error al despachar el correo: '.$e->getMessage().'. Por favor, verifica la configuración SMTP en el archivo .env.';

            return back()->withInput()->withErrors(['email' => $mensajeUsuario]);
        }
    }
}
