@extends('layouts.app')

@section('title', 'Inicio')
@section('meta_description', 'Agencia N°5801 — Simulación de jugadas de lotería: Quiniela, Quini 6, Loto Plus, Loto 5 y Quiniela Poceada.')

@section('content')

    {{-- ===== Hero ===== --}}
    <section class="hero" aria-labelledby="hero-heading">
        <div class="container">
            <span class="hero-eyebrow">Simulación Académica · Agencia N°5801</span>
            <h1 class="hero-title" id="hero-heading">
                Tu agencia de quiniela<br>
                <span class="accent">en el mundo digital</span>
            </h1>
            <p class="hero-subtitle">
                Configurá tus jugadas, visualizá costos en tiempo real y descargá tu comprobante digital.
                Todo en un entorno simulado, seguro y de acceso libre.
            </p>
            <div class="hero-actions">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg" id="hero-btn-admin">
                            🗂️ Ir al Backoffice
                        </a>
                    @else
                        <a href="{{ route('jugadas.index') }}" class="btn btn-primary btn-lg" id="hero-btn-jugadas">
                            🎯 Mis Jugadas
                        </a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="btn btn-gold btn-lg" id="hero-btn-register">
                        Crear cuenta gratis
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline btn-lg" id="hero-btn-login">
                        Ya tengo cuenta
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- ===== Juegos disponibles ===== --}}
    <section class="games-section" aria-labelledby="juegos-heading">
        <div class="container">
            <h2 class="section-title" id="juegos-heading">Modalidades disponibles</h2>
            <p class="section-subtitle">Seleccioná el juego de tu preferencia y configurá tu jugada</p>

            <div class="games-grid">
                @foreach($juegos as $juego)
                    <article class="game-card" aria-label="{{ $juego['nombre'] }}">
                        <span class="game-card-icon" aria-hidden="true">{{ $juego['icono'] }}</span>
                        <div class="game-card-name">{{ $juego['nombre'] }}</div>
                        <p class="game-card-desc">{{ $juego['descripcion'] }}</p>
                        <span class="game-card-price">{{ $juego['precio'] }}</span>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== Cómo funciona ===== --}}
    <section class="steps-section" aria-labelledby="steps-heading">
        <div class="container">
            <h2 class="section-title" id="steps-heading">¿Cómo funciona?</h2>
            <p class="section-subtitle">Tres pasos simples para registrar tu jugada simulada</p>

            <div class="steps-grid">
                <div class="step-item">
                    <div class="step-number" aria-hidden="true">1</div>
                    <div class="step-title">Registrate</div>
                    <p class="step-desc">
                        Creá tu cuenta con tu nombre, DNI y correo electrónico.
                        El proceso tarda menos de un minuto.
                    </p>
                </div>
                <div class="step-item">
                    <div class="step-number" aria-hidden="true">2</div>
                    <div class="step-title">Elegí tu jugada</div>
                    <p class="step-desc">
                        Seleccioná la modalidad, configurá tus números y
                        visualizá el costo simulado en tiempo real.
                    </p>
                </div>
                <div class="step-item">
                    <div class="step-number" aria-hidden="true">3</div>
                    <div class="step-title">Descargá el comprobante</div>
                    <p class="step-desc">
                        Recibís un comprobante PDF con el detalle completo
                        de tu jugada para su seguimiento y archivo.
                    </p>
                </div>
            </div>
        </div>
    </section>

@endsection
