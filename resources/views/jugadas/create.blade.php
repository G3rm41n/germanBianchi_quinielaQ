@extends('layouts.app')

@section('title', 'Nueva Jugada')
@section('meta_description', 'Registrá una nueva jugada en Agencia N°5801.')

@section('content')
<div class="container" style="padding-top: 2.5rem; padding-bottom: 3rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div>
            <h1 style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem;">Nueva Jugada</h1>
            <p class="text-muted">Seleccioná tu modalidad y configurá tus números.</p>
        </div>
        <a href="{{ route('jugadas.index') }}" class="btn btn-ghost btn-sm">← Volver al historial</a>
    </div>

    @if($errors->any())
        <div class="alert alert-error" role="alert" style="margin-bottom: 2rem;">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem; align-items: start;" class="create-jugada-layout">
        
        {{-- Formulario principal --}}
        <div>
            {{-- Tabs de modalidad --}}
            <div class="jugada-tabs" role="tablist">
                <button type="button" class="jugada-tab active" data-target="quiniela" role="tab" aria-selected="true">
                    <span>🎰</span> Quiniela
                </button>
                <button type="button" class="jugada-tab" data-target="quini6" role="tab" aria-selected="false">
                    <span>6️⃣</span> Quini 6
                </button>
                <button type="button" class="jugada-tab" data-target="lotoplus" role="tab" aria-selected="false">
                    <span>➕</span> Loto Plus
                </button>
                <button type="button" class="jugada-tab" data-target="loto5" role="tab" aria-selected="false">
                    <span>5️⃣</span> Loto 5
                </button>
                <button type="button" class="jugada-tab" data-target="poceada" role="tab" aria-selected="false">
                    <span>🏆</span> Poceada
                </button>
            </div>

            <form method="POST" action="{{ route('jugadas.store') }}" id="form-jugada">
                @csrf
                <input type="hidden" name="idempotency_token" id="idempotency_token" value="{{ $token }}">
                <input type="hidden" name="cart_data" id="cart_data" value="[]">

                {{-- Panel: Quiniela --}}
                <div class="jugada-form-section" id="panel-quiniela" style="display: block;">
                    <div class="card">
                        <h2 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600; margin-bottom: 1.25rem;">Quiniela Tradicional</h2>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;" class="quiniela-grid">
                            <div class="form-group">
                                <label for="numero" class="form-label">Número (1 a 4 cifras)</label>
                                <input type="text" id="numero" name="numero" class="form-input {{ $errors->has('numero') ? 'is-invalid' : '' }}" value="{{ old('numero') }}" placeholder="Ej: 324" maxlength="4">
                            </div>
                            
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label">Posiciones</label>
                                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                    <select id="rango_posiciones" class="form-input" style="flex: 1;">
                                        <option value="">(Selección manual o elegí un rango rápido...)</option>
                                        <option value="1">A la cabeza (1)</option>
                                        <option value="5">A los 5 (Del 1 al 5)</option>
                                        <option value="10">A los 10 (Del 1 al 10)</option>
                                        <option value="20">A los 20 (Del 1 al 20)</option>
                                    </select>
                                    <button type="button" id="btn-clear-posiciones" class="btn btn-ghost btn-sm" style="border: 1px solid var(--color-border);">Limpiar</button>
                                </div>
                                <div class="numero-grid" id="grid-posiciones-quiniela" style="grid-template-columns: repeat(10, 1fr); gap: 5px;">
                                    <!-- Botones generados por JS -->
                                </div>
                                <input type="hidden" name="posiciones" id="input_posiciones" value="[]">
                            </div>

                            <div class="form-group">
                                <label for="jurisdiccion" class="form-label">Jurisdicción</label>
                                <select id="jurisdiccion" name="jurisdiccion" class="form-input {{ $errors->has('jurisdiccion') ? 'is-invalid' : '' }}">
                                    <option value="">Seleccioná...</option>
                                    <option value="nacion" {{ old('jurisdiccion') == 'nacion' ? 'selected' : '' }}>Nación</option>
                                    <option value="provincia" {{ old('jurisdiccion') == 'provincia' ? 'selected' : '' }}>Provincia de Bs. As.</option>
                                    <option value="santa_fe" {{ old('jurisdiccion') == 'santa_fe' ? 'selected' : '' }}>Santa Fe</option>
                                    <option value="cordoba" {{ old('jurisdiccion') == 'cordoba' ? 'selected' : '' }}>Córdoba</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="importe" class="form-label">Importe por posición ($)</label>
                                <input type="number" id="importe" name="importe" class="form-input {{ $errors->has('importe') ? 'is-invalid' : '' }}" value="{{ old('importe') }}" placeholder="Ej: 500" min="1">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel: Quini 6 --}}
                <div class="jugada-form-section" id="panel-quini6" style="display: none;">
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h2 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600;">Quini 6</h2>
                            <span class="badge badge-procesado">Elegí 6 números</span>
                        </div>
                        <div class="numero-grid" id="grid-quini6"></div>
                    </div>
                </div>

                {{-- Panel: Loto Plus --}}
                <div class="jugada-form-section" id="panel-lotoplus" style="display: none;">
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h2 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600;">Loto Plus</h2>
                            <span class="badge badge-procesado">Elegí 6 números</span>
                        </div>
                        <div class="numero-grid" id="grid-lotoplus" style="margin-bottom: 1.5rem;"></div>

                        <hr class="divider">
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="numero_plus" class="form-label">Número Plus (0 al 9)</label>
                            <input type="number" id="numero_plus" name="numero_plus" class="form-input {{ $errors->has('numero_plus') ? 'is-invalid' : '' }}" value="{{ old('numero_plus') }}" style="max-width: 150px;" min="0" max="9" placeholder="Ej: 5">
                        </div>
                    </div>
                </div>

                {{-- Panel: Loto 5 --}}
                <div class="jugada-form-section" id="panel-loto5" style="display: none;">
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h2 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600;">Loto 5</h2>
                            <span class="badge badge-procesado">Elegí 5 números</span>
                        </div>
                        <div class="numero-grid" id="grid-loto5"></div>
                    </div>
                </div>

                {{-- Panel: Poceada --}}
                <div class="jugada-form-section" id="panel-poceada" style="display: none;">
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h2 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600;">Quiniela Poceada</h2>
                            <span class="badge badge-procesado">Elegí 8 números</span>
                        </div>
                        <div class="numero-grid" id="grid-poceada"></div>
                    </div>
                </div>

            </form>
        </div>

        {{-- Panel lateral: Resumen y Carrito --}}
        <div>
            <div class="card costo-resumen" style="position: sticky; top: 100px;">
                <h3 style="font-size: 0.85rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Costo Apuesta Actual</h3>
                <div style="font-family: var(--font-display); font-size: 2.5rem; font-weight: 800; color: var(--color-gold); line-height: 1; margin-bottom: 1rem;">
                    $<span id="resumen-monto">0.00</span>
                </div>
                
                <div id="resumen-seleccion" style="font-size: 0.85rem; color: var(--color-muted); margin-bottom: 1.5rem; min-height: 20px;">
                    Configurá tu jugada para continuar.
                </div>

                <button type="button" class="btn btn-ghost btn-block" style="border: 1px solid var(--color-primary); color: var(--color-primary); margin-bottom: 2rem;" id="btn-add-cart" disabled>
                    + Agregar a la Boleta
                </button>

                <h3 style="font-size: 0.85rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; border-top: 1px solid var(--color-border); padding-top: 1rem;">Tu Boleta</h3>
                
                <div id="cart-list" style="margin-bottom: 1.5rem; max-height: 300px; overflow-y: auto;">
                    <!-- Cart items injected via JS -->
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-weight: bold;">
                    <span>TOTAL:</span>
                    <span style="font-size: 1.25rem; color: var(--color-gold);">$<span id="cart-total-amount">0.00</span></span>
                </div>

                <button type="submit" form="form-jugada" class="btn btn-primary btn-block btn-lg" id="btn-submit-cart" disabled>
                    Pagar Boleta Completa
                </button>
            </div>
        </div>

    </div>
</div>

{{-- Datos de precios para JS --}}
<div id="precios-data" style="display: none;"
     data-quini6="{{ $precios['quini6'] }}"
     data-lotoplus="{{ $precios['lotoplus'] }}"
     data-loto5="{{ $precios['loto5'] }}"
     data-poceada="{{ $precios['poceada'] }}">
</div>

<script type="module" src="{{ Vite::asset('resources/js/jugadas.js') }}"></script>

@endsection
