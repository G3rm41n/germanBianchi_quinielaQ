@extends('layouts.admin')

@section('content')
@php
    $filtrosActivos = collect(['inicial_nombre', 'inicial_apellido', 'min_jugadas', 'min_volumen'])
        ->filter(fn($key) => request()->filled($key))
        ->count();
    $abecedario = range('A', 'Z');
@endphp

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold); margin: 0 0 0.25rem 0;">Gestión de Clientes</h1>
        <p style="color: var(--color-muted); font-size: 0.9rem; margin: 0;">Filtra clientes, analiza su volumen de juego e importa/exporta datos mediante planillas Excel (.xlsx).</p>
    </div>

    {{-- Botones de Acción: Filtro, Exportar, Importar, Plantilla --}}
    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        @if($filtrosActivos > 0)
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm" style="color: var(--color-danger); border: 1px solid rgba(248,81,73,0.35);">
                Limpiar Filtros ({{ $filtrosActivos }})
            </a>
        @endif

        <button type="button" onclick="toggleMenuFiltrosClientes()" class="btn {{ $filtrosActivos > 0 ? 'btn-primary' : 'btn-outline' }} btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
            <span>🔍 Filtrar Clientes</span>
            @if($filtrosActivos > 0)
                <span style="background: rgba(255,255,255,0.25); border-radius: 9999px; padding: 0.1rem 0.45rem; font-size: 0.75rem;">{{ $filtrosActivos }}</span>
            @endif
            <span id="icono-chevron-clientes" style="font-size: 0.75rem; transition: transform 0.2s;">{{ $filtrosActivos > 0 ? '▲' : '▼' }}</span>
        </button>

        <a href="{{ route('admin.users.exportar', request()->query()) }}" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;" title="Exporta los clientes actuales y sus jugadas detalladas a un archivo Excel">
            <span>📊 Exportar Excel</span>
        </a>

        <button type="button" onclick="abrirModalImportar()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            <span>📥 Importar Excel</span>
        </button>

        <a href="{{ route('admin.users.plantilla') }}" class="btn btn-ghost btn-sm" style="color: var(--color-gold); border: 1px dashed var(--color-gold);" title="Descargar plantilla de Excel con formato y ejemplos">
            <span>📄 Plantilla Ejemplo</span>
        </a>
    </div>
</div>

{{-- Panel Desplegable de Filtros --}}
<div id="menu-filtros-clientes" style="display: {{ $filtrosActivos > 0 ? 'block' : 'none' }}; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.25rem; background-color: var(--color-surface2); border: 1px solid var(--color-border);">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) auto; gap: 1rem; align-items: flex-end;">
            
            {{-- Filtro Inicial Nombre --}}
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem; letter-spacing: 0.05em;">
                    Inicial Nombre
                </label>
                <select name="inicial_nombre" class="form-input" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; width: 100%;">
                    <option value="">Todas (A-Z)</option>
                    @foreach($abecedario as $letra)
                        <option value="{{ $letra }}" {{ request('inicial_nombre') === $letra ? 'selected' : '' }}>
                            {{ $letra }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filtro Inicial Apellido --}}
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem; letter-spacing: 0.05em;">
                    Inicial Apellido
                </label>
                <select name="inicial_apellido" class="form-input" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; width: 100%;">
                    <option value="">Todas (A-Z)</option>
                    @foreach($abecedario as $letra)
                        <option value="{{ $letra }}" {{ request('inicial_apellido') === $letra ? 'selected' : '' }}>
                            {{ $letra }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filtro Mínimo de Jugadas --}}
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem; letter-spacing: 0.05em;">
                    Jugadas desde (≥)
                </label>
                <input type="number" name="min_jugadas" min="0" placeholder="Ej: 5" value="{{ request('min_jugadas') }}" class="form-input" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; width: 100%;">
            </div>

            {{-- Filtro Volumen de Pesos --}}
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem; letter-spacing: 0.05em;">
                    Volumen desde ($ ≥)
                </label>
                <input type="number" name="min_volumen" min="0" step="100" placeholder="Ej: 10000" value="{{ request('min_volumen') }}" class="form-input" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; width: 100%;">
            </div>

            {{-- Botones Aplicar / Limpiar --}}
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.85rem; white-space: nowrap;">
                    Aplicar
                </button>
                @if($filtrosActivos > 0)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabla de Clientes --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background-color: rgba(255,255,255,0.01);">
        <span style="font-size: 0.85rem; color: var(--color-muted);">
            Mostrando <strong>{{ $users->total() }}</strong> clientes registrados
            @if($filtrosActivos > 0)
                (filtrados con {{ $filtrosActivos }} {{ $filtrosActivos === 1 ? 'criterio' : 'criterios' }})
            @endif
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; color: var(--color-text);">
            <thead>
                <tr style="background-color: rgba(0,0,0,0.2); border-bottom: 1px solid var(--color-border);">
                    <th style="padding: 1rem; text-align: left; color: var(--color-muted); font-size: 0.8rem; text-transform: uppercase;">ID</th>
                    <th style="padding: 1rem; text-align: left; color: var(--color-muted); font-size: 0.8rem; text-transform: uppercase;">Cliente</th>
                    <th style="padding: 1rem; text-align: right; color: var(--color-muted); font-size: 0.8rem; text-transform: uppercase;">Saldo Actual</th>
                    <th style="padding: 1rem; text-align: center; color: var(--color-muted); font-size: 0.8rem; text-transform: uppercase;">Jugadas Totales</th>
                    <th style="padding: 1rem; text-align: right; color: var(--color-muted); font-size: 0.8rem; text-transform: uppercase;">Volumen Jugado ($)</th>
                    <th style="padding: 1rem; text-align: right; color: var(--color-muted); font-size: 0.8rem; text-transform: uppercase;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr style="border-bottom: 1px solid var(--color-border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--color-surface2)'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 1rem; color: var(--color-muted);">#{{ $u->id }}</td>
                    <td style="padding: 1rem;">
                        <strong style="color: var(--color-text); font-size: 0.95rem;">{{ $u->name }}</strong><br>
                        <span style="color: var(--color-muted); font-size: 0.8rem;">{{ $u->email }} | DNI: {{ $u->dni ?? 'S/D' }}</span>
                    </td>
                    <td style="padding: 1rem; text-align: right; font-weight: 600; color: {{ ($u->saldo ?? 0) > 0 ? 'var(--color-success)' : 'var(--color-muted)' }};">
                        ${{ number_format($u->saldo ?? 0, 2, ',', '.') }}
                    </td>
                    <td style="padding: 1rem; text-align: center; font-weight: bold; color: var(--color-primary);">
                        {{ $u->jugadas_count }}
                    </td>
                    <td style="padding: 1rem; text-align: right; font-weight: 700; color: var(--color-gold);">
                        ${{ number_format($u->jugadas_sum_monto_total ?? 0, 2, ',', '.') }}
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <a href="{{ route('admin.users.show', $u) }}" class="btn btn-primary btn-sm">Ver Detalle</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem;">
                        <span style="color: var(--color-muted); display: block; font-size: 1rem;">No se encontraron clientes con los filtros especificados.</span>
                        @if($filtrosActivos > 0)
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm" style="margin-top: 1rem; display: inline-block;">
                                Limpiar filtros aplicados
                            </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--color-border);">
            {{ $users->links() }}
        </div>
    @endif
</div>

{{-- Modal para Importar Excel --}}
<div id="modal-importar-excel" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
    <div class="card" style="max-width: 550px; width: 100%; max-height: 90vh; overflow-y: auto; background: var(--color-surface); border: 1px solid var(--color-gold); box-shadow: 0 10px 25px rgba(0,0,0,0.5); position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem;">
            <h3 style="font-family: var(--font-display); font-size: 1.25rem; color: var(--color-gold); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                📥 Importar Clientes y Jugadas
            </h3>
            <button type="button" onclick="cerrarModalImportar()" style="background: transparent; border: none; color: var(--color-muted); font-size: 1.25rem; cursor: pointer;">✕</button>
        </div>

        <form action="{{ route('admin.users.importar') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <p style="font-size: 0.85rem; color: var(--color-muted); line-height: 1.5; margin-bottom: 1rem;">
                Sube un archivo de Excel (.xlsx o .xls) con las hojas <strong>Clientes</strong> y opcionalmente <strong>Jugadas</strong>. Si un cliente ya existe (por DNI o Email), sus datos se actualizarán y se le asociarán las nuevas jugadas.
            </p>

            <div style="background-color: var(--color-surface2); border: 1px dashed var(--color-border); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem; text-align: center;">
                <label for="input-archivo-excel" style="display: block; font-weight: 600; color: var(--color-text); margin-bottom: 0.5rem; cursor: pointer;">
                    Seleccionar Archivo Excel
                </label>
                <input id="input-archivo-excel" type="file" name="archivo" accept=".xlsx,.xls" required style="width: 100%; font-size: 0.85rem; color: var(--color-muted);">
                <small style="display: block; color: var(--color-muted); margin-top: 0.5rem; font-size: 0.75rem;">Formatos soportados: .xlsx, .xls (Máximo 10MB)</small>
            </div>

            <div style="background-color: rgba(212, 175, 55, 0.08); border-left: 3px solid var(--color-gold); padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: 0.8rem; color: var(--color-text);">
                <strong>💡 Recomendación:</strong> Si es la primera vez que importas, te sugerimos descargar la 
                <a href="{{ route('admin.users.plantilla') }}" style="color: var(--color-gold); text-decoration: underline; font-weight: 600;">Plantilla Oficial de Ejemplo</a> 
                para asegurar que las columnas y modalidades coincidan exactamente.
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="cerrarModalImportar()" class="btn btn-ghost" style="font-size: 0.85rem;">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary" style="font-size: 0.85rem; font-weight: 600;">
                    Iniciar Importación
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleMenuFiltrosClientes() {
        const menu = document.getElementById('menu-filtros-clientes');
        const chevron = document.getElementById('icono-chevron-clientes');
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
            if (chevron) chevron.textContent = '▲';
        } else {
            menu.style.display = 'none';
            if (chevron) chevron.textContent = '▼';
        }
    }

    function abrirModalImportar() {
        const modal = document.getElementById('modal-importar-excel');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function cerrarModalImportar() {
        const modal = document.getElementById('modal-importar-excel');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Cerrar al hacer clic fuera del contenido del modal
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modal-importar-excel');
        if (e.target === modal) {
            cerrarModalImportar();
        }
    });
</script>
@endsection
