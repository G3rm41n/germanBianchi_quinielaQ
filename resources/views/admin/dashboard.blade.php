@extends('layouts.admin')

@section('title', 'Panel Admin')
@section('page_title', 'Panel de Administración')

@section('content')

    {{-- Métricas placeholder --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 2rem;">

        <div class="card" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; font-family: var(--font-display); color: var(--color-gold);">
                —
            </div>
            <div style="font-size: 0.85rem; color: var(--color-muted); margin-top: 0.25rem;">Jugadas hoy</div>
        </div>

        <div class="card" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; font-family: var(--font-display); color: var(--color-warning);">
                —
            </div>
            <div style="font-size: 0.85rem; color: var(--color-muted); margin-top: 0.25rem;">Pendientes</div>
        </div>

        <div class="card" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; font-family: var(--font-display); color: var(--color-success);">
                —
            </div>
            <div style="font-size: 0.85rem; color: var(--color-muted); margin-top: 0.25rem;">Procesadas</div>
        </div>

    </div>

    {{-- Panel placeholder --}}
    <div class="card">
        <div class="placeholder-panel" style="padding: 3rem 1.5rem;">
            <span class="placeholder-icon" aria-hidden="true">🗂️</span>
            <h1 class="placeholder-title">Backoffice Administrativo</h1>
            <p class="placeholder-desc">
                Bienvenido, <strong>{{ auth()->user()->name }}</strong>.<br>
                El panel completo de gestión estará disponible en la Fase 6 del desarrollo.
            </p>
            <div class="placeholder-banner">
                ⏳ Esta sección estará disponible en la Fase 6 del desarrollo
            </div>

            <div style="margin-top: 2rem;">
                <div class="card" style="max-width: 520px; margin: 0 auto; text-align: left;">
                    <h2 style="font-size: 0.78rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                        Próximamente disponible
                    </h2>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem;">
                        <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                            <span style="color: var(--color-primary);">✓</span> Grilla de jugadas con filtros por estado y fecha
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                            <span style="color: var(--color-primary);">✓</span> Cambio de estado: Pendiente → Procesado
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                            <span style="color: var(--color-primary);">✓</span> Copia rápida de datos al portapapeles
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                            <span style="color: var(--color-primary);">✓</span> Exportación de comprobantes PDF
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                            <span style="color: var(--color-primary);">✓</span> Gestión de precios por modalidad
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--color-muted);">
                            <span style="color: var(--color-primary);">✓</span> Audit log y limpieza de base de datos
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
