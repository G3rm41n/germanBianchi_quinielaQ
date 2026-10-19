@extends('layouts.admin')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom: 1rem;">&larr; Volver al listado</a>
    <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold);">Jugadas de: {{ $user->name }}</h1>
    <p style="color: var(--color-muted); font-size: 0.9rem;">Email: {{ $user->email }} | DNI: {{ $user->dni }}</p>
</div>

{{-- 1. Tarjetas de Resumen Financiero del Cliente --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    {{-- Total Invertido --}}
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-primary); background-color: var(--color-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Total Invertido</span>
            <span style="font-size: 1.25rem;">🪙</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-text); font-family: var(--font-display);">
            ${{ number_format($totales['total_invertido'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $totales['total_jugadas'] }} boletas</span>
    </div>

    {{-- Total Ganancias --}}
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-success); background-color: var(--color-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Ganancias del Cliente</span>
            <span style="font-size: 1.25rem;">🏆</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-success); font-family: var(--font-display);">
            ${{ number_format($totales['total_ganancias'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $totales['jugadas_ganadoras'] }} boletas premiadas</span>
    </div>

    {{-- Total Pérdidas --}}
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-danger); background-color: var(--color-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Pérdidas del Cliente</span>
            <span style="font-size: 1.25rem;">📉</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-danger); font-family: var(--font-display);">
            ${{ number_format($totales['total_perdidas'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $totales['jugadas_no_ganadoras'] }} boletas sin aciertos</span>
    </div>

    {{-- Balance Neto del Cliente --}}
    @php
        $balUser = $totales['balance_neto'];
        $colorBalUser = $balUser >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
        $signoBalUser = $balUser > 0 ? '+' : '';
    @endphp
    <div class="card" style="padding: 1.25rem; border-left: 4px solid {{ $colorBalUser }}; background-color: var(--color-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Balance Neto Cliente</span>
            <span style="font-size: 1.25rem;">⚖️</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 700; color: {{ $colorBalUser }}; font-family: var(--font-display);">
            {{ $signoBalUser }}${{ number_format($balUser, 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">
            @if($balUser > 0)
                🟢 Cliente en ganancia
            @elseif($balUser < 0)
                🔴 Cliente en pérdida
            @else
                ⚪ En equilibrio
            @endif
        </span>
    </div>
</div>

{{-- 2. Desglose de Categorías del Cliente --}}
<div class="card" style="margin-bottom: 2rem; padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); background-color: rgba(255,255,255,0.02); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-family: var(--font-display); font-size: 1.15rem; margin: 0; color: var(--color-text);">
            📊 Desglose por Categorías de este Cliente
        </h3>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--color-border); background-color: rgba(0,0,0,0.2);">
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Categoría</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Jugadas</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Invertido ($)</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Ganancias ($)</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Pérdidas ($)</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Balance Neto ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorias as $cat)
                    @php
                        $netoCat = $cat['balance_neto'];
                        $colorNetoCat = $netoCat > 0 ? 'var(--color-success)' : ($netoCat < 0 ? 'var(--color-danger)' : 'var(--color-muted)');
                        $signoCat = $netoCat > 0 ? '+' : '';
                    @endphp
                    <tr style="border-bottom: 1px solid var(--color-border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--color-surface2)'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 1rem 1.25rem; font-weight: 600; color: var(--color-text);">
                            <span style="margin-right: 0.5rem; font-size: 1.1rem;">{{ $cat['icono'] }}</span>
                            {{ $cat['nombre'] }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: center; color: var(--color-muted);">
                            {{ $cat['cantidad_jugadas'] }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-text);">
                            ${{ number_format($cat['total_invertido'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-success); font-weight: 600;">
                            ${{ number_format($cat['total_ganancias'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-danger);">
                            ${{ number_format($cat['total_perdidas'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; font-weight: 700; color: {{ $colorNetoCat }};">
                            {{ $signoCat }}${{ number_format($netoCat, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- 3. Historial de Jugadas del Cliente --}}
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem;">
        <h3 style="font-family: var(--font-display); font-size: 1.25rem; color: var(--color-text); margin: 0;">Historial Completo</h3>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; color: var(--color-text);">
            <thead>
                <tr>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">ID / Fecha</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">Apuestas</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: right; color: var(--color-muted);">Monto</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: center; color: var(--color-muted);">Resultado / Premio</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: center; color: var(--color-muted);">Estado</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: right; color: var(--color-muted);">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jugadas as $jugada)
                <tr style="border-bottom: 1px solid var(--color-border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--color-surface2)'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 1rem;">
                        <strong style="color: var(--color-primary);">#{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}</strong><br>
                        <span style="color: var(--color-muted); font-size: 0.75rem;">{{ $jugada->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                    <td style="padding: 1rem;">
                        <ul style="margin: 0; padding-left: 1rem; color: var(--color-muted);">
                            @foreach($jugada->detallesQuiniela as $q)
                                <li>Quiniela: Nº {{ $q->numero }} (Pos: {{ $q->posicion }})</li>
                            @endforeach
                            @foreach($jugada->detallesQuini6 as $q)
                                <li>Quini 6: {{ implode(', ', $q->numeros) }}</li>
                            @endforeach
                            @foreach($jugada->detallesLotoPlus as $q)
                                <li>Loto Plus: {{ implode(', ', $q->numeros) }} (+{{ $q->numero_plus }})</li>
                            @endforeach
                            @foreach($jugada->detallesLoto5 as $q)
                                <li>Loto 5: {{ implode(', ', $q->numeros) }}</li>
                            @endforeach
                            @foreach($jugada->detallesPoceada as $q)
                                <li>Poceada: {{ implode(', ', $q->numeros) }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td style="padding: 1rem; text-align: right; font-weight: 700; color: var(--color-gold); font-size: 0.95rem;">
                        ${{ number_format($jugada->monto_total, 2, ',', '.') }}
                    </td>
                    <td style="padding: 1rem; text-align: center;">
                        <form action="{{ route('admin.jugadas.resultado', $jugada) }}" method="POST" style="display: flex; flex-direction: column; gap: 0.35rem; align-items: center;">
                            @csrf
                            @method('PATCH')
                            <select name="resultado" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: auto;" onchange="const wrap = this.parentElement.querySelector('.premio-wrap'); if(this.value==='ganadora'){ wrap.style.display='flex'; } else { wrap.style.display='none'; this.form.submit(); }">
                                <option value="pendiente_sorteo" {{ $jugada->resultado === 'pendiente_sorteo' ? 'selected' : '' }}>⏳ En Sorteo</option>
                                <option value="ganadora" {{ $jugada->resultado === 'ganadora' ? 'selected' : '' }}>🏆 Ganadora</option>
                                <option value="no_ganadora" {{ $jugada->resultado === 'no_ganadora' ? 'selected' : '' }}>❌ No Ganadora</option>
                            </select>

                            <div class="premio-wrap" style="display: {{ $jugada->resultado === 'ganadora' ? 'flex' : 'none' }}; gap: 0.25rem; align-items: center; margin-top: 0.25rem;">
                                <input type="number" step="0.01" min="0" name="monto_premio" class="form-input" value="{{ $jugada->monto_premio ?: '' }}" placeholder="$ Premio" style="width: 85px; padding: 0.2rem 0.4rem; font-size: 0.75rem;">
                                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Guardar Premio">💾</button>
                            </div>

                            @if($jugada->resultado === 'ganadora')
                                <span style="font-size: 0.75rem; color: var(--color-success); font-weight: 700;">
                                    Premio: ${{ number_format($jugada->monto_premio, 2, ',', '.') }}
                                </span>
                            @endif
                        </form>
                    </td>
                    <td style="padding: 1rem; text-align: center;">
                        <span class="badge badge-{{ $jugada->estado }}">
                            {{ strtoupper($jugada->estado) }}
                        </span>
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                            <a href="{{ route('jugadas.pdf', $jugada) }}" target="_blank" class="btn btn-outline btn-sm">PDF</a>
                            
                            <form action="{{ route('admin.jugadas.estado', $jugada) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="estado" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onchange="this.form.submit()">
                                    <option value="pendiente" {{ $jugada->estado == 'pendiente' ? 'selected' : '' }}>Marcar Pendiente</option>
                                    <option value="procesado" {{ $jugada->estado == 'procesado' ? 'selected' : '' }}>Marcar Procesado</option>
                                    <option value="error_envio" {{ $jugada->estado == 'error_envio' ? 'selected' : '' }}>Marcar Error</option>
                                </select>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem;">
                        <span style="font-size: 2.5rem; opacity: 0.5;">📭</span><br>
                        <span style="color: var(--color-muted); display: block; margin-top: 1rem;">El cliente no tiene jugadas registradas.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 1.5rem;">
        {{ $jugadas->links() }}
    </div>
</div>
@endsection
