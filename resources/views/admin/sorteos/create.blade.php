@extends('layouts.admin')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold); margin: 0;">Nuevo Sorteo Oficial</h1>
        <p style="color: var(--color-muted); font-size: 0.9rem; margin-top: 0.25rem;">Carga el extracto oficial de números para ejecutar el escrutinio de boletas.</p>
    </div>
    
    <a href="{{ route('admin.sorteos.index') }}" class="btn btn-ghost" style="border: 1px solid var(--color-border);">
        ← Volver al listado
    </a>
</div>

{{-- Selector de Juego (Tabs) --}}
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 0.5rem;" id="sorteo-tabs">
    <button type="button" class="btn btn-tab active" data-tab="quiniela" style="padding: 0.6rem 1.25rem; font-weight: 600; font-size: 0.9rem; border-radius: 8px;">
        🎯 Quiniela
    </button>
    <button type="button" class="btn btn-tab" data-tab="quini6" style="padding: 0.6rem 1.25rem; font-weight: 600; font-size: 0.9rem; border-radius: 8px;">
        🎱 Quini 6
    </button>
    <button type="button" class="btn btn-tab" data-tab="lotoplus" style="padding: 0.6rem 1.25rem; font-weight: 600; font-size: 0.9rem; border-radius: 8px;">
        ⭐ Loto Plus
    </button>
    <button type="button" class="btn btn-tab" data-tab="loto5" style="padding: 0.6rem 1.25rem; font-weight: 600; font-size: 0.9rem; border-radius: 8px;">
        🎲 Loto 5
    </button>
    <button type="button" class="btn btn-tab" data-tab="poceada" style="padding: 0.6rem 1.25rem; font-weight: 600; font-size: 0.9rem; border-radius: 8px;">
        🔢 Poceada
    </button>
</div>

@if ($errors->any())
    <div class="alert alert-error" style="margin-bottom: 1.5rem;">
        <div style="font-weight: 700; margin-bottom: 0.5rem;">Por favor corrige los siguientes errores:</div>
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.sorteos.store') }}" method="POST" id="form-sorteo">
    @csrf
    <input type="hidden" name="juego" id="input-juego" value="{{ old('juego', 'quiniela') }}">

    <div class="card" style="padding: 1.75rem; margin-bottom: 1.5rem;">
        <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-gold); margin-top: 0; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem;">
            Datos del Sorteo
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
            <div>
                <label class="form-label" for="fecha_sorteo">Fecha del Sorteo *</label>
                <input type="date" name="fecha_sorteo" id="fecha_sorteo" class="form-input" value="{{ old('fecha_sorteo', date('Y-m-d')) }}" required>
            </div>

            <div>
                <label class="form-label" for="numero_sorteo">N° de Sorteo / Concurso (opcional)</label>
                <input type="text" name="numero_sorteo" id="numero_sorteo" class="form-input" value="{{ old('numero_sorteo') }}" placeholder="Ej: 3184">
            </div>

            {{-- Campos específicos de Quiniela --}}
            <div class="campo-quiniela">
                <label class="form-label" for="jurisdiccion">Jurisdicción *</label>
                <select name="jurisdiccion" id="jurisdiccion" class="form-input">
                    <option value="nacion" {{ old('jurisdiccion') === 'nacion' ? 'selected' : '' }}>Nación / Ciudad</option>
                    <option value="provincia" {{ old('jurisdiccion') === 'provincia' ? 'selected' : '' }}>Provincia Bs. As.</option>
                    <option value="santa_fe" {{ old('jurisdiccion') === 'santa_fe' ? 'selected' : '' }}>Santa Fe</option>
                    <option value="cordoba" {{ old('jurisdiccion') === 'cordoba' ? 'selected' : '' }}>Córdoba</option>
                </select>
            </div>

            <div class="campo-quiniela">
                <label class="form-label" for="turno">Turno *</label>
                <select name="turno" id="turno" class="form-input">
                    <option value="matutina" {{ old('turno', 'matutina') === 'matutina' ? 'selected' : '' }}>Matutina</option>
                    <option value="previa" {{ old('turno') === 'previa' ? 'selected' : '' }}>La Previa</option>
                    <option value="primera" {{ old('turno') === 'primera' ? 'selected' : '' }}>Primera</option>
                    <option value="vespertina" {{ old('turno') === 'vespertina' ? 'selected' : '' }}>Vespertina</option>
                    <option value="nocturna" {{ old('turno') === 'nocturna' ? 'selected' : '' }}>Nocturna</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 1: QUINIELA (20 POSICIONES) --}}
    {{-- ========================================================================= --}}
    <div class="seccion-juego" id="panel-quiniela">
        <div class="card" style="padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-gold); margin: 0;">
                        Extracto Oficial (20 Posiciones)
                    </h3>
                    <p style="font-size: 0.8rem; color: var(--color-muted); margin: 0.25rem 0 0 0;">Ingresa los números de 4 cifras (o de 1 a 4 dígitos). Salta de casilla automáticamente.</p>
                </div>
                <button type="button" class="btn btn-outline btn-sm btn-azar" data-juego="quiniela" style="color: var(--color-gold); border-color: var(--color-gold);">
                    🎲 Azar (Extracto de prueba)
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.85rem;" id="grid-quiniela-inputs">
                @for ($i = 1; $i <= 20; $i++)
                    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: 0.6rem; text-align: center;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-muted); display: block; margin-bottom: 0.35rem;">
                            {{ $i }}° Premio
                        </span>
                        <input type="text"
                               name="extracto[{{ $i }}]"
                               id="quiniela_pos_{{ $i }}"
                               class="form-input quiniela-input"
                               data-index="{{ $i }}"
                               maxlength="4"
                               value="{{ old('extracto.'.$i) }}"
                               placeholder="0000"
                               style="text-align: center; font-size: 1.1rem; font-weight: 700; letter-spacing: 2px; padding: 0.4rem;">
                    </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 2: QUINI 6 --}}
    {{-- ========================================================================= --}}
    <div class="seccion-juego" id="panel-quini6" style="display: none;">
        <div class="card" style="padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-gold); margin: 0;">
                        Extracto Quini 6 (6 Números del 00 al 45)
                    </h3>
                </div>
                <button type="button" class="btn btn-outline btn-sm btn-azar" data-juego="quini6" style="color: var(--color-gold); border-color: var(--color-gold);">
                    🎲 Azar (6 números)
                </button>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; margin-bottom: 2rem;">
                @for ($i = 0; $i < 6; $i++)
                    <div style="text-align: center;">
                        <input type="number"
                               name="quini6_numeros[{{ $i }}]"
                               id="quini6_num_{{ $i }}"
                               class="form-input quini6-input"
                               min="0"
                               max="45"
                               placeholder="00"
                               value="{{ old('quini6_numeros.'.$i, old('numeros.'.$i)) }}"
                               style="width: 70px; height: 70px; text-align: center; font-size: 1.5rem; font-weight: 700; border-radius: 50%; padding: 0;">
                    </div>
                @endfor
            </div>

            <h4 style="font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--color-text); margin-bottom: 0.75rem;">
                Premios a Liquidar por Acierto ($)
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div>
                    <label class="form-label">Premio 6 Aciertos ($) *</label>
                    <input type="number" name="quini6_premios[6]" id="premio_quini6_6" class="form-input" min="0" step="any" placeholder="Ingresa monto total" value="{{ old('quini6_premios.6', old('tabla_premios.6')) }}">
                </div>
                <div>
                    <label class="form-label">Premio 5 Aciertos ($) *</label>
                    <input type="number" name="quini6_premios[5]" id="premio_quini6_5" class="form-input" min="0" step="any" placeholder="Ingresa monto por ganador" value="{{ old('quini6_premios.5', old('tabla_premios.5')) }}">
                </div>
                <div>
                    <label class="form-label">Premio 4 Aciertos ($) *</label>
                    <input type="number" name="quini6_premios[4]" id="premio_quini6_4" class="form-input" min="0" step="any" placeholder="Ingresa monto por ganador" value="{{ old('quini6_premios.4', old('tabla_premios.4')) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 3: LOTO PLUS --}}
    {{-- ========================================================================= --}}
    <div class="seccion-juego" id="panel-lotoplus" style="display: none;">
        <div class="card" style="padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-gold); margin: 0;">
                        Extracto Loto Plus (6 Números del 00 al 41 + 1 Número Plus)
                    </h3>
                </div>
                <button type="button" class="btn btn-outline btn-sm btn-azar" data-juego="lotoplus" style="color: var(--color-gold); border-color: var(--color-gold);">
                    🎲 Azar (Loto Plus)
                </button>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: center; align-items: center; flex-wrap: wrap; margin-bottom: 2rem;">
                @for ($i = 0; $i < 6; $i++)
                    <div style="text-align: center;">
                        <input type="number"
                               name="lotoplus_numeros[{{ $i }}]"
                               id="lotoplus_num_{{ $i }}"
                               class="form-input lotoplus-input"
                               min="0"
                               max="41"
                               placeholder="00"
                               value="{{ old('lotoplus_numeros.'.$i, old('numeros.'.$i)) }}"
                               style="width: 70px; height: 70px; text-align: center; font-size: 1.5rem; font-weight: 700; border-radius: 50%; padding: 0;">
                    </div>
                @endfor
                <div style="margin-left: 1rem; text-align: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-gold); display: block; margin-bottom: 0.25rem;">PLUS</span>
                    <input type="number"
                           name="lotoplus_numero_plus"
                           id="lotoplus_plus"
                           class="form-input"
                           min="0"
                           max="9"
                           placeholder="+"
                           value="{{ old('lotoplus_numero_plus', old('numero_plus')) }}"
                           style="width: 70px; height: 70px; text-align: center; font-size: 1.5rem; font-weight: 700; border-radius: 50%; padding: 0; border: 2px solid var(--color-gold); color: var(--color-gold);">
                </div>
            </div>

            <h4 style="font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--color-text); margin-bottom: 0.75rem;">
                Premios a Liquidar ($)
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div>
                    <label class="form-label">Premio 6 Aciertos ($) *</label>
                    <input type="number" name="lotoplus_premios[6]" id="premio_lotoplus_6" class="form-input" min="0" step="any" placeholder="Ingresa monto total" value="{{ old('lotoplus_premios.6', old('tabla_premios.6')) }}">
                </div>
                <div>
                    <label class="form-label">Premio 5 Aciertos ($) *</label>
                    <input type="number" name="lotoplus_premios[5]" id="premio_lotoplus_5" class="form-input" min="0" step="any" placeholder="Ingresa monto por ganador" value="{{ old('lotoplus_premios.5', old('tabla_premios.5')) }}">
                </div>
                <div>
                    <label class="form-label">Premio 4 Aciertos ($) *</label>
                    <input type="number" name="lotoplus_premios[4]" id="premio_lotoplus_4" class="form-input" min="0" step="any" placeholder="Ingresa monto por ganador" value="{{ old('lotoplus_premios.4', old('tabla_premios.4')) }}">
                </div>
                <div>
                    <label class="form-label">Bonus Acierto Plus ($)</label>
                    <input type="number" name="lotoplus_premios[plus_bonus]" id="premio_lotoplus_bonus" class="form-input" min="0" step="any" placeholder="Monto extra (opcional)" value="{{ old('lotoplus_premios.plus_bonus', old('tabla_premios.plus_bonus')) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 4: LOTO 5 --}}
    {{-- ========================================================================= --}}
    <div class="seccion-juego" id="panel-loto5" style="display: none;">
        <div class="card" style="padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-gold); margin: 0;">
                        Extracto Loto 5 (5 Números del 00 al 36)
                    </h3>
                </div>
                <button type="button" class="btn btn-outline btn-sm btn-azar" data-juego="loto5" style="color: var(--color-gold); border-color: var(--color-gold);">
                    🎲 Azar (5 números)
                </button>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; margin-bottom: 2rem;">
                @for ($i = 0; $i < 5; $i++)
                    <div style="text-align: center;">
                        <input type="number"
                               name="loto5_numeros[{{ $i }}]"
                               id="loto5_num_{{ $i }}"
                               class="form-input loto5-input"
                               min="0"
                               max="36"
                               placeholder="00"
                               value="{{ old('loto5_numeros.'.$i, old('numeros.'.$i)) }}"
                               style="width: 70px; height: 70px; text-align: center; font-size: 1.5rem; font-weight: 700; border-radius: 50%; padding: 0;">
                    </div>
                @endfor
            </div>

            <h4 style="font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--color-text); margin-bottom: 0.75rem;">
                Premios a Liquidar ($)
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div>
                    <label class="form-label">Premio 5 Aciertos ($) *</label>
                    <input type="number" name="loto5_premios[5]" id="premio_loto5_5" class="form-input" min="0" step="any" placeholder="Ingresa monto total" value="{{ old('loto5_premios.5', old('tabla_premios.5')) }}">
                </div>
                <div>
                    <label class="form-label">Premio 4 Aciertos ($) *</label>
                    <input type="number" name="loto5_premios[4]" id="premio_loto5_4" class="form-input" min="0" step="any" placeholder="Ingresa monto por ganador" value="{{ old('loto5_premios.4', old('tabla_premios.4')) }}">
                </div>
                <div>
                    <label class="form-label">Premio 3 Aciertos ($) *</label>
                    <input type="number" name="loto5_premios[3]" id="premio_loto5_3" class="form-input" min="0" step="any" placeholder="Ingresa monto por ganador" value="{{ old('loto5_premios.3', old('tabla_premios.3')) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 5: POCEADA --}}
    {{-- ========================================================================= --}}
    <div class="seccion-juego" id="panel-poceada" style="display: none;">
        <div class="card" style="padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <h3 style="font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--color-gold); margin: 0;">
                        Extracto Poceada (20 Números de 2 Cifras del 00 al 99)
                    </h3>
                </div>
                <button type="button" class="btn btn-outline btn-sm btn-azar" data-juego="poceada" style="color: var(--color-gold); border-color: var(--color-gold);">
                    🎲 Azar (20 números)
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(65px, 1fr)); gap: 0.5rem; margin-bottom: 2rem;">
                @for ($i = 0; $i < 20; $i++)
                    <input type="text"
                           name="poceada_numeros[{{ $i }}]"
                           id="poceada_num_{{ $i }}"
                           class="form-input poceada-input"
                           maxlength="2"
                           placeholder="00"
                           value="{{ old('poceada_numeros.'.$i, old('numeros.'.$i)) }}"
                           style="text-align: center; font-size: 1.1rem; font-weight: 700; padding: 0.45rem;">
                @endfor
            </div>

            <h4 style="font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--color-text); margin-bottom: 0.75rem;">
                Premios a Liquidar ($)
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div>
                    <label class="form-label">Premio 8 Aciertos ($) *</label>
                    <input type="number" name="poceada_premios[8]" id="premio_poceada_8" class="form-input" min="0" step="any" placeholder="Ingresa monto total" value="{{ old('poceada_premios.8', old('tabla_premios.8')) }}">
                </div>
                <div>
                    <label class="form-label">Premio 7 Aciertos ($) *</label>
                    <input type="number" name="poceada_premios[7]" id="premio_poceada_7" class="form-input" min="0" step="any" placeholder="Ingresa monto por ganador" value="{{ old('poceada_premios.7', old('tabla_premios.7')) }}">
                </div>
                <div>
                    <label class="form-label">Premio 6 Aciertos ($) *</label>
                    <input type="number" name="poceada_premios[6]" id="premio_poceada_6" class="form-input" min="0" step="any" placeholder="Ingresa monto por ganador" value="{{ old('poceada_premios.6', old('tabla_premios.6')) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Botón Guardar Sorteo --}}
    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
        <a href="{{ route('admin.sorteos.index') }}" class="btn btn-ghost" style="border: 1px solid var(--color-border);">Cancelar</a>
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem; font-weight: 600;">
            💾 Registrar Sorteo y Previsualizar Escrutinio →
        </button>
    </div>
</form>

<style>
.btn-tab {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--color-border);
    color: var(--color-muted);
    cursor: pointer;
    transition: all 0.2s;
}
.btn-tab:hover {
    color: var(--color-text);
    border-color: var(--color-gold);
}
.btn-tab.active {
    background: var(--color-gold);
    color: #0d1117;
    border-color: var(--color-gold);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#sorteo-tabs .btn-tab');
    const inputJuego = document.getElementById('input-juego');
    const secciones = document.querySelectorAll('.seccion-juego');
    const camposQuiniela = document.querySelectorAll('.campo-quiniela');

    function activarTab(juego) {
        tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === juego));
        
        secciones.forEach(s => {
            const activa = (s.id === `panel-${juego}`);
            s.style.display = activa ? 'block' : 'none';
            s.querySelectorAll('input, select, textarea').forEach(inp => {
                inp.disabled = !activa;
                if (!activa) inp.required = false;
            });
        });

        camposQuiniela.forEach(c => {
            const activa = (juego === 'quiniela');
            c.style.display = activa ? 'block' : 'none';
            c.querySelectorAll('input, select, textarea').forEach(inp => {
                inp.disabled = !activa;
                inp.required = activa;
            });
        });

        inputJuego.value = juego;

        // Activar required solo en la sección activa
        if (juego === 'quiniela') {
            document.querySelectorAll('.quiniela-input').forEach(inp => inp.required = true);
        } else if (juego === 'quini6') {
            document.querySelectorAll('.quini6-input').forEach(inp => inp.required = true);
            ['premio_quini6_6', 'premio_quini6_5', 'premio_quini6_4'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.required = true;
            });
        } else if (juego === 'lotoplus') {
            document.querySelectorAll('.lotoplus-input').forEach(inp => inp.required = true);
            const plus = document.getElementById('lotoplus_plus');
            if (plus) plus.required = true;
            ['premio_lotoplus_6', 'premio_lotoplus_5', 'premio_lotoplus_4'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.required = true;
            });
        } else if (juego === 'loto5') {
            document.querySelectorAll('.loto5-input').forEach(inp => inp.required = true);
            ['premio_loto5_5', 'premio_loto5_4', 'premio_loto5_3'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.required = true;
            });
        } else if (juego === 'poceada') {
            document.querySelectorAll('.poceada-input').forEach(inp => inp.required = true);
            ['premio_poceada_8', 'premio_poceada_7', 'premio_poceada_6'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.required = true;
            });
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => activarTab(tab.dataset.tab));
    });

    activarTab(inputJuego.value || 'quiniela');

    // Deshabilitar inputs de paneles inactivos al enviar el formulario
    document.getElementById('form-sorteo').addEventListener('submit', () => {
        secciones.forEach(s => {
            if (s.id !== `panel-${inputJuego.value}`) {
                s.querySelectorAll('input, select, textarea').forEach(inp => inp.disabled = true);
            }
        });
    });

    // Auto-focus next en quiniela inputs
    const quinielaInputs = document.querySelectorAll('.quiniela-input');
    quinielaInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            if (input.value.length === 4 && index < quinielaInputs.length - 1) {
                quinielaInputs[index + 1].focus();
            }
        });
    });

    // Helper: números al azar sin repetidos
    function generarNumerosUnicos(cantidad, min, max) {
        const set = new Set();
        while (set.size < cantidad) {
            const num = Math.floor(Math.random() * (max - min + 1)) + min;
            set.add(num);
        }
        return Array.from(set).sort((a, b) => a - b);
    }

    // Botones "Azar" para pruebas
    document.querySelectorAll('.btn-azar').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const juego = e.currentTarget.dataset.juego;

            if (juego === 'quiniela') {
                quinielaInputs.forEach(input => {
                    const rnd = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                    input.value = rnd;
                });
            } else if (juego === 'quini6') {
                const unicos = generarNumerosUnicos(6, 0, 45);
                unicos.forEach((n, idx) => {
                    const inp = document.getElementById(`quini6_num_${idx}`);
                    if (inp) inp.value = n;
                });
            } else if (juego === 'lotoplus') {
                const unicos = generarNumerosUnicos(6, 0, 41);
                unicos.forEach((n, idx) => {
                    const inp = document.getElementById(`lotoplus_num_${idx}`);
                    if (inp) inp.value = n;
                });
                const plus = document.getElementById('lotoplus_plus');
                if (plus) plus.value = Math.floor(Math.random() * 10);
            } else if (juego === 'loto5') {
                const unicos = generarNumerosUnicos(5, 0, 36);
                unicos.forEach((n, idx) => {
                    const inp = document.getElementById(`loto5_num_${idx}`);
                    if (inp) inp.value = n;
                });
            } else if (juego === 'poceada') {
                const unicos = generarNumerosUnicos(20, 0, 99);
                unicos.forEach((n, idx) => {
                    const inp = document.getElementById(`poceada_num_${idx}`);
                    if (inp) inp.value = n.toString().padStart(2, '0');
                });
            }
        });
    });
});
</script>
@endsection
