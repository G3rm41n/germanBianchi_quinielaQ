@extends('layouts.admin')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom: 1rem;">&larr; Volver al listado</a>
    <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold);">Jugadas de: {{ $user->name }}</h1>
    <p style="color: var(--color-muted); font-size: 0.9rem;">Email: {{ $user->email }} | DNI: {{ $user->dni }}</p>
</div>

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
                    <td colspan="5" style="text-align: center; padding: 3rem;">
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
