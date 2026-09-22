# Plan de Implementación — Fase 4
## Generación de Comprobantes PDF — Agencia N°5801

---

## Revisión de Documentos: Cambios Detectados

Antes de presentar el plan, comparo los documentos originales con el estado actual del proyecto para detectar divergencias.

### ✅ Sin cambios respecto al plan original
- El SRS (Sección 6 — *Emisión de Comprobantes*) no fue modificado.
- El plan en `implementation_plan1` describe correctamente lo que quedó pendiente para Fase 4.
- `JugadaController@downloadPdf` existe como **stub** que devuelve `abort(501)` — exactamente lo esperado.

### 📝 Contexto confirmado del estado actual
| Elemento | Estado |
|----------|--------|
| `JugadaController@downloadPdf` | Stub `abort(501)` — listo para implementar |
| `barryvdh/laravel-dompdf` | ❌ **No instalado** — primer paso |
| `resources/views/pdf/` | ❌ No existe todavía |
| Logo de la agencia | ✅ Generado en Fase 1 (artifact `logo_agencia_*.jpg`) |
| `MAIL_FROM_NAME` en .env | Configurado como `"Agencia N°5801 (Sistema)"` |
| `APP_NAME` | `"Agencia N°5801"` |
| SMTP en .env | Apunta a Mailtrap (placeholder `<TU_USUARIO_MAILTRAP>`) |

> [!NOTE]
> El SRS especifica que el PDF también debe ser descargable **por el Administrador** desde el Backoffice (sección §3). Aunque el backoffice completo es Fase 6, agregaremos soporte de autorización dual desde ya en `downloadPdf` para que el admin también pueda descargarlo.

---

## Alcance de la Fase 4

```
1 → Instalar barryvdh/laravel-dompdf
2 → Copiar logo a public/images/ (accesible para DomPDF)
3 → Vista Blade del comprobante: resources/views/pdf/comprobante.blade.php
4 → Implementar JugadaController@downloadPdf (reemplaza el stub 501)
5 → Tests Pest: ComprobantePdfTest
```

---

## Paso 1 — Instalar `barryvdh/laravel-dompdf`

```bash
composer require barryvdh/laravel-dompdf
```

> [!NOTE]
> DomPDF requiere las extensiones PHP `gd`, `mbstring` y `dom`. En Laragon estas suelen estar activas por defecto. Se verificará al correr el primer test.

---

## Paso 2 — Logo accesible para DomPDF

### [MODIFY] Logo a `public/images/logo.png`

DomPDF renderiza HTML estático; **no puede acceder a `storage/` ni a rutas de Vite**. El logo debe estar en `public/images/` y referenciarse con una ruta absoluta de sistema de archivos dentro de la vista Blade del PDF.

```php
// En la vista del PDF se usará:
$logoPath = public_path('images/logo.png');
// <img src="{{ $logoPath }}"> en el Blade
```

El logo generado en Fase 1 se copiará a `public/images/logo.png` durante la implementación.

---

## Paso 3 — Vista `resources/views/pdf/comprobante.blade.php`

### [NEW] `resources/views/pdf/comprobante.blade.php`

Vista HTML pura (sin `@extends`) optimizada para impresión en A4. DomPDF no soporta Tailwind ni CSS externo, por lo que **todos los estilos van inline o en `<style>` embebido**.

**Estructura del documento:**

```
┌─────────────────────────────────────┐
│  [LOGO]   Agencia N°5801            │  ← Encabezado institucional
│           Datos de contacto simul.  │
├─────────────────────────────────────┤
│  COMPROBANTE DE JUGADA SIMULADA     │  ← Título
│                                     │
│  Ticket ID: xxxxxxxx-xxxx-...       │  ← UUID de la jugada
│  Fecha/Hora: 22/09/2026 17:23:45   │  ← created_at del servidor
│  Usuario: Juan Pérez (DNI 12345678) │
├─────────────────────────────────────┤
│  Modalidad:   Quini 6               │  ← Datos de la jugada
│  Números:     01, 07, 14, 22, 33, 44│
│  Monto:       $1.200,00             │
│  Estado:      Pendiente             │
├─────────────────────────────────────┤
│  ⚠ Exclusivamente una simulación   │  ← Disclaimer / marca de agua
│    con fines académicos             │
└─────────────────────────────────────┘
```

**Variables que recibe la vista:**
| Variable | Origen |
|----------|--------|
| `$jugada` | Modelo `Jugada` con relaciones eager-loaded |
| `$usuario` | `$jugada->user` |
| `$logoPath` | `public_path('images/logo.png')` |
| `$detalle` | El modelo de detalle correspondiente a la modalidad |
| `$numerosFormateados` | String generado en el controller (ej. `01, 07, 14`) |

**Lógica de presentación de números** (se resuelve en el controller antes de pasarlo a la vista):

| Modalidad | Formato en PDF |
|-----------|---------------|
| `quiniela` | `N° XXXX — Posición Y — Jurisdicción Z — $importe` |
| `quini6` | `01, 07, 14, 22, 33, 44` |
| `lotoplus` | `01, 07, 14, 22, 33, 44 — Plus: 5` |
| `loto5` | `01, 07, 14, 22, 33` |
| `poceada` | `01, 07, 14, 22, 33, 44, 55, 66` |

---

## Paso 4 — `JugadaController@downloadPdf`

### [MODIFY] [`JugadaController.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/JugadaController.php)

Reemplaza el stub actual `abort(501)` con la implementación completa.

**Lógica del método:**

```
1. Autorización dual:
   - Si el usuario es CLIENTE → solo puede descargar sus propias jugadas (user_id === Auth::id())
   - Si el usuario es ADMIN → puede descargar cualquier jugada
   - Cualquier otro caso → abort(403)

2. Cargar la jugada con todas las relaciones de detalle (eager load)

3. Preparar variable $numerosFormateados según modalidad

4. Generar PDF con DomPDF:
   - Instanciar Pdf::loadView('pdf.comprobante', [...])
   - setPaper('A4', 'portrait')
   - stream() o download() con nombre descriptivo

5. Retornar como descarga:
   - Nombre de archivo: "comprobante_{$jugada->id}_{timestamp}.pdf"
```

**Firma del método actualizada:**
```php
public function downloadPdf(Jugada $jugada): \Symfony\Component\HttpFoundation\Response
```

> [!IMPORTANT]
> La verificación de autorización usa `Auth::user()->rol` (campo existente en la tabla `users` desde Fase 1), evitando instalar un paquete extra de autorización para este caso simple.

---

## Paso 5 — Tests Pest

### [NEW] `tests/Feature/ComprobantePdfTest.php`

| Test | Qué verifica |
|------|-------------|
| `cliente_puede_descargar_su_propia_jugada_pdf` | GET `/jugadas/{jugada}/pdf` → 200, Content-Type `application/pdf` |
| `cliente_no_puede_descargar_jugada_de_otro` | Jugada de otro user → 403 |
| `invitado_no_puede_descargar_pdf` | Sin auth → redirect al login |
| `admin_puede_descargar_cualquier_jugada_pdf` | Admin accede a jugada de un cliente → 200 |
| `pdf_contiene_datos_de_la_jugada` | El contenido del response incluye el UUID de la jugada |

---

## Rutas (sin cambios nuevos)

La ruta ya existe desde Fase 3:
```
GET /jugadas/{jugada}/pdf → JugadaController@downloadPdf [auth]
```

No se requiere agregar rutas nuevas. El admin accederá a la misma ruta desde el backoffice en Fase 6.

---

## Verificación al Finalizar Fase 4

### Comandos
```bash
# Instalar DomPDF
composer require barryvdh/laravel-dompdf

# Tests de PDF
php artisan test tests/Feature/ComprobantePdfTest.php --compact

# Todos los tests (no deben romperse)
php artisan test --compact
```

### Manual
1. Login como cliente → ir a `/jugadas` → hacer click en botón "📄 PDF" de cualquier jugada → debe descargarse un PDF.
2. Verificar que el PDF contiene: logo, nombre de agencia, ticket ID (UUID), fecha, números, monto, disclaimer.
3. Intentar acceder a `/jugadas/{id_de_otro_usuario}/pdf` manualmente → debe devolver 403.
4. Login como admin → acceder a la misma ruta → debe descargarse normalmente.

---

## Fases Siguientes (referencia)

| Fase | Alcance |
|------|---------|
| **5** | `NuevaJugadaMail`, SMTP Mailtrap real, estado `error_envio`, reintento |
| **6** | Backoffice admin completo (grilla, filtros, cambio de estado, precios) |
| **7** | SessionTimeout middleware, JugadaPolicy, backup .bat |
