<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Jugada - Agencia N°5801</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .agencia-nombre {
            font-size: 24px;
            font-weight: bold;
            color: #1a202c;
            margin: 0;
        }
        .agencia-datos {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }
        .titulo {
            text-align: center;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
            color: #2d3748;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .info-table th {
            text-align: left;
            padding: 12px;
            background-color: #f7fafc;
            border-bottom: 1px solid #e2e8f0;
            width: 30%;
            font-weight: bold;
            color: #4a5568;
        }
        .info-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #1a202c;
        }
        .monto-total {
            font-size: 18px;
            font-weight: bold;
            color: #2b6cb0;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .disclaimer {
            font-size: 11px;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(226, 232, 240, 0.4);
            z-index: -1;
            white-space: nowrap;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <div class="watermark">SIMULACIÓN NO VÁLIDA</div>

    <div class="header">
        <img src="{{ $logoPath }}" alt="Logo Agencia" class="logo">
        <h1 class="agencia-nombre">Agencia N°5801</h1>
        <p class="agencia-datos">Av. Siempre Viva 123, Springfield • Tel: (555) 123-4567<br>CUIT: 30-12345678-9 • Ingresos Brutos: 123456</p>
    </div>

    <h2 class="titulo">Comprobante de Jugada</h2>

    <table class="info-table">
        <tr>
            <th>Ticket ID</th>
            <td style="font-family: monospace; font-size: 14px;">{{ $jugada->idempotency_token }}</td>
        </tr>
        <tr>
            <th>Fecha y Hora</th>
            <td>{{ $jugada->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <th>Usuario</th>
            <td>{{ $usuario->name }} (DNI: {{ $usuario->dni }})</td>
        </tr>
        <tr>
            <th>Modalidad</th>
            <td style="text-transform: capitalize;">
                @if($jugada->modalidad === 'lotoplus') Loto Plus @elseif($jugada->modalidad === 'quini6') Quini 6 @else {{ $jugada->modalidad }} @endif
            </td>
        </tr>
        <tr>
            <th>Jugada</th>
            <td style="font-size: 16px; font-weight: bold;">{{ $numerosFormateados }}</td>
        </tr>
        <tr>
            <th>Costo Total</th>
            <td class="monto-total">$ {{ number_format($jugada->monto_total, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Estado</th>
            <td style="text-transform: uppercase; font-weight: bold; color: {{ $jugada->estado === 'procesado' ? '#38a169' : '#d69e2e' }};">
                {{ $jugada->estado }}
            </td>
        </tr>
    </table>

    <div class="footer">
        <p class="disclaimer">
            ⚠ Atención: Este comprobante es exclusivamente una simulación generada con fines académicos.<br>
            No posee ningún valor comercial, legal ni representativo de una apuesta real.
        </p>
    </div>

</body>
</html>
