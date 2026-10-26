@extends('layouts.admin')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--color-gold);">Gestión de Clientes</h1>
        <p style="color: var(--color-muted); font-size: 0.9rem;">Visualiza todos los clientes registrados y el volumen de sus jugadas.</p>
    </div>
</div>

<div class="card">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; color: var(--color-text);">
            <thead>
                <tr>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">ID</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; color: var(--color-muted);">Cliente</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: center; color: var(--color-muted);">Jugadas Totales</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: right; color: var(--color-muted);">Volumen ($)</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: right; color: var(--color-muted);">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr style="border-bottom: 1px solid var(--color-border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--color-surface2)'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 1rem; color: var(--color-muted);">#{{ $u->id }}</td>
                    <td style="padding: 1rem;">
                        <strong>{{ $u->name }}</strong><br>
                        <span style="color: var(--color-muted); font-size: 0.8rem;">{{ $u->email }} | DNI: {{ $u->dni }}</span>
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
                    <td colspan="5" style="text-align: center; padding: 3rem;">
                        <span style="color: var(--color-muted); display: block; margin-top: 1rem;">No hay clientes registrados en el sistema.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 1.5rem;">
        {{ $users->links() }}
    </div>
</div>
@endsection
