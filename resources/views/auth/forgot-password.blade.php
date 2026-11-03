@extends('layouts.app')

@section('title', 'Recuperar contraseña')
@section('meta_description', 'Recuperá el acceso a tu cuenta en Agencia N°5801 mediante correo electrónico.')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card fade-up">

        <div class="auth-logo">
            <a href="{{ route('home') }}" aria-label="Ir al inicio">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo Agencia N°5801">
            </a>
        </div>

        <h1 class="auth-title">Recuperar contraseña</h1>
        <p class="auth-subtitle">Ingresá tu correo electrónico y te enviaremos un enlace seguro para restablecer tu clave.</p>

        @if (session('status'))
            <div class="alert alert-success" style="margin-bottom: 1.5rem; font-size: 0.85rem;" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" id="form-forgot-password" novalidate>
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label for="email" class="form-label">Correo electrónico registrado</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="ejemplo@correo.com"
                    required
                    autofocus
                    autocomplete="email"
                    aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                >
                @error('email')
                    <div class="form-error" id="email-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-forgot-submit">
                Enviar enlace de recuperación
            </button>
        </form>

        <div class="auth-divider">
            <a href="{{ route('login') }}" style="color: var(--color-gold); font-size: 0.85rem; text-decoration: none;">
                ← Volver a Iniciar sesión
            </a>
        </div>

    </div>
</div>
@endsection

