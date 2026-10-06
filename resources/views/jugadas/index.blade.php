@extends('layouts.app')

@section('title', 'Mis Boletas')
@section('meta_description', 'Historial de tus boletas en Agencia N°5801.')

@section('content')
<div class="container" style="padding-top: 2.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div>
            <h1 style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem;">Mis Boletas</h1>
            <p class="text-muted">Historial completo de tus compras en la plataforma.</p>
        </div>
        <a href="{{ route('jugadas.create') }}" class="btn btn-primary btn-sm">
            🎯 Nueva Jugada
        </a>
    </div>

    @if($jugadas->isEmpty())
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <span style="font-size: 3.5rem; opacity: 0.6; display: block; margin-bottom: 1.25rem;">📝</span>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">Aún no hay boletas</h2>
            <p class="text-muted" style="margin-bottom: 2rem;">Todavía no registraste ninguna compra en la plataforma.</p>
            <a href="{{ route('jugadas.create') }}" class="btn btn-primary">
                Armar mi primera boleta
            </a>
        </div>
    @else
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--color-border); background-color: rgba(0,0,0,0.2);">
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Boleta ID / Fecha</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Apuestas</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Monto Total</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Estado</th>
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
                                            <li>🎰 Quiniela: Nº {{ $q->numero }} (Pos: {{ $q->posicion }})</li>
                                        @endforeach
                                        @foreach($jugada->detallesQuini6 as $q)
                                            <li>6️⃣ Quini 6</li>
                                        @endforeach
                                        @foreach($jugada->detallesLotoPlus as $q)
                                            <li>➕ Loto Plus</li>
                                        @endforeach
                                        @foreach($jugada->detallesLoto5 as $q)
                                            <li>5️⃣ Loto 5</li>
                                        @endforeach
                                        @foreach($jugada->detallesPoceada as $q)
                                            <li>🏆 Poceada</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--color-gold);">
                                    ${{ number_format($jugada->monto_total, 2, ',', '.') }}
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
                                        📄 PDF
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
@endsection
