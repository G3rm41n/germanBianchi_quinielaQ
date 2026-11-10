@extends('layouts.admin')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold); margin: 0;">Sorteos y Escrutinio</h1>
        <p style="color: var(--color-muted); font-size: 0.9rem; margin-top: 0.25rem;">Carga de extractos oficiales, simulación y liquidación automática de premios a billeteras.</p>
    </div>
    
    <a href="{{ route('admin.sorteos.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
        ➕ Nuevo Sorteo Oficial
    </a>
</div>

{{-- 1. Tarjetas de Métricas --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-primary); background-color: var(--color-surface);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem;">Total Sorteos</div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-text); font-family: var(--font-display);">
            {{ $metricas['total'] }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">Registrados en sistema</span>
    </div>

    <div class="card" style="padding: 1.25rem; border-left: 4px solid #ecc94b; background-color: var(--color-surface);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem;">Pendientes de Escrutinio</div>
        <div style="font-size: 1.6rem; font-weight: 700; color: #ecc94b; font-family: var(--font-display);">
            {{ $metricas['pendientes'] }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">Esperando liquidación</span>
    </div>

    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-success); background-color: var(--color-surface);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem;">Sorteos Escrutados</div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-success); font-family: var(--font-display);">
            {{ $metricas['escrutados'] }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">Premios ya acreditados</span>
    </div>

    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-danger); background-color: var(--color-surface);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem;">Premios Liquidados</div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-danger); font-family: var(--font-display);">
            ${{ number_format($metricas['total_premios'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">Acreditados en billeteras</span>
    </div>
</div>

{{-- 2. Filtros de Búsqueda --}}
<div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
    <form method="GET" action="{{ route('admin.sorteos.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 1rem; align-items: flex-end;">
        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Juego</label>
            <select name="juego" id="filtro-juego" class="form-input" style="width: 100%; padding: 0.45rem 0.6rem; font-size: 0.85rem;">
                <option value="">Todos los juegos</option>
                <option value="quiniela" {{ request('juego') === 'quiniela' ? 'selected' : '' }}>Quiniela</option>
                <option value="quini6" {{ request('juego') === 'quini6' ? 'selected' : '' }}>Quini 6</option>
                <option value="lotoplus" {{ request('juego') === 'lotoplus' ? 'selected' : '' }}>Loto Plus</option>
                <option value="loto5" {{ request('juego') === 'loto5' ? 'selected' : '' }}>Loto 5</option>
                <option value="poceada" {{ request('juego') === 'poceada' ? 'selected' : '' }}>Quiniela Poceada</option>
            </select>
        </div>

        <div id="contenedor-filtro-turno" style="{{ request('juego') === 'quiniela' ? '' : 'display: none;' }}">
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-gold); text-transform: uppercase; margin-bottom: 0.35rem;">
                ⏰ Horario / Turno
            </label>
            <select name="turno" id="select-filtro-turno" class="form-input" style="width: 100%; padding: 0.45rem 0.6rem; font-size: 0.85rem; border-color: var(--color-gold);">
                <option value="">Todos los horarios</option>
                <option value="previa" {{ request('turno') === 'previa' ? 'selected' : '' }}>La Previa (10:15 hs)</option>
                <option value="primera" {{ request('turno') === 'primera' ? 'selected' : '' }}>Primera (12:00 hs)</option>
                <option value="matutina" {{ request('turno') === 'matutina' ? 'selected' : '' }}>Matutina (15:00 hs)</option>
                <option value="vespertina" {{ request('turno') === 'vespertina' ? 'selected' : '' }}>Vespertina (18:00 hs)</option>
                <option value="nocturna" {{ request('turno') === 'nocturna' ? 'selected' : '' }}>Nocturna (21:00 hs)</option>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Estado</label>
            <select name="estado" class="form-input" style="width: 100%; padding: 0.45rem 0.6rem; font-size: 0.85rem;">
                <option value="">Todos los estados</option>
                <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="escrutado" {{ request('estado') === 'escrutado' ? 'selected' : '' }}>Escrutado</option>
                <option value="anulado" {{ request('estado') === 'anulado' ? 'selected' : '' }}>Anulado</option>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Fecha</label>
            <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-input" style="width: 100%; padding: 0.45rem 0.6rem; font-size: 0.85rem;">
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-outline" style="padding: 0.45rem 1rem; font-size: 0.85rem;">Filtrar</button>
            @if(request()->hasAny(['juego', 'turno', 'estado', 'fecha']))
                <a href="{{ route('admin.sorteos.index') }}" class="btn btn-ghost" style="padding: 0.45rem 0.75rem; font-size: 0.85rem;">Limpiar</a>
            @endif
        </div>
    </form>
</div>

{{-- 3. Tabla de Sorteos --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--color-border); background-color: rgba(255, 255, 255, 0.02);">
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600;">Fecha / ID</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600;">Juego</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600;">Turno / Jurisdicción</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600; text-align: center;">Estado</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600; text-align: center;">Boletas</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600; text-align: right;">Premios Pagados</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sorteos as $sorteo)
                    <tr style="border-bottom: 1px solid var(--color-border); transition: background-color 0.2s;">
                        <td style="padding: 1rem;">
                            <span style="font-weight: 600; color: var(--color-text);">{{ $sorteo->fecha_sorteo->format('d/m/Y') }}</span>
                            <div style="font-size: 0.75rem; color: var(--color-muted);">#{{ $sorteo->id }} {{ $sorteo->numero_sorteo ? '('.$sorteo->numero_sorteo.')' : '' }}</div>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="font-weight: 700; color: var(--color-gold);">{{ $sorteo->nombre_juego }}</span>
                        </td>
                        <td style="padding: 1rem; color: var(--color-muted);">
                            @if($sorteo->juego === 'quiniela')
                                <span>{{ $sorteo->nombre_jurisdiccion }}</span>
                                <span style="display: block; font-size: 0.75rem; color: var(--color-primary); font-weight: 600;">{{ $sorteo->nombre_turno }}</span>
                            @else
                                <span style="font-size: 0.8rem; color: var(--color-muted);">Sorteo Oficial</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            @if($sorteo->estado === 'escrutado')
                                <span class="badge badge-procesado" style="background: rgba(72, 187, 120, 0.15); color: #48bb78; border: 1px solid rgba(72, 187, 120, 0.3);">
                                    ✓ Escrutado
                                </span>
                            @elseif($sorteo->estado === 'pendiente')
                                <span class="badge badge-pendiente" style="background: rgba(236, 201, 75, 0.15); color: #ecc94b; border: 1px solid rgba(236, 201, 75, 0.3);">
                                    ⏳ Pendiente
                                </span>
                            @else
                                <span class="badge" style="background: rgba(229, 62, 62, 0.15); color: #e53e3e; border: 1px solid rgba(229, 62, 62, 0.3);">
                                    ✕ Anulado
                                </span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            @if($sorteo->estado === 'escrutado')
                                <span style="font-weight: 600; color: var(--color-text);">{{ $sorteo->total_boletas_evaluadas }}</span>
                                <span style="font-size: 0.75rem; color: #48bb78; display: block;">({{ $sorteo->total_boletas_ganadoras }} ganadoras)</span>
                            @else
                                <span style="color: var(--color-muted); font-size: 0.8rem;">Sin escrutar</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: right; font-weight: 700; color: {{ $sorteo->total_premios_liquidados > 0 ? '#48bb78' : 'var(--color-muted)' }};">
                            ${{ number_format($sorteo->total_premios_liquidados, 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <a href="{{ route('admin.sorteos.show', $sorteo) }}" class="btn btn-outline btn-sm" style="padding: 0.3rem 0.75rem;">
                                    {{ $sorteo->estado === 'pendiente' ? 'Simular / Escrutar' : 'Ver Detalle' }}
                                </a>

                                @if($sorteo->estado === 'pendiente')
                                    <form action="{{ route('admin.sorteos.destroy', $sorteo) }}" method="POST" onsubmit="return confirm('¿Seguro de eliminar este sorteo?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--color-danger); padding: 0.3rem 0.5rem;" title="Eliminar sorteo">
                                            🗑️
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 3rem 1rem; text-align: center; color: var(--color-muted);">
                            No hay sorteos registrados con los filtros seleccionados.
                            <div style="margin-top: 1rem;">
                                <a href="{{ route('admin.sorteos.create') }}" class="btn btn-primary btn-sm">Cargar el primer sorteo</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sorteos->hasPages())
        <div style="padding: 1rem; border-top: 1px solid var(--color-border);">
            {{ $sorteos->links() }}
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectJuego = document.getElementById('filtro-juego');
    const contenedorTurno = document.getElementById('contenedor-filtro-turno');
    const selectTurno = document.getElementById('select-filtro-turno');

    if (selectJuego && contenedorTurno && selectTurno) {
        selectJuego.addEventListener('change', function () {
            if (this.value === 'quiniela') {
                contenedorTurno.style.display = 'block';
            } else {
                contenedorTurno.style.display = 'none';
                selectTurno.value = '';
            }
        });
    }
});
</script>
@endsection

