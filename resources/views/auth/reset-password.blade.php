@extends('layouts.app')

@section('title', 'Restablecer contraseña')
@section('meta_description', 'Definí tu nueva contraseña de acceso en Agencia N°5801.')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card fade-up">

        <div class="auth-logo">
            <a href="{{ route('home') }}" aria-label="Ir al inicio">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo Agencia N°5801">
            </a>
        </div>

        <h1 class="auth-title">Nueva contraseña</h1>
        <p class="auth-subtitle">Ingresá tu correo y la nueva contraseña que utilizarás a partir de ahora.</p>

        <form method="POST" action="{{ route('password.update') }}" id="form-reset-password" novalidate>
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Email --}}
            <div class="form-group">
                <label for="email" class="form-label">Correo electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email', $email) }}"
                    required
                    autocomplete="email"
                    aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                >
                @error('email')
                    <div class="form-error" id="email-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nueva Contraseña --}}
            <div class="form-group">
                <label for="password" class="form-label">Nueva contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Mínimo 8 caracteres"
                    required
                    autofocus
                    autocomplete="new-password"
                    aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                >
                @error('password')
                    <div class="form-error" id="password-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            {{-- Confirmar Contraseña --}}
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="Repetí la nueva contraseña"
                    required
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-reset-submit">
                Guardar nueva contraseña
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

