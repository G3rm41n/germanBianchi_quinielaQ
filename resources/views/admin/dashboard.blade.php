@extends('layouts.admin')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold);">Panel de Control</h1>
        <p style="color: var(--color-muted); font-size: 0.9rem;">Balance financiero, gestión de precios, escrutinio de jugadas y monitoreo.</p>
    </div>
    
    <form action="{{ route('admin.limpiar') }}" method="POST" onsubmit="return confirm('ATENCIÓN: Esto borrará permanentemente TODAS las jugadas y logs. ¿Deseas continuar?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">
            Limpiar Base de Datos
        </button>
    </form>
</div>

{{-- 1. Tarjetas de Resumen Financiero de la Agencia --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    {{-- Recaudación Total --}}
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-primary); background-color: var(--color-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Recaudación Bruta</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-text); font-family: var(--font-display);">
            ${{ number_format($totales['total_invertido'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $totales['total_jugadas'] }} boletas de clientes</span>
    </div>

    {{-- Premios Abonados --}}
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-danger); background-color: var(--color-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Premios Liquidados</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-danger); font-family: var(--font-display);">
            ${{ number_format($totales['total_ganancias'], 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">{{ $totales['jugadas_ganadoras'] }} boletas premiadas</span>
    </div>

    {{-- Ganancia Neta Agencia --}}
    @php
        $margenAgencia = $totales['total_invertido'] - $totales['total_ganancias'];
        $colorMargen = $margenAgencia >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
        $signoMargen = $margenAgencia > 0 ? '+' : '';
    @endphp
    <div class="card" style="padding: 1.25rem; border-left: 4px solid {{ $colorMargen }}; background-color: var(--color-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Ganancia Neta Agencia</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 700; color: {{ $colorMargen }}; font-family: var(--font-display);">
            {{ $signoMargen }}${{ number_format($margenAgencia, 2, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">
            @if($margenAgencia > 0)
                Utilidad a favor
            @elseif($margenAgencia < 0)
                Saldo negativo
            @else
                En equilibrio
            @endif
        </span>
    </div>

    {{-- En Espera de Escrutinio --}}
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--color-warning); background-color: var(--color-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); letter-spacing: 0.05em;">Pendientes Sorteo</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 700; color: var(--color-warning); font-family: var(--font-display);">
            {{ $totales['jugadas_pendientes'] }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-muted);">Boletas en juego</span>
    </div>
</div>

{{-- 2. Tabla Analítica de Categorías de Juego (Agencia) --}}
<div class="card" style="margin-bottom: 2rem; padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); background-color: rgba(255,255,255,0.02); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-family: var(--font-display); font-size: 1.15rem; margin: 0; color: var(--color-text);">
            Rentabilidad por Categoría de Juego
        </h3>
        <span style="font-size: 0.8rem; color: var(--color-muted);">Métricas consolidadas de la agencia</span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--color-border); background-color: rgba(0,0,0,0.2);">
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Categoría</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Jugadas</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Recaudación ($)</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Premios Pagados ($)</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Ganancia Agencia ($)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sumAgenciaJugadas = 0;
                    $sumAgenciaRecaudacion = 0;
                    $sumAgenciaPremios = 0;
                @endphp
                @foreach($categorias as $cat)
                    @php
                        $sumAgenciaJugadas += $cat['cantidad_jugadas'];
                        $sumAgenciaRecaudacion += $cat['total_invertido'];
                        $sumAgenciaPremios += $cat['total_ganancias'];
                        $margenCat = $cat['total_invertido'] - $cat['total_ganancias'];
                        $colorMargenCat = $margenCat > 0 ? 'var(--color-success)' : ($margenCat < 0 ? 'var(--color-danger)' : 'var(--color-muted)');
                        $signoMargenCat = $margenCat > 0 ? '+' : '';
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
                        <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-danger);">
                            ${{ number_format($cat['total_ganancias'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; font-weight: 700; color: {{ $colorMargenCat }};">
                            {{ $signoMargenCat }}${{ number_format($margenCat, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $margenTotalAgencia = $sumAgenciaRecaudacion - $sumAgenciaPremios;
                    $colorMargenTotal = $margenTotalAgencia > 0 ? 'var(--color-success)' : ($margenTotalAgencia < 0 ? 'var(--color-danger)' : 'var(--color-gold)');
                    $signoTotalAgencia = $margenTotalAgencia > 0 ? '+' : '';
                @endphp
                <tr style="background-color: rgba(255,255,255,0.04); font-weight: 700; border-top: 2px solid var(--color-border);">
                    <td style="padding: 1rem 1.25rem; text-transform: uppercase; color: var(--color-gold);">
                        Total Agencia
                    </td>
                    <td style="padding: 1rem 1.25rem; text-align: center; color: var(--color-gold);">
                        {{ $sumAgenciaJugadas }}
                    </td>
                    <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-text);">
                        ${{ number_format($sumAgenciaRecaudacion, 2, ',', '.') }}
                    </td>
                    <td style="padding: 1rem 1.25rem; text-align: right; color: var(--color-danger);">
                        ${{ number_format($sumAgenciaPremios, 2, ',', '.') }}
                    </td>
                    <td style="padding: 1rem 1.25rem; text-align: right; color: {{ $colorMargenTotal }}; font-size: 0.95rem;">
                        {{ $signoTotalAgencia }}${{ number_format($margenTotalAgencia, 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- 3. Gestión de Precios --}}
<div class="card" style="margin-bottom: 2rem;">
    <h3 style="font-family: var(--font-display); font-size: 1.25rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; color: var(--color-text);">Gestión de Precios (Por jugada)</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
        @foreach(['quini6' => 'Quini 6', 'lotoplus' => 'Loto Plus', 'loto5' => 'Loto 5', 'poceada' => 'Poceada'] as $key => $name)
            <div style="background-color: var(--color-surface2); padding: 1.25rem; border-radius: 8px; border: 1px solid var(--color-border);">
                <label style="display: block; font-weight: 600; margin-bottom: 0.75rem; color: var(--color-text);">{{ $name }}</label>
                <form action="{{ route('admin.precios.update', $key) }}" method="POST" style="display: flex; gap: 0.5rem;">
                    @csrf
                    @method('PUT')
                    <div style="position: relative; flex: 1;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-gold); font-weight: bold;">$</span>
                        <input type="number" name="valor" class="form-input" value="{{ $preciosMap[$key] ?? 0 }}" required style="padding-left: 2rem;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 0 1rem;">Guardar</button>
                </form>
            </div>
        @endforeach
    </div>
</div>

{{-- 4. Historial de Jugadas y Escrutinio --}}
<div class="card" style="margin-bottom: 2rem;">
    @php
        $filtrosActivosAdmin = collect(['categoria', 'fecha', 'fecha_desde', 'fecha_hasta', 'estado', 'resultado'])
            ->filter(fn($key) => request()->filled($key))
            ->count();
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
        <h3 style="font-family: var(--font-display); font-size: 1.25rem; color: var(--color-text); margin: 0;">Historial de Jugadas y Escrutinio</h3>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <form action="{{ route('admin.jugadas.bulk_hoy') }}" method="POST" onsubmit="return confirm('¿Marcar como PROCESADAS todas las boletas pendientes emitidas hoy? Se notificará a los usuarios por correo.');" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; color: var(--color-gold); border-color: var(--color-gold);">
                    ⚡ Procesar todas las de hoy
                </button>
            </form>

            @if($filtrosActivosAdmin > 0)
                <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost btn-sm" style="color: var(--color-danger); border: 1px solid rgba(248,81,73,0.35);">
                    Limpiar Filtros ({{ $filtrosActivosAdmin }})
                </a>
            @endif

            <button type="button" onclick="toggleMenuFiltrosAdmin()" class="btn {{ $filtrosActivosAdmin > 0 ? 'btn-primary' : 'btn-outline' }} btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                <span>Filtrar Jugadas</span>
                @if($filtrosActivosAdmin > 0)
                    <span style="background: rgba(255,255,255,0.25); border-radius: 9999px; padding: 0.1rem 0.45rem; font-size: 0.75rem;">{{ $filtrosActivosAdmin }}</span>
                @endif
                <span id="icono-chevron-admin" style="font-size: 0.75rem; transition: transform 0.2s;">{{ $filtrosActivosAdmin > 0 ? '▲' : '▼' }}</span>
            </button>
        </div>
    </div>

    <div id="menu-filtros-admin" style="display: {{ $filtrosActivosAdmin > 0 ? 'block' : 'none' }}; margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('admin.dashboard') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) auto; gap: 0.75rem; align-items: flex-end; background: var(--color-surface2); padding: 1.25rem; border-radius: 8px; border: 1px solid var(--color-border);">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Categoría</label>
                <select name="categoria" class="form-input" style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.8rem;">
                    <option value="">Todas las categorías</option>
                    <option value="quiniela" {{ request('categoria') === 'quiniela' ? 'selected' : '' }}>Quiniela</option>
                    <option value="quini6" {{ request('categoria') === 'quini6' ? 'selected' : '' }}>Quini 6</option>
                    <option value="lotoplus" {{ request('categoria') === 'lotoplus' ? 'selected' : '' }}>Loto Plus</option>
                    <option value="loto5" {{ request('categoria') === 'loto5' ? 'selected' : '' }}>Loto 5</option>
                    <option value="poceada" {{ request('categoria') === 'poceada' ? 'selected' : '' }}>Poceada</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Fecha Puntual</label>
                <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-input" style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.8rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-input" style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.8rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-input" style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.8rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Envío</label>
                <select name="estado" class="form-input" style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.8rem;">
                    <option value="">Todos los envíos</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                    <option value="procesado" {{ request('estado') === 'procesado' ? 'selected' : '' }}>Procesados</option>
                    <option value="error_envio" {{ request('estado') === 'error_envio' ? 'selected' : '' }}>Error SMTP</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Resultado</label>
                <select name="resultado" class="form-input" style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.8rem;">
                    <option value="">Todos los resultados</option>
                    <option value="pendiente_sorteo" {{ request('resultado') === 'pendiente_sorteo' ? 'selected' : '' }}>En Sorteo</option>
                    <option value="ganadora" {{ request('resultado') === 'ganadora' ? 'selected' : '' }}>Ganadoras</option>
                    <option value="no_ganadora" {{ request('resultado') === 'no_ganadora' ? 'selected' : '' }}>No Ganadoras</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary btn-sm" style="height: 36px; padding: 0 1.25rem;">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleMenuFiltrosAdmin() {
            const menu = document.getElementById('menu-filtros-admin');
            const chevron = document.getElementById('icono-chevron-admin');
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
                if (chevron) chevron.textContent = '▲';
            } else {
                menu.style.display = 'none';
                if (chevron) chevron.textContent = '▼';
            }
        }
    </script>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; color: var(--color-text);">
            <thead>
                <tr>
                    <th style="padding: 1rem 0.5rem 1rem 1rem; border-bottom: 1px solid var(--color-border); text-align: center; width: 35px;">
                        <input type="checkbox" id="check-all-jugadas" title="Seleccionar todas las visibles" style="cursor: pointer; width: 16px; height: 16px;">
                    </th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">ID / Fecha</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">Cliente</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">Apuestas</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: right; color: var(--color-muted);">Monto</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: center; color: var(--color-muted);">Resultado / Premio</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: center; color: var(--color-muted);">Estado Agencia</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: right; color: var(--color-muted);">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jugadas as $jugada)
                <tr style="border-bottom: 1px solid var(--color-border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--color-surface2)'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 1rem 0.5rem 1rem 1rem; text-align: center;">
                        <input type="checkbox"
                               class="check-jugada"
                               value="{{ $jugada->id }}"
                               data-monto="{{ $jugada->monto_total }}"
                               data-texto="{{ e($jugada->texto_transcripcion) }}"
                               style="cursor: pointer; width: 16px; height: 16px;">
                    </td>
                    <td style="padding: 1rem;">
                        <strong style="color: var(--color-primary);">#{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}</strong><br>
                        <span style="color: var(--color-muted); font-size: 0.75rem;">{{ $jugada->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                    <td style="padding: 1rem;">
                        <strong>{{ $jugada->user->name }}</strong><br>
                        <span style="color: var(--color-muted); font-size: 0.75rem;">{{ $jugada->user->email }}<br>DNI: {{ $jugada->user->dni }}</span>
                    </td>
                    <td style="padding: 1rem;">
                        <ul style="margin: 0; padding-left: 1rem; color: var(--color-muted);">
                            @foreach($jugada->detallesQuiniela as $q)
                                <li>Quiniela ({{ ucfirst($q->jurisdiccion) }} - {{ ucfirst($q->turno ?? 'matutina') }}): Nº {{ $q->numero }} (Pos: {{ $q->posicion }})</li>
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
                        {{-- Formulario de Escrutinio Rápido --}}
                        <form action="{{ route('admin.jugadas.resultado', $jugada) }}" method="POST" style="display: flex; flex-direction: column; gap: 0.35rem; align-items: center;">
                            @csrf
                            @method('PATCH')
                            <select name="resultado" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: auto;" onchange="const wrap = this.parentElement.querySelector('.premio-wrap'); if(this.value==='ganadora'){ wrap.style.display='flex'; } else { wrap.style.display='none'; this.form.submit(); }">
                                <option value="pendiente_sorteo" {{ $jugada->resultado === 'pendiente_sorteo' ? 'selected' : '' }}>En Sorteo</option>
                                <option value="ganadora" {{ $jugada->resultado === 'ganadora' ? 'selected' : '' }}>Ganadora</option>
                                <option value="no_ganadora" {{ $jugada->resultado === 'no_ganadora' ? 'selected' : '' }}>No Ganadora</option>
                            </select>

                            <div class="premio-wrap" style="display: {{ $jugada->resultado === 'ganadora' ? 'flex' : 'none' }}; gap: 0.25rem; align-items: center; margin-top: 0.25rem;">
                                <input type="number" step="0.01" min="0" name="monto_premio" class="form-input" value="{{ $jugada->monto_premio ?: '' }}" placeholder="$ Premio" style="width: 85px; padding: 0.2rem 0.4rem; font-size: 0.75rem;">
                                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Guardar Premio">Guardar</button>
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
                            <div style="display: flex; gap: 0.35rem; align-items: center;">
                                <button type="button" class="btn btn-ghost btn-sm btn-copiar-linea" data-texto="{{ e($jugada->texto_transcripcion) }}" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border: 1px solid var(--color-border); color: var(--color-gold);" title="Copiar datos para terminal de lotería">
                                    📋 Copiar
                                </button>
                                <a href="{{ route('jugadas.pdf', $jugada) }}" target="_blank" class="btn btn-outline btn-sm">PDF</a>
                            </div>
                            
                            <form action="{{ route('admin.jugadas.estado', $jugada) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="estado" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onchange="this.form.submit()">
                                    <option value="pendiente" {{ $jugada->estado == 'pendiente' ? 'selected' : '' }}>Marcar Pendiente</option>
                                    <option value="procesado" {{ $jugada->estado == 'procesado' ? 'selected' : '' }}>Marcar Procesado</option>
                                    <option value="error_envio" {{ $jugada->estado == 'error_envio' ? 'selected' : '' }}>Marcar Error</option>
                                </select>
                            </form>

                            @if($jugada->estado === 'error_envio')
                            <form action="{{ route('admin.jugadas.reenviar', $jugada) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Reenviar Mail</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem;">
                        <span style="color: var(--color-muted); display: block; margin-top: 1rem;">No hay jugadas registradas en el sistema.</span>
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

{{-- 5. Logs de Auditoría --}}
<div class="card">
    <h3 style="font-family: var(--font-display); font-size: 1.25rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; color: var(--color-text);">Logs de Auditoría (Últimos 50 eventos)</h3>
    <div style="max-height: 400px; overflow-y: auto; background-color: rgba(0,0,0,0.3); color: var(--color-text); padding: 1.25rem; border-radius: 8px; font-family: monospace; font-size: 0.8rem; border: 1px solid var(--color-border);">
        @forelse($auditLogs as $log)
            <div style="margin-bottom: 0.75rem; border-bottom: 1px dashed var(--color-border); padding-bottom: 0.75rem;">
                <span style="color: var(--color-muted);">[{{ $log->created_at->format('Y-m-d H:i:s') }}]</span> 
                <strong style="color: var(--color-primary); margin: 0 0.5rem;">{{ strtoupper($log->accion) }}</strong> 
                @if($log->jugada_id) <span style="color: var(--color-gold);">[Jugada ID: {{ $log->jugada_id }}]</span> @endif
                <br>
                <span style="color: #a5b4fc;">{{ $log->descripcion ?? $log->detalles }}</span>
            </div>
        @empty
            <div style="color: var(--color-muted); text-align: center; padding: 2rem 0;">No hay registros de auditoría en el sistema.</div>
        @endforelse
    </div>
</div>

{{-- Barra Flotante de Acciones en Lote (Sticky Bulk Bar) --}}
<div id="bulk-bar" style="display: none; position: fixed; bottom: 25px; left: 50%; transform: translateX(-50%); z-index: 1000; background: #1e293b; border: 2px solid var(--color-gold); border-radius: 12px; padding: 0.85rem 1.5rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.6); align-items: center; gap: 1.25rem; flex-wrap: wrap;">
    <div style="font-size: 0.9rem; font-weight: 700; color: var(--color-text);">
        <span id="bulk-count">0</span> boletas seleccionadas (<span id="bulk-total" style="color: var(--color-gold);">$0,00</span>)
    </div>
    <div style="display: flex; gap: 0.6rem; align-items: center;">
        <form id="form-bulk-procesar" method="POST" action="{{ route('admin.jugadas.bulk') }}" onsubmit="return confirm('¿Marcar las boletas seleccionadas como PROCESADAS? Se notificará a los usuarios por correo.');">
            @csrf
            <input type="hidden" name="estado" value="procesado">
            <div id="bulk-inputs-container"></div>
            <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700;">
                ✓ Marcar como PROCESADAS
            </button>
        </form>
        <button type="button" id="btn-copiar-lote" class="btn btn-outline btn-sm" style="color: var(--color-gold); border-color: var(--color-gold);">
            📋 Copiar Lote
        </button>
        <button type="button" id="btn-deseleccionar-todo" class="btn btn-ghost btn-sm" style="color: var(--color-muted); font-size: 0.8rem;">
            Desmarcar
        </button>
    </div>
</div>

{{-- Toast Flotante de Copiado --}}
<div id="toast-copiado" style="display: none; position: fixed; top: 25px; right: 25px; z-index: 2000; background: #22c55e; color: #0f172a; font-weight: 700; padding: 0.75rem 1.25rem; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.4); font-size: 0.85rem; align-items: center; gap: 0.5rem;">
    ✓ ¡Datos copiados al portapapeles!
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkAll = document.getElementById('check-all-jugadas');
    const checkboxes = document.querySelectorAll('.check-jugada');
    const bulkBar = document.getElementById('bulk-bar');
    const bulkCount = document.getElementById('bulk-count');
    const bulkTotal = document.getElementById('bulk-total');
    const bulkInputsContainer = document.getElementById('bulk-inputs-container');
    const btnCopiarLote = document.getElementById('btn-copiar-lote');
    const btnDeseleccionar = document.getElementById('btn-deseleccionar-todo');
    const toast = document.getElementById('toast-copiado');

    function showToast(mensaje = '¡Datos copiados al portapapeles!') {
        toast.textContent = `✓ ${mensaje}`;
        toast.style.display = 'flex';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 2500);
    }

    function copiarAlPortapapeles(texto) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(texto).then(() => showToast());
        } else {
            // Fallback para entornos locales sin SSL
            const ta = document.createElement('textarea');
            ta.value = texto;
            ta.style.position = 'fixed';
            ta.style.left = '-999999px';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showToast();
        }
    }

    // Copiar línea individual
    document.querySelectorAll('.btn-copiar-linea').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const texto = e.currentTarget.dataset.texto;
            copiarAlPortapapeles(texto);
        });
    });

    // Actualizar barra flotante
    function actualizarBarraLote() {
        const seleccionadas = Array.from(checkboxes).filter(cb => cb.checked);
        const count = seleccionadas.length;

        if (count > 0) {
            let total = 0;
            bulkInputsContainer.innerHTML = '';

            seleccionadas.forEach(cb => {
                total += parseFloat(cb.dataset.monto || 0);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                bulkInputsContainer.appendChild(input);
            });

            bulkCount.textContent = count;
            bulkTotal.textContent = '$' + total.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            bulkBar.style.display = 'flex';
        } else {
            bulkBar.style.display = 'none';
            bulkInputsContainer.innerHTML = '';
        }

        if (checkAll) {
            checkAll.checked = (count === checkboxes.length && count > 0);
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            actualizarBarraLote();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', actualizarBarraLote);
    });

    if (btnDeseleccionar) {
        btnDeseleccionar.addEventListener('click', () => {
            checkboxes.forEach(cb => cb.checked = false);
            if (checkAll) checkAll.checked = false;
            actualizarBarraLote();
        });
    }

    if (btnCopiarLote) {
        btnCopiarLote.addEventListener('click', () => {
            const seleccionadas = Array.from(checkboxes).filter(cb => cb.checked);
            if (seleccionadas.length === 0) return;

            const textos = seleccionadas.map(cb => cb.dataset.texto).filter(t => t);
            const bloqueFinal = textos.join("\n---\n");
            copiarAlPortapapeles(bloqueFinal);
            showToast(`¡${seleccionadas.length} boletas copiadas al portapapeles!`);
        });
    }
});
</script>
@endsection
