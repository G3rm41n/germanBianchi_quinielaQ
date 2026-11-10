@extends('layouts.admin')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold); margin: 0;">
                Sorteo #{{ $sorteo->id }} — {{ $sorteo->nombre_juego }}
            </h1>
            @if($sorteo->estado === 'escrutado')
                <span class="badge badge-procesado" style="background: rgba(72, 187, 120, 0.15); color: #48bb78; border: 1px solid rgba(72, 187, 120, 0.3); font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                    ✓ Escrutado
                </span>
            @elseif($sorteo->estado === 'pendiente')
                <span class="badge badge-pendiente" style="background: rgba(236, 201, 75, 0.15); color: #ecc94b; border: 1px solid rgba(236, 201, 75, 0.3); font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                    ⏳ Pendiente de Escrutinio
                </span>
            @else
                <span class="badge" style="background: rgba(229, 62, 62, 0.15); color: #e53e3e; border: 1px solid rgba(229, 62, 62, 0.3); font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                    ✕ Anulado
                </span>
            @endif
        </div>
        <p style="color: var(--color-muted); font-size: 0.9rem; margin-top: 0.35rem;">
            Fecha del sorteo: <strong>{{ $sorteo->fecha_sorteo->format('d/m/Y') }}</strong>
            @if($sorteo->juego === 'quiniela')
                | Jurisdicción: <strong>{{ $sorteo->nombre_jurisdiccion }}</strong>
                | Turno: <strong>{{ $sorteo->nombre_turno }}</strong>
            @endif
            @if($sorteo->numero_sorteo)
                | Concurso: <strong>{{ $sorteo->numero_sorteo }}</strong>
            @endif
        </p>
    </div>
    
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('admin.sorteos.index') }}" class="btn btn-ghost" style="border: 1px solid var(--color-border);">
            ← Volver al listado
        </a>
    </div>
</div>

{{-- 1. Extracto Oficial Visual --}}
<div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
    <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-gold); margin-top: 0; margin-bottom: 1rem;">
        Extracto Oficial Cargado
    </h3>

    @if($sorteo->juego === 'quiniela')
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 0.75rem;">
            @php
                $extracto = $sorteo->extracto;
            @endphp
            @for ($i = 1; $i <= 20; $i++)
                @php
                    $val = $extracto[$i] ?? $extracto[(string)$i] ?? $extracto[$i-1] ?? '----';
                @endphp
                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--color-border); border-radius: 8px; padding: 0.6rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--color-muted); font-weight: 600;">{{ $i }}° Premio</div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: {{ $i === 1 ? 'var(--color-gold)' : 'var(--color-text)' }}; letter-spacing: 1px;">
                        {{ str_pad((string)$val, 4, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            @endfor
        </div>
    @elseif(in_array($sorteo->juego, ['quini6', 'loto5', 'poceada']))
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; justify-content: center; padding: 1rem 0;">
            @php
                $numeros = $sorteo->extracto['numeros'] ?? $sorteo->extracto ?? [];
            @endphp
            @foreach($numeros as $num)
                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(212, 160, 23, 0.15); border: 2px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; color: var(--color-gold);">
                    {{ str_pad((string)$num, 2, '0', STR_PAD_LEFT) }}
                </div>
            @endforeach
        </div>
    @elseif($sorteo->juego === 'lotoplus')
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; justify-content: center; padding: 1rem 0;">
            @php
                $numeros = $sorteo->extracto['numeros'] ?? [];
                $plus = $sorteo->extracto['numero_plus'] ?? null;
            @endphp
            @foreach($numeros as $num)
                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(212, 160, 23, 0.15); border: 2px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; color: var(--color-gold);">
                    {{ str_pad((string)$num, 2, '0', STR_PAD_LEFT) }}
                </div>
            @endforeach
            @if($plus !== null)
                <div style="display: flex; flex-direction: column; align-items: center; margin-left: 1rem;">
                    <span style="font-size: 0.7rem; font-weight: 700; color: var(--color-gold);">PLUS</span>
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--color-gold); color: #0d1117; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 900;">
                        {{ $plus }}
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Tabla de Premios (para juegos con pozos) --}}
    @if(!empty($sorteo->tabla_premios))
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--color-border);">
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Tabla de Premios Configurada:</div>
            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                @foreach($sorteo->tabla_premios as $cat => $monto)
                    <div style="background: rgba(255, 255, 255, 0.02); padding: 0.4rem 0.8rem; border-radius: 6px; border: 1px solid var(--color-border); font-size: 0.85rem;">
                        <span style="color: var(--color-muted);">{{ ucfirst(str_replace('_', ' ', (string)$cat)) }}:</span>
                        <strong style="color: var(--color-text); margin-left: 0.25rem;">${{ number_format((float)$monto, 2, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- 2. SECCIÓN: SI ESTÁ PENDIENTE DE ESCRUTINIO (SIMULACIÓN Y CONFIRMACIÓN) --}}
@if($sorteo->estado === 'pendiente' && $simulacion)
    <div class="card" style="padding: 1.75rem; margin-bottom: 2rem; border-left: 4px solid #ecc94b;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <h2 style="font-family: var(--font-display); font-size: 1.4rem; font-weight: 700; color: #ecc94b; margin: 0;">
                    Previsualización del Escrutinio (Simulación)
                </h2>
                <p style="color: var(--color-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                    Boletas pendientes del {{ $sorteo->fecha_sorteo->format('d/m/Y') }} que coinciden con este sorteo. Los saldos NO se modificarán hasta que confirmes.
                </p>
            </div>

            <form action="{{ route('admin.sorteos.escrutar', $sorteo) }}" method="POST" onsubmit="return confirm('¿Confirmas el escrutinio definitivo? Se acreditarán ${{ number_format($simulacion['total_premios'], 2, ',', '.') }} en las billeteras de los apostadores ganadores.');">
                @csrf
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem; font-size: 1rem; font-weight: 700;">
                    ✓ Confirmar y Liquidar Premios (${{ number_format($simulacion['total_premios'], 2, ',', '.') }})
                </button>
            </form>
        </div>

        {{-- Resumen de la Simulación --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: rgba(255, 255, 255, 0.02); padding: 1rem; border-radius: 8px; border: 1px solid var(--color-border);">
                <span style="font-size: 0.75rem; color: var(--color-muted); text-transform: uppercase;">Boletas Participantes</span>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-text);">{{ $simulacion['total_boletas'] }}</div>
            </div>
            <div style="background: rgba(72, 187, 120, 0.05); padding: 1rem; border-radius: 8px; border: 1px solid rgba(72, 187, 120, 0.2);">
                <span style="font-size: 0.75rem; color: #48bb78; text-transform: uppercase;">Boletas Ganadoras</span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #48bb78;">{{ $simulacion['total_ganadoras'] }}</div>
            </div>
            <div style="background: rgba(255, 255, 255, 0.02); padding: 1rem; border-radius: 8px; border: 1px solid var(--color-border);">
                <span style="font-size: 0.75rem; color: var(--color-muted); text-transform: uppercase;">No Ganadoras</span>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-muted);">{{ $simulacion['total_no_ganadoras'] }}</div>
            </div>
            <div style="background: rgba(212, 160, 23, 0.05); padding: 1rem; border-radius: 8px; border: 1px solid rgba(212, 160, 23, 0.2);">
                <span style="font-size: 0.75rem; color: var(--color-gold); text-transform: uppercase;">Total a Acreditar</span>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gold);">${{ number_format($simulacion['total_premios'], 2, ',', '.') }}</div>
            </div>
        </div>

        {{-- Detalle de Boletas en Simulación --}}
        @if($simulacion['total_boletas'] > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--color-border); text-align: left;">
                            <th style="padding: 0.75rem; color: var(--color-muted);">Ticket #</th>
                            <th style="padding: 0.75rem; color: var(--color-muted);">Cliente</th>
                            <th style="padding: 0.75rem; color: var(--color-muted);">Aciertos / Desglose</th>
                            <th style="padding: 0.75rem; color: var(--color-muted); text-align: center;">Resultado</th>
                            <th style="padding: 0.75rem; color: var(--color-muted); text-align: right;">Premio Simulado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($simulacion['boletas'] as $item)
                            @php
                                $b = $item['jugada'];
                            @endphp
                            <tr style="border-bottom: 1px solid var(--color-border); background: {{ $item['ganadora'] ? 'rgba(72, 187, 120, 0.03)' : 'transparent' }};">
                                <td style="padding: 0.75rem; font-weight: 600;">#{{ $b->id }}</td>
                                <td style="padding: 0.75rem;">
                                    <strong>{{ $b->user->name }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--color-muted);">{{ $b->user->email }}</div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    @if($item['ganadora'])
                                        <div style="color: #48bb78; font-weight: 600;">
                                            @foreach($item['detalles'] as $d)
                                                @if(!empty($d['premio']) && $d['premio'] > 0)
                                                    <div>
                                                        ✓ Acertó {{ $d['numero'] ?? implode(',', $d['numeros_acertados'] ?? []) }}
                                                        ({{ $d['aciertos'] }} aciertos) — Premio: ${{ number_format($d['premio'], 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color: var(--color-muted);">Sin aciertos premiados</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    @if($item['ganadora'])
                                        <span class="badge" style="background: rgba(72, 187, 120, 0.15); color: #48bb78; border: 1px solid rgba(72, 187, 120, 0.3);">Ganadora</span>
                                    @else
                                        <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--color-muted); border: 1px solid var(--color-border);">No Ganadora</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-weight: 700; color: {{ $item['ganadora'] ? '#48bb78' : 'var(--color-muted)' }};">
                                    ${{ number_format($item['monto_premio'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 2rem; text-align: center; color: var(--color-muted);">
                ℹ No se encontraron boletas pendientes de sorteo para la fecha <strong>{{ $sorteo->fecha_sorteo->format('d/m/Y') }}</strong> y modalidad <strong>{{ $sorteo->nombre_juego }}</strong>.
            </div>
        @endif
    </div>
@endif

{{-- 3. SECCIÓN: SI YA ESTÁ ESCRUTADO (RESULTADOS FINALES Y BOTÓN REVERSIÓN) --}}
@if($sorteo->estado === 'escrutado')
    <div class="card" style="padding: 1.75rem; margin-bottom: 2rem; border-left: 4px solid var(--color-success);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <h2 style="font-family: var(--font-display); font-size: 1.4rem; font-weight: 700; color: var(--color-success); margin: 0;">
                    Sorteo Liquidado Oficialmente
                </h2>
                <p style="color: var(--color-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                    Escrutado el {{ $sorteo->escrutado_en?->format('d/m/Y H:i') }} | Total liquidado: <strong>${{ number_format($sorteo->total_premios_liquidados, 2, ',', '.') }}</strong>
                </p>
            </div>

            {{-- Botón de Reversión (Rollback de Seguridad) --}}
            <form action="{{ route('admin.sorteos.revertir', $sorteo) }}" method="POST" onsubmit="return confirm('ATENCIÓN: ¿Seguro que deseas revertir este sorteo? Se debitarán los premios ya pagados de las billeteras de los clientes y las boletas volverán a estar pendientes.');">
                @csrf
                <button type="submit" class="btn btn-ghost" style="color: var(--color-danger); border: 1px solid var(--color-danger); font-size: 0.85rem;">
                    ⚠️ Revertir Sorteo (Rollback)
                </button>
            </form>
        </div>

        {{-- Métricas consolidadas --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: rgba(255, 255, 255, 0.02); padding: 1rem; border-radius: 8px; border: 1px solid var(--color-border);">
                <span style="font-size: 0.75rem; color: var(--color-muted); text-transform: uppercase;">Boletas Evaluadas</span>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-text);">{{ $sorteo->total_boletas_evaluadas }}</div>
            </div>
            <div style="background: rgba(72, 187, 120, 0.05); padding: 1rem; border-radius: 8px; border: 1px solid rgba(72, 187, 120, 0.2);">
                <span style="font-size: 0.75rem; color: #48bb78; text-transform: uppercase;">Boletas Ganadoras</span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #48bb78;">{{ $sorteo->total_boletas_ganadoras }}</div>
            </div>
            <div style="background: rgba(212, 160, 23, 0.05); padding: 1rem; border-radius: 8px; border: 1px solid rgba(212, 160, 23, 0.2);">
                <span style="font-size: 0.75rem; color: var(--color-gold); text-transform: uppercase;">Total Pagado</span>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gold);">${{ number_format($sorteo->total_premios_liquidados, 2, ',', '.') }}</div>
            </div>
        </div>

        {{-- Listado de Boletas Escrutadas --}}
        @if($jugadasEscrutadas && $jugadasEscrutadas->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--color-border); text-align: left;">
                            <th style="padding: 0.75rem; color: var(--color-muted);">Ticket #</th>
                            <th style="padding: 0.75rem; color: var(--color-muted);">Cliente</th>
                            <th style="padding: 0.75rem; color: var(--color-muted);">Aciertos / Desglose</th>
                            <th style="padding: 0.75rem; color: var(--color-muted); text-align: center;">Resultado</th>
                            <th style="padding: 0.75rem; color: var(--color-muted); text-align: right;">Premio Acreditado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jugadasEscrutadas as $jugada)
                            <tr style="border-bottom: 1px solid var(--color-border); background: {{ $jugada->esGanadora() ? 'rgba(72, 187, 120, 0.03)' : 'transparent' }};">
                                <td style="padding: 0.75rem; font-weight: 600;">#{{ $jugada->id }}</td>
                                <td style="padding: 0.75rem;">
                                    <a href="{{ route('admin.users.show', $jugada->user) }}" style="color: var(--color-gold); text-decoration: none; font-weight: 600;">
                                        {{ $jugada->user->name }}
                                    </a>
                                    <div style="font-size: 0.75rem; color: var(--color-muted);">{{ $jugada->user->email }}</div>
                                </td>
                                <td style="padding: 0.75rem;">
                                    @if($jugada->esGanadora() && !empty($jugada->detalle_escrutinio))
                                        <div style="color: #48bb78; font-size: 0.8rem;">
                                            @foreach($jugada->detalle_escrutinio as $d)
                                                @if(!empty($d['premio']) && $d['premio'] > 0)
                                                    <div>
                                                        ✓ Acertó {{ $d['numero'] ?? implode(',', $d['numeros_acertados'] ?? []) }}
                                                        ({{ $d['aciertos'] }} aciertos) — ${{ number_format($d['premio'], 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color: var(--color-muted);">Sin aciertos</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    @if($jugada->esGanadora())
                                        <span class="badge" style="background: rgba(72, 187, 120, 0.15); color: #48bb78; border: 1px solid rgba(72, 187, 120, 0.3);">Ganadora</span>
                                    @else
                                        <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--color-muted); border: 1px solid var(--color-border);">No Ganadora</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; text-align: right; font-weight: 700; color: {{ $jugada->esGanadora() ? '#48bb78' : 'var(--color-muted)' }};">
                                    ${{ number_format($jugada->monto_premio, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($jugadasEscrutadas->hasPages())
                <div style="padding: 1rem 0 0 0;">
                    {{ $jugadasEscrutadas->links() }}
                </div>
            @endif
        @endif
    </div>
@endif
@endsection

