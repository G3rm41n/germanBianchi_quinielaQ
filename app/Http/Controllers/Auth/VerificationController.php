<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Muestra el aviso informativo de verificación de correo electrónico.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('jugadas.index')
            : view('auth.verify-email');
    }

    /**
     * Procesa y valida el enlace firmado recibido por correo electrónico.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('jugadas.index')
                ->with('info', 'Tu cuenta de correo ya se encontraba verificada.');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('jugadas.index')
            ->with('success', '¡Felicitaciones! Tu correo electrónico ha sido verificado con éxito.');
    }

    /**
     * Reenvía un nuevo correo de verificación al usuario autenticado.
     */
    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('jugadas.index');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
