@extends('layouts.app')

@section('title', 'Mis Jugadas')
@section('meta_description', 'Panel de jugadas de Agencia N°5801')

@section('content')
<div class="container" style="padding-top: 2.5rem; padding-bottom: 3rem;">

    <div class="placeholder-panel">
        <span class="placeholder-icon" aria-hidden="true">🎯</span>
        <h1 class="placeholder-title">Mis Jugadas</h1>
        <p class="placeholder-desc">
            Bienvenido/a, <strong>{{ auth()->user()->name }}</strong>.<br>
            Desde aquí podrás registrar y hacer seguimiento de tus jugadas simuladas.
        </p>
        <div class="placeholder-banner">
            ⏳ Esta sección estará disponible en la Fase 3 del desarrollo
        </div>

        <div style="margin-top: 2rem;">
            <div class="card" style="max-width: 480px; margin: 0 auto; text-align: left;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.78rem;">
                    Próximamente disponible
                </h2>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem;">
                    <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                        <span style="color: var(--color-primary);">✓</span> Nueva jugada (5 modalidades)
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                        <span style="color: var(--color-primary);">✓</span> Cálculo de costo en tiempo real
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                        <span style="color: var(--color-primary);">✓</span> Descarga de comprobante PDF
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                        <span style="color: var(--color-primary);">✓</span> Historial y estado de jugadas
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
