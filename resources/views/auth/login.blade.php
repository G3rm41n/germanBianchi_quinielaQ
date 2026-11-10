@extends('layouts.app')

@section('title', 'Iniciar sesión')
@section('meta_description', 'Ingresá a tu cuenta de Agencia N°5801 para gestionar tus jugadas simuladas.')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card fade-up">

        <div class="auth-logo">
            <a href="{{ route('home') }}" aria-label="Ir al inicio">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo Agencia N°5801">
            </a>
        </div>

        <h1 class="auth-title">Iniciar sesión</h1>
        <p class="auth-subtitle">Ingresá con tu correo y contraseña registrados</p>

        <form method="POST" action="{{ route('login.store') }}" id="form-login" novalidate>
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label for="email" class="form-label">Correo electrónico</label>
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

            {{-- Contraseña --}}
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label for="password" class="form-label" style="margin-bottom: 0;">Contraseña</label>
                    <a href="{{ route('password.request') }}" style="font-size: 0.78rem; color: var(--color-gold); text-decoration: none;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Tu contraseña"
                    required
                    autocomplete="current-password"
                    aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                >
                @error('password')
                    <div class="form-error" id="password-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-login-submit">
                Ingresar
            </button>
        </form>

        <div class="auth-divider">
            ¿No tenés cuenta?
            <a href="{{ route('register') }}" id="link-to-register">Registrate gratis</a>
        </div>

    </div>
</div>
@endsection
