<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Restablecer Contraseña - Agencia N°5801</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #0f172a; margin: 0; padding: 20px; color: #e2e8f0; }
        .card { background-color: #1e293b; max-width: 580px; margin: 0 auto; border-radius: 8px; border: 1px solid #334155; border-top: 4px solid #d4af37; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 1px solid #334155; padding-bottom: 15px; }
        .header h1 { font-size: 20px; color: #d4af37; margin: 0 0 5px 0; text-transform: uppercase; letter-spacing: 0.05em; }
        .header p { font-size: 13px; color: #94a3b8; margin: 0; }
        .content p { font-size: 15px; line-height: 1.6; color: #cbd5e1; margin: 12px 0; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; background-color: #d4af37; color: #0f172a !important; font-weight: 700; font-size: 15px; text-decoration: none; padding: 12px 28px; border-radius: 6px; }
        .note { background-color: rgba(212, 175, 55, 0.08); border-left: 3px solid #d4af37; padding: 12px 15px; border-radius: 4px; font-size: 13px; color: #cbd5e1; margin: 20px 0; }
        .fallback { font-size: 12px; color: #64748b; word-break: break-all; margin-top: 25px; border-top: 1px solid #334155; padding-top: 15px; }
        .footer { text-align: center; font-size: 12px; color: #64748b; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Agencia N°5801</h1>
            <p>Sistema de Gestión y Simulación de Apuestas</p>
        </div>

        <div class="content">
            <p>Hola, <strong>{{ $user->name }}</strong>:</p>
            <p>Recibiste este correo electrónico porque solicitaste restablecer la contraseña de tu cuenta en Agencia N°5801.</p>
            
            <div class="btn-container">
                <a href="{{ $url }}" class="btn">Restablecer mi Contraseña</a>
            </div>

            <div class="note">
                <strong>Importante:</strong> Este enlace de recuperación expirará en <strong>60 minutos</strong>. Si tú no solicitaste este cambio, no es necesario realizar ninguna acción; tu contraseña actual continuará siendo segura.
            </div>

            <div class="fallback">
                Si tienes problemas al hacer clic en el botón, copia y pega el siguiente enlace en tu navegador web:<br>
                <a href="{{ $url }}" style="color: #d4af37;">{{ $url }}</a>
            </div>
        </div>

        <div class="footer">
            Este es un correo automático enviado por Agencia N°5801. Por favor, no respondas a este mensaje.
        </div>
    </div>
</body>
</html>

