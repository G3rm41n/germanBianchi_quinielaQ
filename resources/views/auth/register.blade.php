@extends('layouts.app')

@section('title', 'Crear cuenta')
@section('meta_description', 'Registrate en Agencia N°5801 para acceder al simulador de jugadas de lotería.')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card fade-up">

        <div class="auth-logo">
            <a href="{{ route('home') }}" aria-label="Ir al inicio">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo Agencia N°5801">
            </a>
        </div>

        <h1 class="auth-title">Crear cuenta</h1>
        <p class="auth-subtitle">Completá tus datos para comenzar a simular jugadas</p>

        <form method="POST" action="{{ route('register.store') }}" id="form-register" novalidate>
            @csrf

            {{-- Nombre completo --}}
            <div class="form-group">
                <label for="name" class="form-label">Nombre completo *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ old('name') }}"
                    placeholder="Ej: Juan Pérez"
                    required
                    autocomplete="name"
                    aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                >
                @error('name')
                    <div class="form-error" id="name-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            {{-- DNI --}}
            <div class="form-group">
                <label for="dni" class="form-label">DNI *</label>
                <input
                    type="text"
                    id="dni"
                    name="dni"
                    class="form-input {{ $errors->has('dni') ? 'is-invalid' : '' }}"
                    value="{{ old('dni') }}"
                    placeholder="Ej: 12345678"
                    required
                    autocomplete="off"
                    aria-describedby="{{ $errors->has('dni') ? 'dni-error' : '' }}"
                >
                @error('dni')
                    <div class="form-error" id="dni-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="email" class="form-label">Correo electrónico *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="ejemplo@correo.com"
                    required
                    autocomplete="email"
                    aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                >
                @error('email')
                    <div class="form-error" id="email-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            {{-- Contraseña --}}
            <div class="form-group">
                <label for="password" class="form-label">Contraseña *</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Mínimo 8 caracteres"
                    required
                    autocomplete="new-password"
                    aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                >
                @error('password')
                    <div class="form-error" id="password-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            {{-- Confirmar contraseña --}}
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar contraseña *</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="Repetí tu contraseña"
                    required
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-register-submit">
                Crear cuenta
            </button>
        </form>

        <div class="auth-divider">
            ¿Ya tenés cuenta?
            <a href="{{ route('login') }}" id="link-to-login">Iniciá sesión</a>
        </div>

    </div>
</div>
@endsection
