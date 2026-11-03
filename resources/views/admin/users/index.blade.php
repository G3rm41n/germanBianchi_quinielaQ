@extends('layouts.admin')

@section('content')
@php
    $filtrosActivos = collect(['buscar', 'min_jugadas', 'min_volumen', 'inicial_nombre', 'inicial_apellido'])
        ->filter(fn($key) => request()->filled($key))
        ->count();
@endphp

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold); margin: 0 0 0.25rem 0;">Gestión de Clientes</h1>
        <p style="color: var(--color-muted); font-size: 0.9rem; margin: 0;">Filtra clientes en tiempo real, analiza su volumen de juego e importa/exporta datos mediante planillas Excel (.xlsx).</p>
    </div>

    {{-- Botones de Acción: Filtro, Exportar, Importar, Plantilla --}}
    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <a id="btn-limpiar-top" href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm" style="color: var(--color-danger); border: 1px solid rgba(248,81,73,0.35); display: {{ $filtrosActivos > 0 ? 'inline-flex' : 'none' }};">
            Limpiar Filtros (<span id="texto-contador-filtros-top">{{ $filtrosActivos }}</span>)
        </a>

        <button type="button" onclick="toggleMenuFiltrosClientes()" class="btn {{ $filtrosActivos > 0 ? 'btn-primary' : 'btn-outline' }} btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
            <span>🔍 Filtrar Clientes</span>
            <span id="badge-filtros-clientes" style="background: rgba(255,255,255,0.25); border-radius: 9999px; padding: 0.1rem 0.45rem; font-size: 0.75rem; display: {{ $filtrosActivos > 0 ? 'inline-block' : 'none' }};">
                {{ $filtrosActivos }}
            </span>
            <span id="icono-chevron-clientes" style="font-size: 0.75rem; transition: transform 0.2s;">{{ $filtrosActivos > 0 ? '▲' : '▼' }}</span>
        </button>

        <a id="btn-exportar-excel" href="{{ route('admin.users.exportar', request()->query()) }}" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;" title="Exporta los clientes actuales y sus jugadas detalladas a un archivo Excel">
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

{{-- Panel Desplegable de Filtros con Búsqueda en Tiempo Real --}}
<div id="menu-filtros-clientes" style="display: {{ $filtrosActivos > 0 ? 'block' : 'none' }}; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.25rem; background-color: var(--color-surface2); border: 1px solid var(--color-border);">
        <form id="form-filtros-clientes" method="GET" action="{{ route('admin.users.index') }}" style="display: grid; grid-template-columns: minmax(260px, 2fr) minmax(140px, 1fr) minmax(160px, 1fr) auto; gap: 1rem; align-items: flex-end;">
            
            {{-- Búsqueda de Texto Manual (Nombre y Apellido) en Tiempo Real --}}
            <div>
                <label for="input-buscar-cliente" style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem; letter-spacing: 0.05em;">
                    Búsqueda de Cliente (Nombre o Apellido)
                </label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--color-muted); font-size: 0.85rem; pointer-events: none;">🔍</span>
                    <input 
                        type="text" 
                        id="input-buscar-cliente" 
                        name="buscar" 
                        value="{{ request('buscar') }}" 
                        placeholder="Escribe letras para buscar en tiempo real (ej: Juan Pérez)..." 
                        class="form-input" 
                        style="font-size: 0.85rem; padding: 0.5rem 2.2rem 0.5rem 2.2rem; width: 100%;" 
                        autocomplete="off"
                    >
                    {{-- Botón para borrar el texto escrito --}}
                    <button 
                        type="button" 
                        id="btn-borrar-busqueda" 
                        onclick="limpiarTextoBusqueda()" 
                        style="display: {{ request('buscar') ? 'inline-flex' : 'none' }}; position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--color-muted); cursor: pointer; font-size: 0.9rem; padding: 0.2rem;" 
                        title="Borrar texto"
                    >
                        ✕
                    </button>
                    {{-- Indicador de carga en vivo --}}
                    <span id="spinner-busqueda" style="display: none; position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: var(--color-gold); font-weight: 600;">
                        ⏳
                    </span>
                </div>
            </div>

            {{-- Filtro Mínimo de Jugadas --}}
            <div>
                <label for="input-min-jugadas" style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem; letter-spacing: 0.05em;">
                    Jugadas desde (≥)
                </label>
                <input 
                    type="number" 
                    id="input-min-jugadas" 
                    name="min_jugadas" 
                    min="0" 
                    placeholder="Ej: 5" 
                    value="{{ request('min_jugadas') }}" 
                    class="form-input" 
                    style="font-size: 0.85rem; padding: 0.5rem 0.75rem; width: 100%;"
                >
            </div>

            {{-- Filtro Volumen de Pesos --}}
            <div>
                <label for="input-min-volumen" style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 0.35rem; letter-spacing: 0.05em;">
                    Volumen desde ($ ≥)
                </label>
                <input 
                    type="number" 
                    id="input-min-volumen" 
                    name="min_volumen" 
                    min="0" 
                    step="100" 
                    placeholder="Ej: 10000" 
                    value="{{ request('min_volumen') }}" 
                    class="form-input" 
                    style="font-size: 0.85rem; padding: 0.5rem 0.75rem; width: 100%;"
                >
            </div>

            {{-- Botones Aplicar / Limpiar --}}
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.85rem; white-space: nowrap;">
                    Aplicar
                </button>
                <button type="button" id="btn-limpiar-form" onclick="limpiarTodosFiltros()" class="btn btn-ghost" style="padding: 0.5rem 0.75rem; font-size: 0.85rem; display: {{ $filtrosActivos > 0 ? 'inline-block' : 'none' }};">
                    Limpiar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Contenedor Reactivo de la Tabla de Clientes --}}
<div id="contenedor-tabla-clientes">
    @include('admin.users._table', ['users' => $users, 'filtrosActivos' => $filtrosActivos])
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
    const baseUrlUsuarios = @json(route('admin.users.index'));
    let temporizadorDebounce = null;
    let controladorPeticion = null;

    function toggleMenuFiltrosClientes() {
        const menu = document.getElementById('menu-filtros-clientes');
        const chevron = document.getElementById('icono-chevron-clientes');
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
            if (chevron) chevron.textContent = '▲';
            const inputBuscar = document.getElementById('input-buscar-cliente');
            if (inputBuscar) setTimeout(() => inputBuscar.focus(), 50);
        } else {
            menu.style.display = 'none';
            if (chevron) chevron.textContent = '▼';
        }
    }

    function abrirModalImportar() {
        const modal = document.getElementById('modal-importar-excel');
        if (modal) modal.style.display = 'flex';
    }

    function cerrarModalImportar() {
        const modal = document.getElementById('modal-importar-excel');
        if (modal) modal.style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modal-importar-excel');
        if (e.target === modal) cerrarModalImportar();
    });

    /**
     * Ejecuta la consulta AJAX para actualizar la lista de clientes en tiempo real.
     */
    function ejecutarBusquedaEnTiempoReal(url = null) {
        const inputBuscar = document.getElementById('input-buscar-cliente');
        const inputMinJugadas = document.getElementById('input-min-jugadas');
        const inputMinVolumen = document.getElementById('input-min-volumen');
        const btnBorrar = document.getElementById('btn-borrar-busqueda');
        const spinner = document.getElementById('spinner-busqueda');

        // Mostrar / ocultar botón 'X' de borrar
        if (btnBorrar) {
            btnBorrar.style.display = (inputBuscar && inputBuscar.value.trim().length > 0) ? 'inline-flex' : 'none';
        }

        // Mostrar spinner
        if (spinner) spinner.style.display = 'inline-block';

        // Cancelar petición anterior si aún estaba pendiente
        if (controladorPeticion) {
            controladorPeticion.abort();
        }
        controladorPeticion = new AbortController();

        let requestUrl = url;
        if (!requestUrl) {
            const params = new URLSearchParams();
            if (inputBuscar && inputBuscar.value.trim()) {
                params.set('buscar', inputBuscar.value.trim());
            }
            if (inputMinJugadas && inputMinJugadas.value.trim()) {
                params.set('min_jugadas', inputMinJugadas.value.trim());
            }
            if (inputMinVolumen && inputMinVolumen.value.trim()) {
                params.set('min_volumen', inputMinVolumen.value.trim());
            }
            requestUrl = `${baseUrlUsuarios}${params.toString() ? '?' + params.toString() : ''}`;
        }

        fetch(requestUrl, {
            signal: controladorPeticion.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al consultar datos');
            return response.json();
        })
        .then(data => {
            const contenedor = document.getElementById('contenedor-tabla-clientes');
            if (contenedor && data.html) {
                contenedor.innerHTML = data.html;
            }

            // Actualizar enlace de exportación Excel con los filtros vigentes
            const btnExportar = document.getElementById('btn-exportar-excel');
            if (btnExportar && data.export_url) {
                btnExportar.href = data.export_url;
            }

            // Actualizar badges y contadores de filtros
            actualizarIndicadoresFiltros(data.filtros_activos);

            // Actualizar URL del navegador sin recargar
            window.history.replaceState(null, '', requestUrl);
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Error en búsqueda de clientes:', err);
            }
        })
        .finally(() => {
            if (spinner) spinner.style.display = 'none';
        });
    }

    function actualizarIndicadoresFiltros(cantidad) {
        const badge = document.getElementById('badge-filtros-clientes');
        const btnLimpiarTop = document.getElementById('btn-limpiar-top');
        const contadorTop = document.getElementById('texto-contador-filtros-top');
        const btnLimpiarForm = document.getElementById('btn-limpiar-form');

        if (cantidad > 0) {
            if (badge) {
                badge.textContent = cantidad;
                badge.style.display = 'inline-block';
            }
            if (btnLimpiarTop) {
                if (contadorTop) contadorTop.textContent = cantidad;
                btnLimpiarTop.style.display = 'inline-flex';
            }
            if (btnLimpiarForm) {
                btnLimpiarForm.style.display = 'inline-block';
            }
        } else {
            if (badge) badge.style.display = 'none';
            if (btnLimpiarTop) btnLimpiarTop.style.display = 'none';
            if (btnLimpiarForm) btnLimpiarForm.style.display = 'none';
        }
    }

    function limpiarTextoBusqueda() {
        const inputBuscar = document.getElementById('input-buscar-cliente');
        if (inputBuscar) {
            inputBuscar.value = '';
            inputBuscar.focus();
        }
        ejecutarBusquedaEnTiempoReal();
    }

    function limpiarTodosFiltros() {
        const inputBuscar = document.getElementById('input-buscar-cliente');
        const inputMinJugadas = document.getElementById('input-min-jugadas');
        const inputMinVolumen = document.getElementById('input-min-volumen');

        if (inputBuscar) inputBuscar.value = '';
        if (inputMinJugadas) inputMinJugadas.value = '';
        if (inputMinVolumen) inputMinVolumen.value = '';

        ejecutarBusquedaEnTiempoReal();
    }

    // Inicializar listeners en el cliente
    document.addEventListener('DOMContentLoaded', function () {
        const inputBuscar = document.getElementById('input-buscar-cliente');
        const inputMinJugadas = document.getElementById('input-min-jugadas');
        const inputMinVolumen = document.getElementById('input-min-volumen');

        if (inputBuscar) {
            inputBuscar.addEventListener('input', function () {
                clearTimeout(temporizadorDebounce);
                temporizadorDebounce = setTimeout(() => {
                    ejecutarBusquedaEnTiempoReal();
                }, 250); // 250ms de debounce para fluidez en tiempo real
            });
        }

        if (inputMinJugadas) {
            inputMinJugadas.addEventListener('input', function () {
                clearTimeout(temporizadorDebounce);
                temporizadorDebounce = setTimeout(() => {
                    ejecutarBusquedaEnTiempoReal();
                }, 300);
            });
        }

        if (inputMinVolumen) {
            inputMinVolumen.addEventListener('input', function () {
                clearTimeout(temporizadorDebounce);
                temporizadorDebounce = setTimeout(() => {
                    ejecutarBusquedaEnTiempoReal();
                }, 300);
            });
        }

        // Delegación de eventos para la paginación reactiva (clic en página 2, 3, etc.)
        const contenedorTabla = document.getElementById('contenedor-tabla-clientes');
        if (contenedorTabla) {
            contenedorTabla.addEventListener('click', function (e) {
                const link = e.target.closest('.pagination a, a[rel="next"], a[rel="prev"]');
                if (link && link.href) {
                    e.preventDefault();
                    ejecutarBusquedaEnTiempoReal(link.href);
                }
            });
        }
    });
</script>
@endsection
