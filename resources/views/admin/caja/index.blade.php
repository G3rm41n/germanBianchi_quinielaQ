@extends('layouts.admin')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold); margin: 0;">
            Cierre de Caja y Arqueo Diario
        </h1>
        <p style="color: var(--color-muted); font-size: 0.9rem; margin-top: 0.25rem;">
            Control estricto de ingresos por apuestas, egresos por premios y balance neto contable de la agencia.
        </p>
    </div>
    
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <a href="{{ route('admin.caja.pdf', ['fecha' => $fecha]) }}" class="btn btn-primary" target="_blank" style="display: inline-flex; align-items: center; gap: 0.5rem;">
            📄 Descargar Planilla PDF (A4)
        </a>
    </div>
</div>

{{-- 1. Selector de Fecha de Arqueo --}}
<div class="card" style="padding: 1.25rem; margin-bottom: 2rem;">
    <form method="GET" action="{{ route('admin.caja.index') }}" style="display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap;">
        <div>
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">
                Fecha a Auditar
            </label>
            <input type="date" name="fecha" value="{{ $fecha }}" class="form-input" style="padding: 0.45rem 0.75rem; font-size: 0.9rem;">
        </div>

        <button type="submit" class="btn btn-outline" style="padding: 0.45rem 1.25rem; font-size: 0.9rem;">
            🔍 Consultar Fecha
        </button>

        @if($fecha !== date('Y-m-d'))
            <a href="{{ route('admin.caja.index') }}" class="btn btn-ghost" style="padding: 0.45rem 0.75rem; font-size: 0.9rem;">
                Ver Hoy
            </a>
        @endif
    </form>
</div>

{{-- 2. Tarjetas Contables Principales (Estrictamente: Recaudado, Premios, Balance Neto) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    {{-- Total Recaudado --}}
    <div class="card" style="padding: 1.5rem; border-left: 4px solid var(--color-primary); background-color: var(--color-surface);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em; margin-bottom: 0.35rem;">
            Total Recaudado (Apuestas)
        </div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--color-text); font-family: var(--font-display);">
            ${{ number_format($arqueo['total_recaudado'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $arqueo['total_boletas'] }} boletas emitidas el {{ $arqueo['fecha']->format('d/m/Y') }}</span>
    </div>

    {{-- Premios Pagados --}}
    <div class="card" style="padding: 1.5rem; border-left: 4px solid var(--color-danger); background-color: var(--color-surface);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em; margin-bottom: 0.35rem;">
            Premios Pagados (Egresos)
        </div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--color-danger); font-family: var(--font-display);">
            ${{ number_format($arqueo['total_premios'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $arqueo['boletas_ganadoras'] }} boletas premiadas</span>
    </div>

    {{-- Balance Neto --}}
    @php
        $esPositivo = $arqueo['balance_neto'] >= 0;
        $colorBalance = $esPositivo ? 'var(--color-success)' : 'var(--color-danger)';
        $signo = $arqueo['balance_neto'] > 0 ? '+' : '';
    @endphp
    <div class="card" style="padding: 1.5rem; border-left: 4px solid {{ $colorBalance }}; background-color: var(--color-surface);">
        <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em; margin-bottom: 0.35rem;">
            Balance Neto de Caja
        </div>
        <div style="font-size: 1.8rem; font-weight: 800; color: {{ $colorBalance }}; font-family: var(--font-display);">
            {{ $signo }}${{ number_format($arqueo['balance_neto'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">
            {{ $esPositivo ? 'Saldo positivo a favor de la agencia' : 'Déficit operativo por premios mayores' }}
        </span>
    </div>
</div>

{{-- 3. Desglose Contable por Juego --}}
<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 2rem;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); background-color: rgba(255, 255, 255, 0.02);">
        <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-gold); margin: 0;">
            Matriz de Ventas y Liquidaciones por Modalidad
        </h3>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--color-border); text-align: left;">
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600;">Modalidad de Juego</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600; text-align: center;">Cantidad Boletas</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600; text-align: right;">Total Recaudado</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600; text-align: right;">Premios Pagados</th>
                    <th style="padding: 1rem; color: var(--color-muted); font-weight: 600; text-align: right;">Balance Neto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($arqueo['desglose_juegos'] as $key => $d)
                    @php
                        $bal = $d['balance'];
                        $colorBal = $bal >= 0 ? '#48bb78' : '#e53e3e';
                        $sig = $bal > 0 ? '+' : '';
                    @endphp
                    <tr style="border-bottom: 1px solid var(--color-border);">
                        <td style="padding: 1rem; font-weight: 600; color: var(--color-text);">
                            {{ $d['nombre'] }}
                        </td>
                        <td style="padding: 1rem; text-align: center; color: var(--color-muted);">
                            {{ $d['cantidad'] }}
                        </td>
                        <td style="padding: 1rem; text-align: right; font-weight: 600; color: var(--color-text);">
                            ${{ number_format($d['recaudado'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem; text-align: right; font-weight: 600; color: {{ $d['premios'] > 0 ? 'var(--color-danger)' : 'var(--color-muted)' }};">
                            ${{ number_format($d['premios'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem; text-align: right; font-weight: 700; color: {{ $colorBal }};">
                            {{ $sig }}${{ number_format($bal, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: rgba(255, 255, 255, 0.03); font-weight: 700; border-top: 2px solid var(--color-border);">
                    <td style="padding: 1rem; color: var(--color-gold);">TOTALES CONSOLIDADOS</td>
                    <td style="padding: 1rem; text-align: center; color: var(--color-gold);">{{ $arqueo['total_boletas'] }}</td>
                    <td style="padding: 1rem; text-align: right; color: var(--color-text);">${{ number_format($arqueo['total_recaudado'], 2, ',', '.') }}</td>
                    <td style="padding: 1rem; text-align: right; color: var(--color-danger);">${{ number_format($arqueo['total_premios'], 2, ',', '.') }}</td>
                    <td style="padding: 1rem; text-align: right; color: {{ $colorBalance }}; font-size: 1rem;">
                        {{ $signo }}${{ number_format($arqueo['balance_neto'], 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- 4. Listado Pormenorizado de Boletas del Día --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); background-color: rgba(255, 255, 255, 0.02); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-text); margin: 0;">
            Boletas Registradas ({{ $arqueo['jugadas']->count() }})
        </h3>
        <span style="font-size: 0.8rem; color: var(--color-muted);">
            Procesadas: <strong>{{ $arqueo['boletas_procesadas'] }}</strong> | Pendientes: <strong>{{ $arqueo['boletas_pendientes'] }}</strong>
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--color-border); text-align: left;">
                    <th style="padding: 0.75rem 1rem; color: var(--color-muted);">Ticket / Hora</th>
                    <th style="padding: 0.75rem 1rem; color: var(--color-muted);">Cliente</th>
                    <th style="padding: 0.75rem 1rem; color: var(--color-muted);">Estado Despacho</th>
                    <th style="padding: 0.75rem 1rem; color: var(--color-muted); text-align: center;">Resultado</th>
                    <th style="padding: 0.75rem 1rem; color: var(--color-muted); text-align: right;">Premio</th>
                    <th style="padding: 0.75rem 1rem; color: var(--color-muted); text-align: right;">Monto Apostado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($arqueo['jugadas'] as $j)
                    <tr style="border-bottom: 1px solid var(--color-border);">
                        <td style="padding: 0.75rem 1rem;">
                            <strong>#{{ str_pad($j->id, 8, '0', STR_PAD_LEFT) }}</strong>
                            <div style="font-size: 0.75rem; color: var(--color-muted);">{{ $j->created_at->format('H:i:s') }}</div>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <div>{{ $j->user?->name ?? 'Usuario' }}</div>
                            <div style="font-size: 0.75rem; color: var(--color-muted);">DNI {{ $j->user?->dni }}</div>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if($j->estado === 'procesado')
                                <span class="badge badge-procesado" style="font-size: 0.75rem;">Procesada</span>
                            @elseif($j->estado === 'pendiente')
                                <span class="badge badge-pendiente" style="font-size: 0.75rem;">Pendiente</span>
                            @else
                                <span class="badge" style="font-size: 0.75rem; background: rgba(229,62,62,0.15); color: #e53e3e;">Error</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            @if($j->resultado === 'ganadora')
                                <span class="badge" style="background: rgba(72,187,120,0.15); color: #48bb78; font-size: 0.75rem;">Ganadora</span>
                            @elseif($j->resultado === 'no_ganadora')
                                <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--color-muted); font-size: 0.75rem;">No Ganadora</span>
                            @else
                                <span class="badge" style="background: rgba(212,160,23,0.1); color: var(--color-gold); font-size: 0.75rem;">En Sorteo</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: {{ $j->monto_premio > 0 ? '#48bb78' : 'var(--color-muted)' }};">
                            ${{ number_format($j->monto_premio, 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: var(--color-gold);">
                            ${{ number_format($j->monto_total, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 2.5rem; text-align: center; color: var(--color-muted);">
                            No hay boletas registradas en la fecha seleccionada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

