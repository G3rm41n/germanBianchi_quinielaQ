@extends('layouts.app')

@section('title', 'Pizarra Oficial: ' . $sorteo->nombre_juego)

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                {{ $sorteo->fecha_sorteo->translatedFormat('l d \d\e F \d\e Y') }}
            </div>
            <h1 style="font-family: var(--font-display); font-size: 2.25rem; font-weight: 700; color: var(--color-gold); margin: 0.25rem 0 0 0;">
                Extracto Oficial: {{ $sorteo->nombre_juego }}
            </h1>
            <p style="color: var(--color-muted); font-size: 1rem; margin-top: 0.35rem;">
                @if($sorteo->juego === 'quiniela')
                    Jurisdicción: <strong>{{ $sorteo->nombre_jurisdiccion }}</strong> | Turno: <strong>{{ $sorteo->nombre_turno }}</strong>
                @endif
                @if($sorteo->numero_sorteo)
                    | Sorteo Oficial N°: <strong>{{ $sorteo->numero_sorteo }}</strong>
                @endif
            </p>
        </div>

        <a href="{{ route('sorteos.index') }}" class="btn btn-ghost" style="border: 1px solid var(--color-border);">
            ← Volver a la Pizarra
        </a>
    </div>

    {{-- Extracto Completo --}}
    <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
        @if($sorteo->juego === 'quiniela')
            <h3 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600; color: var(--color-gold); margin-top: 0; margin-bottom: 1.5rem; text-align: center;">
                Pizarra de las 20 Posiciones Oficiales
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1rem;">
                @php
                    $extracto = $sorteo->extracto;
                @endphp
                @for ($i = 1; $i <= 20; $i++)
                    @php
                        $val = $extracto[$i] ?? $extracto[(string)$i] ?? $extracto[$i-1] ?? '----';
                    @endphp
                    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid {{ $i === 1 ? 'var(--color-gold)' : 'var(--color-border)' }}; border-radius: 8px; padding: 0.85rem; text-align: center;">
                        <div style="font-size: 0.75rem; color: var(--color-muted); font-weight: 700;">{{ $i }}° Premio</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: {{ $i === 1 ? 'var(--color-gold)' : 'var(--color-text)' }}; letter-spacing: 2px; margin-top: 0.25rem;">
                            {{ str_pad((string)$val, 4, '0', STR_PAD_LEFT) }}
                        </div>
                    </div>
                @endfor
            </div>
        @elseif(in_array($sorteo->juego, ['quini6', 'loto5', 'poceada']))
            <h3 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600; color: var(--color-gold); margin-top: 0; margin-bottom: 1.5rem; text-align: center;">
                Números Sorteados
            </h3>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; padding: 1rem 0;">
                @php
                    $numeros = $sorteo->extracto['numeros'] ?? $sorteo->extracto ?? [];
                @endphp
                @foreach($numeros as $num)
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(212, 160, 23, 0.15); border: 2px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800; color: var(--color-gold);">
                        {{ str_pad((string)$num, 2, '0', STR_PAD_LEFT) }}
                    </div>
                @endforeach
            </div>
        @elseif($sorteo->juego === 'lotoplus')
            <h3 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600; color: var(--color-gold); margin-top: 0; margin-bottom: 1.5rem; text-align: center;">
                Números Sorteados y Número Plus
            </h3>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; align-items: center; padding: 1rem 0;">
                @php
                    $numeros = $sorteo->extracto['numeros'] ?? [];
                    $plus = $sorteo->extracto['numero_plus'] ?? null;
                @endphp
                @foreach($numeros as $num)
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(212, 160, 23, 0.15); border: 2px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800; color: var(--color-gold);">
                        {{ str_pad((string)$num, 2, '0', STR_PAD_LEFT) }}
                    </div>
                @endforeach
                @if($plus !== null)
                    <div style="display: flex; flex-direction: column; align-items: center; margin-left: 1rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-gold); margin-bottom: 0.25rem;">PLUS</span>
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--color-gold); color: #0d1117; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 900;">
                            {{ $plus }}
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if(!empty($sorteo->tabla_premios))
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
                <h4 style="font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--color-text); margin-bottom: 0.75rem;">
                    Categorías de Premios Oficiales:
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    @foreach($sorteo->tabla_premios as $cat => $monto)
                        <div style="background: rgba(255, 255, 255, 0.02); padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid var(--color-border);">
                            <div style="font-size: 0.8rem; color: var(--color-muted);">{{ ucfirst(str_replace('_', ' ', (string)$cat)) }}</div>
                            <div style="font-size: 1.25rem; font-weight: 700; color: var(--color-gold); margin-top: 0.25rem;">
                                ${{ number_format((float)$monto, 2, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @auth
        <div style="text-align: center;">
            <a href="{{ route('jugadas.index') }}" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                🎟️ Ver si tengo boletas premiadas en Mis Jugadas
            </a>
        </div>
    @endauth
</div>
@endsection

