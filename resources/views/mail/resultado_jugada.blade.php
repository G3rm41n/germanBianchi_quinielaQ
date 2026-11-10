<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #0f172a; color: #f8fafc; margin: 0; padding: 20px; }
        .container { background-color: #1e293b; border-radius: 12px; max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #334155; }
        .header { text-align: center; border-bottom: 1px solid #334155; padding-bottom: 20px; margin-bottom: 20px; }
        .logo-title { font-size: 24px; font-weight: bold; color: #d4a017; margin: 0; }
        .badge { display: inline-block; padding: 6px 14px; border-radius: 9999px; font-weight: bold; font-size: 14px; text-transform: uppercase; margin: 15px 0; }
        .badge-ganadora { background-color: rgba(72, 187, 120, 0.2); color: #48bb78; border: 1px solid #48bb78; }
        .badge-no-ganadora { background-color: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #f87171; }
        .badge-procesada { background-color: rgba(212, 160, 23, 0.2); color: #d4a017; border: 1px solid #d4a017; }
        .premio-box { background: rgba(72, 187, 120, 0.1); border: 2px solid #48bb78; border-radius: 8px; padding: 15px; text-align: center; margin: 20px 0; }
        .premio-monto { font-size: 28px; font-weight: 800; color: #48bb78; margin: 5px 0; }
        .details-box { background-color: #0f172a; border-radius: 8px; padding: 15px; margin: 20px 0; border: 1px solid #334155; }
        .item { padding: 8px 0; border-bottom: 1px solid #1e293b; font-size: 14px; }
        .item:last-child { border-bottom: none; }
        .btn { display: inline-block; background-color: #d4a017; color: #0d1117; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .footer { font-size: 12px; color: #94a3b8; text-align: center; margin-top: 25px; border-top: 1px solid #334155; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="logo-title">Agencia N°5801</h1>
            <p style="color: #94a3b8; margin: 5px 0 0 0; font-size: 13px;">Notificación de tu Jugada</p>
        </div>

        <p>Hola <strong>{{ $jugada->user->name }}</strong>,</p>

        @if($jugada->resultado === 'ganadora')
            <div style="text-align: center;">
                <span class="badge badge-ganadora">🎉 ¡BOLETA GANADORA!</span>
            </div>

            <p>¡Tenemos excelentes noticias! Tu jugada <strong>#{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}</strong> ha sido escrutada y resultó premiada.</p>

            <div class="premio-box">
                <span style="font-size: 13px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px;">Premio Obtenido</span>
                <div class="premio-monto">${{ number_format($jugada->monto_premio, 2, ',', '.') }}</div>
                <span style="font-size: 12px; color: #48bb78;">✓ El saldo ya fue acreditado automáticamente en tu Billetera Virtual.</span>
            </div>
        @elseif($jugada->resultado === 'no_ganadora')
            <div style="text-align: center;">
                <span class="badge badge-no-ganadora">NO GANADORA</span>
            </div>

            <p>Te informamos que tu jugada <strong>#{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}</strong> ha sido escrutada tras el sorteo oficial y en esta ocasión no obtuvo premios.</p>
            <p style="color: #94a3b8;">¡Agradecemos tu participación y te deseamos muchos éxitos en tu próxima jugada!</p>
        @else
            <div style="text-align: center;">
                <span class="badge badge-procesada">✓ BOLETA PROCESADA</span>
            </div>

            <p>Tu jugada <strong>#{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}</strong> fue procesada y validada exitosamente por la agencia. Ya está participando oficialmente del sorteo.</p>
        @endif

        {{-- Detalle de la Boleta --}}
        <div class="details-box">
            <div class="item"><strong>N° de Ticket:</strong> #{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}</div>
            <div class="item"><strong>Fecha de Emisión:</strong> {{ $jugada->created_at->format('d/m/Y H:i') }}</div>
            <div class="item"><strong>Importe Apostado:</strong> ${{ number_format($jugada->monto_total, 2, ',', '.') }}</div>

            @if(!empty($jugada->detalle_escrutinio))
                <div class="item" style="color: #48bb78;">
                    <strong>Detalle de Aciertos:</strong><br>
                    @foreach($jugada->detalle_escrutinio as $d)
                        @if(!empty($d['premio']) && $d['premio'] > 0)
                            <div>• Acertó {{ $d['numero'] ?? implode(', ', $d['numeros_acertados'] ?? []) }} (Premio: ${{ number_format($d['premio'], 2, ',', '.') }})</div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div style="text-align: center;">
            <a href="{{ route('jugadas.index') }}" class="btn">Ver en Mi Cuenta</a>
        </div>

        <div class="footer">
            Agencia N°5801 — Tu agencia oficial de confianza.<br>
            Este es un correo automático, por favor no respondas a este mensaje.
        </div>
    </div>
</body>
</html>

