# Plan de Implementación — Fase 5
## Notificaciones SMTP — Agencia N°5801

---

## Objetivo
Implementar el envío automático de un correo electrónico al Administrador cada vez que un usuario crea una jugada. El sistema utilizará **Mailtrap** para pruebas en el entorno local (tal como está configurado actualmente en el archivo `.env`), y deberá manejar de forma robusta las posibles fallas en el envío cambiando el estado de la jugada a `error_envio` y dejando constancia en el log de auditoría.

---

## 1. Mailable y Plantilla de Correo

### [NEW] `app/Mail/NuevaJugadaMail.php`
Se generará una clase *Mailable* utilizando el comando Artisan:
```bash
php artisan make:mail NuevaJugadaMail
```
**Lógica interna:**
- El constructor recibirá la instancia de la `$jugada` recién creada.
- El asunto del correo será descriptivo, e.g.: `[Agencia N°5801] Nueva Jugada Simulada - Ticket #<UUID>`
- Se enlazará con la vista Blade que se detalla a continuación.

### [NEW] `resources/views/mail/nueva_jugada.blade.php`
Se creará una vista HTML en línea optimizada para clientes de correo (sin CSS externo complejo) que cumpla con el requerimiento del **SRS** de mostrar un "resumen tabular":
- Datos del Cliente (Nombre, DNI, Email).
- Datos de la Transacción (UUID del Ticket, Fecha y Hora).
- Datos de la Apuesta (Modalidad, Números formateados, Costo simulado).
- Disclaimer aclarando que es una notificación generada automáticamente por el entorno de simulación.

---

## 2. Lógica en el Controlador (Manejo de Errores y Transaccionalidad)

### [MODIFY] `app/Http/Controllers/JugadaController.php`
Actualmente el método `store` guarda la jugada, pero deja un comentario `[TODO Fase 5]`. Reemplazaremos ese bloque con el siguiente flujo:

```php
try {
    // Para simplificar la captura del error en tiempo real (requisito del SRS), 
    // forzaremos el envío síncrono del mail a la dirección del administrador.
    $adminEmail = 'administracion.agencia.sim@gmail.com'; 
    \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\NuevaJugadaMail($jugada));

} catch (\Exception $e) {
    // Si falla el servidor SMTP (ej. caída de red o credenciales inválidas):
    
    // 1. Cambiamos el estado de la jugada
    $jugada->update(['estado' => 'error_envio']);
    
    // 2. Registramos el incidente en la tabla audit_logs
    \App\Models\AuditLog::create([
        'user_id' => $user->id,
        'accion' => 'smtp_failure',
        'descripcion' => 'Fallo al enviar notificación SMTP de la jugada ' . $jugada->id . '. Error: ' . $e->getMessage(),
        'ip' => request()->ip()
    ]);
}
```

> [!TIP]
> Usaremos `Mail::send()` en lugar de `Mail::queue()` en esta fase para poder atrapar la excepción directamente en el bloque `try-catch` dentro del controlador y actualizar el estado de la base de datos a `error_envio` tal como pide el documento SRS. (En producción masiva se suele hacer esto dentro de un Job asíncrono con método `failed()`, pero para nuestro simulador el try-catch es la aproximación más directa y fiel al documento).

---

## 3. Configuración y Pruebas (Testing)

### Actualización de Entorno
Asegurarnos de que el `.env` esté preparado para capturar los correos. (Mailtrap ya se encuentra preconfigurado en las variables `MAIL_MAILER` y `MAIL_HOST` por defecto en las instalaciones recientes de Laravel, aunque en la implementación actual usaremos las credenciales ficticias / de pruebas que tengamos a mano para forzar un envío exitoso y un envío fallido en los tests).

### [NEW] `tests/Feature/NotificacionSmtpTest.php`
Se creará un conjunto de pruebas **Pest** para auditar específicamente esta funcionalidad.

| Nombre del Test | Objetivo de la Prueba |
|-----------------|-----------------------|
| `envio_exitoso_mantiene_estado_pendiente` | Usa `Mail::fake()`. Crea una jugada válida y afirma que `Mail::assertSent()` funciona y que la jugada se guarda en BD como `pendiente`. |
| `falla_smtp_cambia_estado_a_error_envio_y_audita` | Hace un mock (doble de prueba) de la clase de Mail para lanzar una Excepción (simulando que se cae el servidor de Gmail/Mailtrap). Verifica que el estado de la jugada cambie a `error_envio` y que aparezca un registro en la tabla `audit_logs` con la acción `smtp_failure`. |

---

## Open Questions
- **Dirección del Administrador**: Actualmente estoy configurando que envíe a `administracion.agencia.sim@gmail.com` tal como indica el ejemplo del **SRS**. ¿Preferís que saque este correo directamente de la base de datos buscando al usuario que tiene `rol = 'admin'`? 

> [!IMPORTANT]
> Revisa la pregunta de arriba. Si estás de acuerdo con el plan en general (o querés resolver la duda del mail), dame luz verde para empezar a codificar.
