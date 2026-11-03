<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background-color: rgba(255,255,255,0.01);">
        <span style="font-size: 0.85rem; color: var(--color-muted);">
            Mostrando <strong>{{ $users->total() }}</strong> clientes registrados
            @if(($filtrosActivos ?? 0) > 0)
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
                        @if(($filtrosActivos ?? 0) > 0)
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

