<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; padding: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; border-top: 5px solid #d97706; }
        h1 { color: #0f172a; margin-top: 0; }
        .details { background-color: #f8fafc; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .item { margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        .footer { font-size: 12px; color: #64748b; margin-top: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Nueva Boleta Registrada!</h1>
        <p>Hola Administrador,</p>
        <p>Se ha registrado una nueva jugada en el sistema. A continuación los detalles:</p>
        
        <div class="details">
            <p><strong>N° de Boleta:</strong> #{{ str_pad($jugada->id, 8, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Cliente:</strong> {{ $jugada->user->name }} ({{ $jugada->user->email }})</p>
            <p><strong>Monto Total:</strong> ${{ number_format($jugada->monto_total, 2, ',', '.') }}</p>
            <p><strong>Estado Actual:</strong> {{ strtoupper($jugada->estado) }}</p>
            
            <hr style="border:0; border-top: 1px solid #cbd5e1; margin: 20px 0;">
            
            <h3>Detalle de Apuestas:</h3>
            @foreach($jugada->detallesQuiniela as $q)
                <div class="item">
                    <strong>Quiniela ({{ ucfirst($q->jurisdiccion) }})</strong><br>
                    N° {{ $q->numero }} | Posición {{ $q->posicion }} | $ {{ number_format($q->importe, 2, ',', '.') }}
                </div>
            @endforeach

            @foreach($jugada->detallesQuini6 as $q)
                <div class="item"><strong>Quini 6:</strong> {{ implode(', ', $q->numeros) }}</div>
            @endforeach

            @foreach($jugada->detallesLotoPlus as $q)
                <div class="item"><strong>Loto Plus:</strong> {{ implode(', ', $q->numeros) }} (Plus: {{ $q->numero_plus }})</div>
            @endforeach

            @foreach($jugada->detallesLoto5 as $q)
                <div class="item"><strong>Loto 5:</strong> {{ implode(', ', $q->numeros) }}</div>
            @endforeach

            @foreach($jugada->detallesPoceada as $q)
                <div class="item"><strong>Poceada:</strong> {{ implode(', ', $q->numeros) }}</div>
            @endforeach
        </div>

        <p>Revisá el panel de administración para cambiar el estado a "Procesado" una vez impactada.</p>
        
        <div class="footer">
            Agencia N°5801 - Sistema Automático
        </div>
    </div>
</body>
</html>
