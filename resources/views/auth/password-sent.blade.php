@extends('layouts.app')

@section('title', 'Correo enviado')
@section('meta_description', 'Instrucciones para restablecer tu contraseña en Agencia N°5801.')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card fade-up" style="max-width: 520px; text-align: center;">

        <div class="auth-logo" style="margin-bottom: 1.5rem;">
            <a href="{{ route('home') }}" aria-label="Ir al inicio">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo Agencia N°5801">
            </a>
        </div>

        <div style="width: 64px; height: 64px; background: rgba(212, 175, 55, 0.15); border: 2px solid var(--color-gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; font-size: 2rem;">
            ✉️
        </div>

        <h1 class="auth-title" style="margin-bottom: 0.5rem; color: var(--color-gold);">¡Revisa tu bandeja de entrada!</h1>
        
        <p style="color: var(--color-text); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
            Hemos enviado las instrucciones y el enlace para restablecer tu contraseña a:<br>
            <strong style="color: var(--color-gold); font-size: 1.05rem; word-break: break-all;">
                {{ session('email_sent') ?? 'tu correo electrónico' }}
            </strong>
        </p>

        <div style="background-color: var(--color-surface2); border: 1px solid var(--color-border); border-radius: 8px; padding: 1.25rem; text-align: left; margin-bottom: 1.75rem; font-size: 0.85rem; color: var(--color-muted); line-height: 1.6;">
            <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                <span>⏱️</span>
                <span><strong>Tiempo de validez:</strong> Por seguridad, el enlace expirará en <strong>60 minutos</strong>.</span>
            </div>
            <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                <span>📁</span>
                <span><strong>Carpeta de Spam:</strong> Si no lo encuentras en tu bandeja principal, revisa la carpeta de correo no deseado.</span>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <span>🔗</span>
                <span><strong>Paso a seguir:</strong> Haz clic en el botón <em>"Restablecer mi Contraseña"</em> del correo y podrás elegir tu nueva clave.</span>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-lg" id="btn-ir-login">
                Volver a Iniciar Sesión
            </a>

            <a href="{{ route('password.request') }}" class="btn btn-ghost btn-block" style="font-size: 0.85rem; color: var(--color-muted);">
                ¿No recibiste el correo? Solicitar otro enlace
            </a>
        </div>

    </div>
</div>
@endsection

