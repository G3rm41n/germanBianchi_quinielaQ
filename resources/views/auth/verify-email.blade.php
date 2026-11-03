@extends('layouts.app')

@section('title', 'Verificar correo electrónico')
@section('meta_description', 'Confirma tu dirección de correo electrónico en Agencia N°5801.')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card fade-up">

        <div class="auth-logo">
            <a href="{{ route('home') }}" aria-label="Ir al inicio">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo Agencia N°5801">
            </a>
        </div>

        <h1 class="auth-title">Verificá tu correo</h1>
        <p class="auth-subtitle">
            ¡Gracias por formar parte de Agencia N°5801! Te enviamos un correo electrónico con un enlace para verificar tu cuenta. Si no lo recibiste, puedes solicitar uno nuevo a continuación.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success" style="margin-bottom: 1.5rem; font-size: 0.85rem;" role="alert">
                Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que registraste.
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem;">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-resend-verification">
                    Reenviar correo de verificación
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-block" style="color: var(--color-muted); font-size: 0.85rem;">
                    Cerrar sesión
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

