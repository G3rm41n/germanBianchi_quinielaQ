@extends('layouts.app')

@section('title', 'Mis Boletas y Balance')
@section('meta_description', 'Historial y balance financiero de tus jugadas en Agencia N°5801.')

@section('content')
<div class="container" style="padding-top: 2.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div>
            <h1 style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem;">Mis Boletas y Balance</h1>
            <p class="text-muted">Resumen financiero, desglose por categorías e historial de compras.</p>
        </div>
        <a href="{{ route('jugadas.create') }}" class="btn btn-primary btn-sm">
            Nueva Jugada
        </a>
    </div>

    {{-- 1. Tarjetas de Resumen Financiero --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        {{-- Saldo Disponible (Billetera) --}}
        <div id="billetera" class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-gold); background-color: var(--color-surface); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-gold); letter-spacing: 0.05em;">Saldo Disponible</span>
                </div>
                <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-gold); font-family: var(--font-display);">
                    ${{ number_format(auth()->user()->saldo, 2, ',', '.') }}
                </div>
                <span style="font-size: 0.75rem; color: var(--color-muted);">Billetera virtual activa</span>
            </div>
            <div style="margin-top: 0.75rem;">
                <button type="button" onclick="document.getElementById('modal-recarga').style.display='flex'" class="btn btn-outline btn-sm" style="width: 100%; font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                    Cargar Saldo
                </button>
            </div>
        </div>

        {{-- Total Invertido --}}
        <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-primary); background-color: var(--color-surface);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Total Invertido</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-text); font-family: var(--font-display);">
                ${{ number_format($totales['total_invertido'], 2, ',', '.') }}
            </div>
            <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $totales['total_jugadas'] }} boleta(s) registradas</span>
        </div>

        {{-- Total Ganancias --}}
        <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-success); background-color: var(--color-surface);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Total Ganancias</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-success); font-family: var(--font-display);">
                ${{ number_format($totales['total_ganancias'], 2, ',', '.') }}
            </div>
            <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $totales['jugadas_ganadoras'] }} boleta(s) premiada(s)</span>
        </div>

        {{-- Total Pérdidas --}}
        <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-danger); background-color: var(--color-surface);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Total Pérdidas</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-danger); font-family: var(--font-display);">
                ${{ number_format($totales['total_perdidas'], 2, ',', '.') }}
            </div>
            <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $totales['jugadas_no_ganadoras'] }} sin aciertos</span>
        </div>

        {{-- Balance Neto --}}
        @php
            $balance = $totales['balance_neto'];
            $colorBalance = $balance >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
            $signoBalance = $balance > 0 ? '+' : '';
        @endphp
        <div class="card" style="padding: 1.25rem; border-left: 4px solid {{ $colorBalance }}; background-color: var(--color-surface);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Balance Neto</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: {{ $colorBalance }}; font-family: var(--font-display);">
                {{ $signoBalance }}${{ number_format($balance, 2, ',', '.') }}
            </div>
            <span style="font-size: 0.75rem; color: var(--color-muted);">
                @if($balance > 0)
                    En superávit
                @elseif($balance < 0)
                    En saldo negativo
                @else
                    Sin diferencia
                @endif
            </span>
        </div>
    </div>

    {{-- 2. Tabla por Categorías de Jugadas Hechas --}}
    <div class="card" style="margin-bottom: 2.5rem; padding: 0; overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); background-color: rgba(255,255,255,0.02); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-family: var(--font-display); font-size: 1.15rem; margin: 0; color: var(--color-text);">
                Desglose por Categorías de Juego
            </h3>
            <span style="font-size: 0.8rem; color: var(--color-muted);">5 modalidades disponibles</span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--color-border); background-color: rgba(0,0,0,0.2);">
                        <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Categoría</th>
                        <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Jugadas</th>
                        <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Invertido ($)</th>
                        <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Ganancias ($)</th>
                        <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Pérdidas ($)</th>
                        <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Balance Neto ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sumaJugadas = 0;
                        $sumaInvertido = 0;
                        $sumaGanancias = 0;
                        $sumaPerdidas = 0;
                    @endphp
                    @foreach($categorias as $cat)
                        @php
                            $sumaJugadas += $cat['cantidad_jugadas'];
                            $sumaInvertido += $cat['total_invertido'];
                            $sumaGanancias += $cat['total_ganancias'];
                            $sumaPerdidas += $cat['total_perdidas'];
                            $netoCat = $cat['balance_neto'];
                            $colorCat = $netoCat > 0 ? 'var(--color-success)' : ($netoCat < 0 ? 'var(--color-danger)' : 'var(--color-muted)');
                            $signoCat = $netoCat > 0 ? '+' : '';
                        @endphp
                        <tr style="border-bottom: 1px solid var(--color-border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--color-surface2)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 1rem 1.25rem; font-weight: 600; color: var(--color-text);">
                                {{ $cat['nombre'] }}
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: center; color: var(--color-muted);">
                                {{ $cat['cantidad_jugadas'] }}
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-text);">
                                ${{ number_format($cat['total_invertido'], 2, ',', '.') }}
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right; font-weight: 600; color: var(--color-success);">
                                ${{ number_format($cat['total_ganancias'], 2, ',', '.') }}
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-danger);">
                                ${{ number_format($cat['total_perdidas'], 2, ',', '.') }}
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right; font-weight: 700; color: {{ $colorCat }};">
                                {{ $signoCat }}${{ number_format($netoCat, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $balanceGlobalTabla = $sumaGanancias - $sumaInvertido;
                        $colorGlobalTabla = $balanceGlobalTabla > 0 ? 'var(--color-success)' : ($balanceGlobalTabla < 0 ? 'var(--color-danger)' : 'var(--color-gold)');
                        $signoGlobal = $balanceGlobalTabla > 0 ? '+' : '';
                    @endphp
                    <tr style="background-color: rgba(255,255,255,0.04); font-weight: 700; border-top: 2px solid var(--color-border);">
                        <td style="padding: 1rem 1.25rem; text-transform: uppercase; color: var(--color-gold);">
                            Total Consolidado
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: center; color: var(--color-gold);">
                            {{ $sumaJugadas }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-text);">
                            ${{ number_format($sumaInvertido, 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-success);">
                            ${{ number_format($sumaGanancias, 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-danger);">
                            ${{ number_format($sumaPerdidas, 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; color: {{ $colorGlobalTabla }}; font-size: 1rem;">
                            {{ $signoGlobal }}${{ number_format($balanceGlobalTabla, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- 3. Historial Completo de Boletas y Filtros --}}
    <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--color-text);">
                    Historial de Boletas
                </h3>
                <span style="font-size: 0.8rem; color: var(--color-muted);">Filtrá por categoría y por fechas para consultar tus jugadas.</span>
            </div>
            @if(request()->hasAny(['categoria', 'fecha', 'fecha_desde', 'fecha_hasta']))
                <a href="{{ route('jugadas.index') }}" class="btn btn-ghost btn-sm" style="color: var(--color-danger); border: 1px solid rgba(248,81,73,0.3);">
                    Limpiar Filtros
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('jugadas.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) auto; gap: 0.75rem; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Categoría</label>
                <select name="categoria" class="form-input" style="width: 100%; padding: 0.45rem 0.75rem; font-size: 0.85rem;">
                    <option value="">Todas las categorías</option>
                    <option value="quiniela" {{ request('categoria') === 'quiniela' ? 'selected' : '' }}>Quiniela Tradicional</option>
                    <option value="quini6" {{ request('categoria') === 'quini6' ? 'selected' : '' }}>Quini 6</option>
                    <option value="lotoplus" {{ request('categoria') === 'lotoplus' ? 'selected' : '' }}>Loto Plus</option>
                    <option value="loto5" {{ request('categoria') === 'loto5' ? 'selected' : '' }}>Loto 5</option>
                    <option value="poceada" {{ request('categoria') === 'poceada' ? 'selected' : '' }}>Quiniela Poceada</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Fecha Puntual</label>
                <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-input" style="width: 100%; padding: 0.45rem 0.75rem; font-size: 0.85rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-input" style="width: 100%; padding: 0.45rem 0.75rem; font-size: 0.85rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-input" style="width: 100%; padding: 0.45rem 0.75rem; font-size: 0.85rem;">
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="padding: 0.45rem 1.25rem; font-size: 0.85rem; height: 38px;">
                    Aplicar
                </button>
            </div>
        </form>
    </div>

    @if($jugadas->isEmpty())
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            @if(request()->hasAny(['categoria', 'fecha', 'fecha_desde', 'fecha_hasta']))
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">No se encontraron boletas</h2>
                <p class="text-muted" style="margin-bottom: 2rem;">No hay jugadas que coincidan con los filtros seleccionados.</p>
                <a href="{{ route('jugadas.index') }}" class="btn btn-ghost">
                    Limpiar filtros
                </a>
            @else
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">Aún no hay boletas</h2>
                <p class="text-muted" style="margin-bottom: 2rem;">Todavía no registraste ninguna compra en la plataforma.</p>
                <a href="{{ route('jugadas.create') }}" class="btn btn-primary">
                    Armar mi primera boleta
                </a>
            @endif
        </div>
    @else
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--color-border); background-color: rgba(0,0,0,0.2);">
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Boleta ID / Fecha</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Apuestas</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Monto Invertido</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Resultado / Premio</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Estado Agencia</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jugadas as $jugada)
                            <tr style="border-bottom: 1px solid var(--color-border);">
                                <td style="padding: 1.25rem 1.5rem; font-size: 0.9rem;">
                                    <strong>#{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}</strong><br>
                                    <span class="text-muted">{{ $jugada->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td style="padding: 1.25rem 1.5rem; font-size: 0.85rem; color: var(--color-muted);">
                                    <ul style="margin: 0; padding-left: 1.25rem;">
                                        @foreach($jugada->detallesQuiniela as $q)
                                            <li>Quiniela: Nº {{ $q->numero }} (Pos: {{ $q->posicion }})</li>
                                        @endforeach
                                        @foreach($jugada->detallesQuini6 as $q)
                                            <li>Quini 6</li>
                                        @endforeach
                                        @foreach($jugada->detallesLotoPlus as $q)
                                            <li>Loto Plus</li>
                                        @endforeach
                                        @foreach($jugada->detallesLoto5 as $q)
                                            <li>Loto 5</li>
                                        @endforeach
                                        @foreach($jugada->detallesPoceada as $q)
                                            <li>Poceada</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--color-gold);">
                                    ${{ number_format($jugada->monto_total, 2, ',', '.') }}
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    @if($jugada->resultado === 'ganadora')
                                        <div style="display: inline-flex; flex-direction: column; gap: 0.25rem;">
                                            <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(63, 185, 80, 0.15); color: #3fb950; border: 1px solid rgba(63, 185, 80, 0.3);">
                                                GANADORA
                                            </span>
                                            <span style="font-size: 0.85rem; font-weight: 700; color: var(--color-success);">
                                                Premio: +${{ number_format($jugada->monto_premio, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    @elseif($jugada->resultado === 'no_ganadora')
                                        <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(248, 81, 73, 0.15); color: #f85149; border: 1px solid rgba(248, 81, 73, 0.3);">
                                            NO GANADORA
                                        </span>
                                    @else
                                        <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: rgba(210, 153, 34, 0.15); color: #d29922; border: 1px solid rgba(210, 153, 34, 0.3);">
                                            EN SORTEO
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    @if($jugada->estado === 'pendiente')
                                        <span class="badge badge-pendiente">Pendiente</span>
                                    @elseif($jugada->estado === 'procesado')
                                        <span class="badge badge-procesado">Procesado</span>
                                    @else
                                        <span class="badge badge-error">Error SMTP</span>
                                    @endif
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <a href="{{ route('jugadas.pdf', $jugada) }}" class="btn btn-ghost btn-sm" title="Descargar comprobante en PDF">
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--color-border);">
                {{ $jugadas->links() }}
            </div>
        </div>
    @endif
</div>

{{-- Modal Cargar Saldo --}}
<div id="modal-recarga" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
    <div class="card" style="max-width: 420px; width: 100%; border: 1px solid var(--color-border); background: var(--color-surface); box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem;">
            <h3 style="font-family: var(--font-display); font-size: 1.25rem; margin: 0; color: var(--color-text);">Cargar Saldo a tu Billetera</h3>
            <button type="button" onclick="document.getElementById('modal-recarga').style.display='none'" style="background: none; border: none; color: var(--color-muted); font-size: 1.25rem; cursor: pointer;">&times;</button>
        </div>
        <p style="font-size: 0.85rem; color: var(--color-muted); margin-bottom: 1.25rem;">
            Seleccioná o ingresá un importe para simular la recarga inmediata de fondos en tu cuenta:
        </p>
        <form method="POST" action="{{ route('billetera.recargar') }}">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                <button type="button" class="btn btn-ghost btn-sm" style="border: 1px solid var(--color-border);" onclick="document.getElementById('input-recarga').value=5000">+$5.000</button>
                <button type="button" class="btn btn-ghost btn-sm" style="border: 1px solid var(--color-border);" onclick="document.getElementById('input-recarga').value=10000">+$10.000</button>
                <button type="button" class="btn btn-ghost btn-sm" style="border: 1px solid var(--color-border);" onclick="document.getElementById('input-recarga').value=20000">+$20.000</button>
                <button type="button" class="btn btn-ghost btn-sm" style="border: 1px solid var(--color-border);" onclick="document.getElementById('input-recarga').value=50000">+$50.000</button>
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="input-recarga" class="form-label">Monto personalizado ($)</label>
                <input type="number" id="input-recarga" name="monto" class="form-input" min="100" max="1000000" step="100" value="10000" required>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-recarga').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">Confirmar Recarga</button>
            </div>
        </form>
    </div>
</div>
@endsection
