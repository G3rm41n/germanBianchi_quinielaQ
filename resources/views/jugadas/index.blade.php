@extends('layouts.app')

@section('title', 'Mis Jugadas')
@section('meta_description', 'Historial de tus jugadas en Agencia N°5801.')

@section('content')
<div class="container" style="padding-top: 2.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div>
            <h1 style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem;">Mis Jugadas</h1>
            <p class="text-muted">Historial completo de tus jugadas simuladas.</p>
        </div>
        <a href="{{ route('jugadas.create') }}" class="btn btn-primary btn-sm">
            🎯 Nueva Jugada
        </a>
    </div>

    @if($jugadas->isEmpty())
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <span style="font-size: 3.5rem; opacity: 0.6; display: block; margin-bottom: 1.25rem;">📝</span>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">Aún no hay jugadas</h2>
            <p class="text-muted" style="margin-bottom: 2rem;">Todavía no registraste ninguna jugada en la plataforma.</p>
            <a href="{{ route('jugadas.create') }}" class="btn btn-primary">
                Crear mi primera jugada
            </a>
        </div>
    @else
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--color-border); background-color: rgba(0,0,0,0.2);">
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Fecha</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Modalidad</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Detalle</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Monto</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Estado</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.05em;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jugadas as $jugada)
                            <tr style="border-bottom: 1px solid var(--color-border);">
                                <td style="padding: 1.25rem 1.5rem; font-size: 0.9rem;">
                                    {{ $jugada->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    @if($jugada->modalidad === 'quiniela')
                                        <span class="badge" style="background-color: rgba(255,255,255,0.1);">🎰 Quiniela</span>
                                    @elseif($jugada->modalidad === 'quini6')
                                        <span class="badge" style="background-color: rgba(255,255,255,0.1);">6️⃣ Quini 6</span>
                                    @elseif($jugada->modalidad === 'lotoplus')
                                        <span class="badge" style="background-color: rgba(255,255,255,0.1);">➕ Loto Plus</span>
                                    @elseif($jugada->modalidad === 'loto5')
                                        <span class="badge" style="background-color: rgba(255,255,255,0.1);">5️⃣ Loto 5</span>
                                    @elseif($jugada->modalidad === 'poceada')
                                        <span class="badge" style="background-color: rgba(255,255,255,0.1);">🏆 Poceada</span>
                                    @endif
                                </td>
                                <td style="padding: 1.25rem 1.5rem; font-size: 0.85rem; color: var(--color-muted);">
                                    @if($jugada->modalidad === 'quiniela' && $jugada->detalleQuiniela)
                                        Nº: <strong>{{ str_pad($jugada->detalleQuiniela->numero, 4, '0', STR_PAD_LEFT) }}</strong> | Pos: {{ $jugada->detalleQuiniela->posicion }} | Jur: {{ ucfirst($jugada->detalleQuiniela->jurisdiccion) }}
                                    @elseif($jugada->modalidad === 'quini6' && $jugada->detalleQuini6)
                                        Nº: <strong>{{ implode(', ', array_map(fn($n) => str_pad($n, 2, '0', STR_PAD_LEFT), $jugada->detalleQuini6->numeros)) }}</strong>
                                    @elseif($jugada->modalidad === 'lotoplus' && $jugada->detalleLotoPlus)
                                        Nº: <strong>{{ implode(', ', array_map(fn($n) => str_pad($n, 2, '0', STR_PAD_LEFT), $jugada->detalleLotoPlus->numeros)) }}</strong> | Plus: {{ $jugada->detalleLotoPlus->numero_plus }}
                                    @elseif($jugada->modalidad === 'loto5' && $jugada->detalleLoto5)
                                        Nº: <strong>{{ implode(', ', array_map(fn($n) => str_pad($n, 2, '0', STR_PAD_LEFT), $jugada->detalleLoto5->numeros)) }}</strong>
                                    @elseif($jugada->modalidad === 'poceada' && $jugada->detallePoceada)
                                        Nº: <strong>{{ implode(', ', array_map(fn($n) => str_pad($n, 2, '0', STR_PAD_LEFT), $jugada->detallePoceada->numeros)) }}</strong>
                                    @endif
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
                                    <a href="{{ route('jugadas.pdf', $jugada) }}" class="btn btn-ghost btn-sm" title="PDF disponible en Fase 4">
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
