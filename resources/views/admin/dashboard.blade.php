@extends('layouts.admin')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold);">Panel de Control</h1>
        <p style="color: var(--color-muted); font-size: 0.9rem;">Gestiona precios, revisa jugadas y monitorea errores del sistema.</p>
    </div>
    
    <form action="{{ route('admin.limpiar') }}" method="POST" onsubmit="return confirm('ATENCIÓN: Esto borrará permanentemente TODAS las jugadas y logs. ¿Deseas continuar?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">
            ⚠️ Limpiar Base de Datos
        </button>
    </form>
</div>

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

<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem;">
        <h3 style="font-family: var(--font-display); font-size: 1.25rem; color: var(--color-text); margin: 0;">Historial de Jugadas</h3>
        
        <form method="GET" action="{{ route('admin.dashboard') }}" style="display: flex; gap: 0.5rem;">
            <select name="estado" class="form-input" onchange="this.form.submit()" style="width: auto; padding: 0.4rem 2rem 0.4rem 1rem;">
                <option value="">Todos los estados</option>
                <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                <option value="procesado" {{ request('estado') === 'procesado' ? 'selected' : '' }}>Procesados</option>
                <option value="error_envio" {{ request('estado') === 'error_envio' ? 'selected' : '' }}>Error SMTP</option>
            </select>
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; color: var(--color-text);">
            <thead>
                <tr>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">ID / Fecha</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">Cliente</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">Apuestas</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: right; color: var(--color-muted);">Monto</th>
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
                        <strong>{{ $jugada->user->name }}</strong><br>
                        <span style="color: var(--color-muted); font-size: 0.75rem;">{{ $jugada->user->email }}<br>DNI: {{ $jugada->user->dni }}</span>
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
                    <td colspan="6" style="text-align: center; padding: 3rem;">
                        <span style="font-size: 2.5rem; opacity: 0.5;">📭</span><br>
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

<div class="card">
    <h3 style="font-family: var(--font-display); font-size: 1.25rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; color: var(--color-text);">Logs de Auditoría (Últimos 50 eventos)</h3>
    <div style="max-height: 400px; overflow-y: auto; background-color: rgba(0,0,0,0.3); color: var(--color-text); padding: 1.25rem; border-radius: 8px; font-family: monospace; font-size: 0.8rem; border: 1px solid var(--color-border);">
        @forelse($auditLogs as $log)
            <div style="margin-bottom: 0.75rem; border-bottom: 1px dashed var(--color-border); padding-bottom: 0.75rem;">
                <span style="color: var(--color-muted);">[{{ $log->created_at->format('Y-m-d H:i:s') }}]</span> 
                <strong style="color: var(--color-primary); margin: 0 0.5rem;">{{ strtoupper($log->accion) }}</strong> 
                @if($log->jugada_id) <span style="color: var(--color-gold);">[Jugada ID: {{ $log->jugada_id }}]</span> @endif
                <br>
                <span style="color: #a5b4fc;">{{ $log->detalles }}</span>
            </div>
        @empty
            <div style="color: var(--color-muted); text-align: center; padding: 2rem 0;">No hay registros de auditoría en el sistema.</div>
        @endforelse
    </div>
</div>

@endsection
