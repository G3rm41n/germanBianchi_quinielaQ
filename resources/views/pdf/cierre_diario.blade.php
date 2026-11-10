<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cierre de Caja - {{ $arqueo['fecha']->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            border-bottom: 2px solid #d4a017;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header-table {
            width: 100%;
        }
        .logo {
            max-height: 45px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 3px 0 0 0;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin: 15px 0 8px 0;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            text-transform: uppercase;
        }
        .box-totales {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .box-totales td {
            padding: 10px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .box-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .box-value {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
        }
        .box-value-success {
            color: #15803d;
        }
        .box-value-danger {
            color: #b91c1c;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
        }
        table.data-table tfoot td {
            background-color: #f8fafc;
            font-weight: bold;
            border-top: 2px solid #cbd5e1;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .firmas {
            margin-top: 35px;
            width: 100%;
        }
        .firma-box {
            width: 45%;
            text-align: center;
            border-top: 1px solid #475569;
            padding-top: 5px;
            font-size: 10px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    {{-- Encabezado Institucional --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 25%;">
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo" class="logo">
                    @else
                        <strong style="color: #d4a017; font-size: 16px;">AGENCIA N°5801</strong>
                    @endif
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="title">Planilla Oficial de Cierre Diario</div>
                    <div class="subtitle">Arqueo de Caja y Liquidación Contable de Lotería</div>
                </td>
                <td style="width: 25%; text-align: right; font-size: 10px;">
                    <strong>Fecha:</strong> {{ $arqueo['fecha']->format('d/m/Y') }}<br>
                    <strong>Emisión:</strong> {{ now()->format('d/m/Y H:i:s') }}<br>
                    <strong>Responsable:</strong> {{ $admin->name }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Resumen Estricto de Caja: Total Recaudado, Premios Pagados y Balance Neto --}}
    <table class="box-totales">
        <tr>
            <td style="background-color: #f8fafc; width: 33.3%;">
                <div class="box-label">Total Recaudado (Apuestas)</div>
                <div class="box-value">${{ number_format($arqueo['total_recaudado'], 2, ',', '.') }}</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">{{ $arqueo['total_boletas'] }} boletas vendidas</div>
            </td>
            <td style="background-color: #f8fafc; width: 33.3%;">
                <div class="box-label">Premios Pagados (Egresos)</div>
                <div class="box-value box-value-danger">${{ number_format($arqueo['total_premios'], 2, ',', '.') }}</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">{{ $arqueo['boletas_ganadoras'] }} boletas premiadas</div>
            </td>
            <td style="background-color: #f8fafc; width: 33.3%;">
                <div class="box-label">Balance Neto de Caja</div>
                @php
                    $esPositivo = $arqueo['balance_neto'] >= 0;
                    $signo = $arqueo['balance_neto'] > 0 ? '+' : '';
                @endphp
                <div class="box-value {{ $esPositivo ? 'box-value-success' : 'box-value-danger' }}">
                    {{ $signo }}${{ number_format($arqueo['balance_neto'], 2, ',', '.') }}
                </div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                    {{ $esPositivo ? 'Superávit operativo' : 'Déficit por premios' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Matriz por Juego --}}
    <div class="section-title">Desglose por Modalidad de Juego</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Modalidad</th>
                <th class="text-center">Boletas</th>
                <th class="text-right">Recaudado</th>
                <th class="text-right">Premios Pagados</th>
                <th class="text-right">Balance Neto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($arqueo['desglose_juegos'] as $d)
                @php
                    $bal = $d['balance'];
                    $sig = $bal > 0 ? '+' : '';
                @endphp
                <tr>
                    <td><strong>{{ $d['nombre'] }}</strong></td>
                    <td class="text-center">{{ $d['cantidad'] }}</td>
                    <td class="text-right">${{ number_format($d['recaudado'], 2, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($d['premios'], 2, ',', '.') }}</td>
                    <td class="text-right"><strong>{{ $sig }}${{ number_format($bal, 2, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>TOTALES</td>
                <td class="text-center">{{ $arqueo['total_boletas'] }}</td>
                <td class="text-right">${{ number_format($arqueo['total_recaudado'], 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format($arqueo['total_premios'], 2, ',', '.') }}</td>
                <td class="text-right">{{ $signo }}${{ number_format($arqueo['balance_neto'], 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Listado de Boletas --}}
    <div class="section-title">Detalle de Boletas del Día ({{ $arqueo['jugadas']->count() }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Ticket / Hora</th>
                <th style="width: 30%;">Cliente / DNI</th>
                <th class="text-center" style="width: 15%;">Estado</th>
                <th class="text-center" style="width: 15%;">Resultado</th>
                <th class="text-right" style="width: 12%;">Premio</th>
                <th class="text-right" style="width: 13%;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($arqueo['jugadas'] as $j)
                <tr>
                    <td>
                        #{{ str_pad($j->id, 8, '0', STR_PAD_LEFT) }}<br>
                        <span style="color: #64748b; font-size: 9px;">{{ $j->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        {{ $j->user?->name ?? 'Cliente' }}<br>
                        <span style="color: #64748b; font-size: 9px;">DNI {{ $j->user?->dni }}</span>
                    </td>
                    <td class="text-center">{{ strtoupper($j->estado) }}</td>
                    <td class="text-center">{{ strtoupper(str_replace('_', ' ', $j->resultado)) }}</td>
                    <td class="text-right">${{ number_format($j->monto_premio, 2, ',', '.') }}</td>
                    <td class="text-right"><strong>${{ number_format($j->monto_total, 2, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #64748b;">
                        Sin boletas registradas en esta fecha.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Firmas Contables --}}
    <table class="firmas">
        <tr>
            <td style="width: 50%; padding-right: 30px;">
                <div class="firma-box">
                    <strong>Firma y Sello del Agenciero</strong><br>
                    {{ $admin->name }} — DNI: {{ $admin->dni }}
                </div>
            </td>
            <td style="width: 50%; padding-left: 30px;">
                <div class="firma-box">
                    <strong>Conformidad Auditoría / Supervisión</strong><br>
                    Agencia Oficial N°5801
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento oficial de control interno emitido por el sistema automatizado de Agencia N°5801. Consérvese para auditorías de lotería.
    </div>

</body>
</html>

