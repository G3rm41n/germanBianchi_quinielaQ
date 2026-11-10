@extends('layouts.app')

@section('title', 'Pizarra de Resultados Oficiales')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-family: var(--font-display); font-size: 2.25rem; font-weight: 700; color: var(--color-gold); margin: 0;">
                Pizarra de Sorteos Oficiales
            </h1>
            <p style="color: var(--color-muted); font-size: 1rem; margin-top: 0.35rem;">
                Consulta los extractos y números ganadores oficiales de Quiniela, Quini 6, Loto Plus, Loto 5 y Poceada.
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card" style="padding: 1.25rem; margin-bottom: 2rem;">
        <form method="GET" action="{{ route('sorteos.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 1rem; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Modalidad</label>
                <select name="juego" id="public-filtro-juego" class="form-input" style="width: 100%;">
                    <option value="">Todos los juegos</option>
                    <option value="quiniela" {{ request('juego') === 'quiniela' ? 'selected' : '' }}>Quiniela</option>
                    <option value="quini6" {{ request('juego') === 'quini6' ? 'selected' : '' }}>Quini 6</option>
                    <option value="lotoplus" {{ request('juego') === 'lotoplus' ? 'selected' : '' }}>Loto Plus</option>
                    <option value="loto5" {{ request('juego') === 'loto5' ? 'selected' : '' }}>Loto 5</option>
                    <option value="poceada" {{ request('juego') === 'poceada' ? 'selected' : '' }}>Quiniela Poceada</option>
                </select>
            </div>

            <div id="public-contenedor-turno" style="{{ request('juego') === 'quiniela' ? '' : 'display: none;' }}">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--color-gold); text-transform: uppercase; margin-bottom: 0.35rem;">
                    ⏰ Horario / Turno
                </label>
                <select name="turno" id="public-filtro-turno" class="form-input" style="width: 100%; border-color: var(--color-gold);">
                    <option value="">Todos los horarios</option>
                    <option value="previa" {{ request('turno') === 'previa' ? 'selected' : '' }}>La Previa (10:15 hs)</option>
                    <option value="primera" {{ request('turno') === 'primera' ? 'selected' : '' }}>Primera (12:00 hs)</option>
                    <option value="matutina" {{ request('turno') === 'matutina' ? 'selected' : '' }}>Matutina (15:00 hs)</option>
                    <option value="vespertina" {{ request('turno') === 'vespertina' ? 'selected' : '' }}>Vespertina (18:00 hs)</option>
                    <option value="nocturna" {{ request('turno') === 'nocturna' ? 'selected' : '' }}>Nocturna (21:00 hs)</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Fecha</label>
                <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-input" style="width: 100%;">
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                @if(request()->hasAny(['juego', 'turno', 'fecha']))
                    <a href="{{ route('sorteos.index') }}" class="btn btn-ghost">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Lista de Sorteos --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        @forelse($sorteos as $sorteo)
            <div class="card" style="padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; margin-bottom: 1rem;">
                    <div>
                        <span style="font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            {{ $sorteo->fecha_sorteo->translatedFormat('l d \d\e F \d\e Y') }}
                        </span>
                        <h2 style="font-family: var(--font-display); font-size: 1.4rem; font-weight: 700; color: var(--color-gold); margin: 0.2rem 0 0 0;">
                            {{ $sorteo->nombre_juego }}
                            @if($sorteo->juego === 'quiniela')
                                <span style="font-size: 0.95rem; font-weight: 500; color: var(--color-text);">— {{ $sorteo->nombre_jurisdiccion }} ({{ $sorteo->nombre_turno }})</span>
                            @endif
                        </h2>
                    </div>

                    <div>
                        <a href="{{ route('sorteos.show', $sorteo) }}" class="btn btn-outline btn-sm">
                            Ver Pizarra Completa →
                        </a>
                    </div>
                </div>

                {{-- Extracto Resumido --}}
                @if($sorteo->juego === 'quiniela')
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem;">
                        @php
                            $extracto = $sorteo->extracto;
                        @endphp
                        @for ($i = 1; $i <= min(10, count($extracto)); $i++)
                            @php
                                $val = $extracto[$i] ?? $extracto[(string)$i] ?? $extracto[$i-1] ?? '----';
                            @endphp
                            <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-border); border-radius: 6px; padding: 0.4rem; text-align: center;">
                                <div style="font-size: 0.65rem; color: var(--color-muted);">{{ $i }}°</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: {{ $i === 1 ? 'var(--color-gold)' : 'var(--color-text)' }};">
                                    {{ str_pad((string)$val, 4, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>
                        @endfor
                    </div>
                    @if(count($extracto) > 10)
                        <div style="font-size: 0.75rem; color: var(--color-muted); margin-top: 0.5rem; text-align: right;">
                            Mostrando los primeros 10 premios. Haz clic en 'Ver Pizarra Completa' para ver los 20.
                        </div>
                    @endif
                @elseif(in_array($sorteo->juego, ['quini6', 'loto5', 'poceada']))
                    <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                        @php
                            $numeros = $sorteo->extracto['numeros'] ?? $sorteo->extracto ?? [];
                        @endphp
                        @foreach(array_slice($numeros, 0, 10) as $num)
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(212, 160, 23, 0.15); border: 2px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; color: var(--color-gold);">
                                {{ str_pad((string)$num, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        @endforeach
                    </div>
                @elseif($sorteo->juego === 'lotoplus')
                    <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
                        @php
                            $numeros = $sorteo->extracto['numeros'] ?? [];
                            $plus = $sorteo->extracto['numero_plus'] ?? null;
                        @endphp
                        @foreach($numeros as $num)
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(212, 160, 23, 0.15); border: 2px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; color: var(--color-gold);">
                                {{ str_pad((string)$num, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        @endforeach
                        @if($plus !== null)
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-gold); color: #0d1117; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 900; margin-left: 0.5rem;" title="Número Plus">
                                {{ $plus }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="card" style="padding: 3rem; text-align: center; color: var(--color-muted);">
                ℹ No hay sorteos oficiales cargados aún para los criterios seleccionados.
            </div>
        @endforelse
    </div>

    @if($sorteos->hasPages())
        <div style="margin-top: 2rem;">
            {{ $sorteos->links() }}
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectJuego = document.getElementById('public-filtro-juego');
    const contenedorTurno = document.getElementById('public-contenedor-turno');
    const selectTurno = document.getElementById('public-filtro-turno');

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

