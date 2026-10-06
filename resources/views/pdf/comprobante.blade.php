<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Jugada</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0f172a; padding-bottom: 15px; }
        .logo { max-width: 150px; margin-bottom: 10px; }
        .title { font-size: 24px; font-weight: bold; color: #0f172a; margin: 0; }
        .subtitle { font-size: 12px; color: #64748b; margin: 5px 0 0 0; }
        
        .section { margin-bottom: 20px; }
        .section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; margin-bottom: 10px; color: #0f172a; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table th { background-color: #f8fafc; font-weight: bold; }
        .table td { vertical-align: top; }
        
        .total { text-align: right; font-size: 20px; font-weight: bold; color: #b45309; margin-top: 20px; }
        .footer { margin-top: 50px; font-size: 10px; text-align: center; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        @if($logoPath && file_exists($logoPath))
            <img src="{{ $logoPath }}" alt="Logo Agencia" class="logo">
        @endif
        <h1 class="title">Agencia N°5801</h1>
        <p class="subtitle">Comprobante Oficial de Jugada Múltiple</p>
    </div>

    <div class="section">
        <div class="section-title">Datos de la Boleta</div>
        <table class="table" style="border: none;">
            <tr>
                <td style="border: none; width: 50%;">
                    <strong>N° de Boleta:</strong> #{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}<br>
                    <strong>Fecha:</strong> {{ $jugada->created_at->format('d/m/Y H:i') }}<br>
                    <strong>Estado:</strong> {{ strtoupper($jugada->estado) }}
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    <strong>Cliente:</strong> {{ $usuario->name }}<br>
                    <strong>DNI:</strong> {{ $usuario->dni }}<br>
                    <strong>Email:</strong> {{ $usuario->email }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalle de Jugadas</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Juego</th>
                    <th>Detalle / Números</th>
                    <th style="text-align: right;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jugada->detallesQuiniela as $q)
                <tr>
                    <td><strong>Quiniela ({{ ucfirst($q->jurisdiccion) }})</strong></td>
                    <td>N° {{ $q->numero }} — Posición {{ $q->posicion }}</td>
                    <td style="text-align: right;">${{ number_format($q->importe, 2, ',', '.') }}</td>
                </tr>
                @endforeach

                @foreach($jugada->detallesQuini6 as $q)
                <tr>
                    <td><strong>Quini 6</strong></td>
                    <td>{{ implode(', ', $q->numeros) }}</td>
                    <td style="text-align: right;">-</td>
                </tr>
                @endforeach

                @foreach($jugada->detallesLotoPlus as $q)
                <tr>
                    <td><strong>Loto Plus</strong></td>
                    <td>{{ implode(', ', $q->numeros) }} (Plus: {{ $q->numero_plus }})</td>
                    <td style="text-align: right;">-</td>
                </tr>
                @endforeach

                @foreach($jugada->detallesLoto5 as $q)
                <tr>
                    <td><strong>Loto 5</strong></td>
                    <td>{{ implode(', ', $q->numeros) }}</td>
                    <td style="text-align: right;">-</td>
                </tr>
                @endforeach

                @foreach($jugada->detallesPoceada as $q)
                <tr>
                    <td><strong>Poceada</strong></td>
                    <td>{{ implode(', ', $q->numeros) }}</td>
                    <td style="text-align: right;">-</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="total">
            TOTAL ABONADO: ${{ number_format($jugada->monto_total, 2, ',', '.') }}
        </div>
    </div>

    <div class="footer">
        Este documento es un comprobante no fiscal emitido electrónicamente. <br>
        Identificador de seguridad: {{ $jugada->idempotency_token }} <br>
        Generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>
