# Plan de Integración — Fase 8
## Filtros Avanzados (Categorías y Fechas), Jugadas Rápidas Aleatorias, Seeders de Prueba y Sistema de Saldo / Billetera

---

## 1. Diagnóstico del Estado Actual y Objetivos

En la versión actual (`0.0.6`), la plataforma cuenta con:
- Sistema transaccional multi-jugada con 5 modalidades ([Quiniela](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleQuiniela.php), [Quini 6](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleQuini6.php), [Loto Plus](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleLotoPlus.php), [Loto 5](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleLoto5.php), [Poceada](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleQuinielaPoceada.php)).
- Módulo financiero con desglose por categorías y balance de ganancias/pérdidas ([BalanceService](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Services/BalanceService.php)).
- Panel de cliente y panel de administrador con escrutinio y emisión de comprobantes PDF.

### Requerimientos de la Fase 8
1. **Filtros Avanzados:** Filtrar boletas por categoría de juego y por fechas (fecha puntual y rango entre dos fechas), tanto para el cliente como para el administrador.
2. **Generador de Números Aleatorios:** Botón "Completar al azar" / "Jugada Aleatoria" en el formulario de creación de jugadas para cada modalidad.
3. **Seeders de Datos de Prueba:** Carga automática de jugadas históricas realistas distribuidas en fechas, modalidades, estados y resultados para pruebas inmediatas.
4. **Sistema de Saldo y Billetera Virtual (Wallet):** Control real de cuánto dinero tiene el usuario y cuánto gastó, con débito al jugar, acreditación de premios y recarga simulada de saldo.

---

## 2. Especificación Técnica por Módulo

### Módulo 1: Billetera Virtual (Saldo y Gastos)

#### Modelo de Datos:
* **Migración:** `add_saldo_to_users_table`:
  ```php
  Schema::table('users', function (Blueprint $table) {
      $table->decimal('saldo', 12, 2)->default(10000.00)->after('rol');
  });
  ```
  *(Por defecto se asigna un saldo inicial de $10.000 para que los usuarios registrados puedan probar inmediatamente).*

#### Métodos en el Modelo [User](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/User.php):
* `debitarSaldo(float $monto): void`: Descuenta el importe y valida que el saldo sea suficiente.
* `acreditarSaldo(float $monto): void`: Suma importe al saldo disponible.

#### Lógica Transaccional en [JugadaController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/JugadaController.php):
* Al enviar el formulario de jugada (`store`):
  1. Verificar si `$user->saldo < $montoTotalBoleta`. Si no alcanza, redirigir con mensaje de error `"Saldo insuficiente ($X disponible). Necesitas $Y."`.
  2. Dentro de `DB::transaction`, debitar el costo de la boleta del saldo del usuario.

#### Acreditación de Premios en [JugadaAdminController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/Admin/JugadaAdminController.php):
* Al marcar una jugada como `ganadora` con un monto de premio:
  * Si la jugada no había sido liquidada previamente, acreditar `$montoPremio` al saldo del cliente dueño de la boleta.
  * Registrar la acreditación en `audit_logs`.

#### Recarga Simulada de Saldo:
* Endpoint `POST /billetera/recargar` para que el cliente pueda sumar fondos de prueba (ej: $5.000, $10.000, $20.000) si se queda sin saldo.

#### Interfaz de Usuario:
* **Barra de navegación ([`layouts/app.blade.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/views/layouts/app.blade.php)):** Indicador visible junto al nombre: `Saldo: $XX.XXX,XX`.
* **Tarjetas en [`views/jugadas/index.blade.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/views/jugadas/index.blade.php):**
  * Saldo Disponible en Cuenta (con botón rápido "Cargar Saldo").
  * Total Gastado en Apuestas.
  * Total Premios Cobrados.
  * Balance Neto.

---

### Módulo 2: Filtros Avanzados (Categorías y Fechas)

#### Parámetros HTTP soportados:
* `categoria`: `quiniela` | `quini6` | `lotoplus` | `loto5` | `poceada`.
* `fecha`: fecha específica (`YYYY-MM-DD`).
* `fecha_desde`: inicio del rango (`YYYY-MM-DD`).
* `fecha_hasta`: fin del rango (`YYYY-MM-DD`).
* `estado`: `pendiente` | `procesado` | `error_envio` (en admin).
* `resultado`: `pendiente_sorteo` | `ganadora` | `no_ganadora` (en admin).

#### Lógica de Filtrado en Controladores:
* **Por Categoría:**
  ```php
  $query->when($request->filled('categoria'), function ($q) use ($request) {
      match ($request->categoria) {
          'quiniela' => $q->whereHas('detallesQuiniela'),
          'quini6'   => $q->whereHas('detallesQuini6'),
          'lotoplus' => $q->whereHas('detallesLotoPlus'),
          'loto5'    => $q->whereHas('detallesLoto5'),
          'poceada'  => $q->whereHas('detallesPoceada'),
          default    => $q,
      };
  });
  ```
* **Por Fecha / Rango:**
  ```php
  $query->when($request->filled('fecha'), fn ($q) => $q->whereDate('created_at', $request->fecha));
  $query->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->fecha_desde));
  $query->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->fecha_hasta));
  ```

#### Componente Visual de Filtros:
Barra de filtros integrada sobre las tablas tanto en:
1. Vista de Usuario: [`views/jugadas/index.blade.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/views/jugadas/index.blade.php).
2. Panel Administrador: [`views/admin/dashboard.blade.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/views/admin/dashboard.blade.php).
3. Detalle de Usuario en Admin: [`views/admin/users/show.blade.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/views/admin/users/show.blade.php).

Incluye inputs de fecha tipo `date`, select de categoría, botón "Aplicar Filtros" y botón "Limpiar".

---

### Módulo 3: Jugadas Rápidas Aleatorias (Quick Pick)

En [`resources/views/jugadas/create.blade.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/views/jugadas/create.blade.php) y [`resources/js/jugadas.js`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/js/jugadas.js):

Se añade un botón destacado **"Completar al azar"** en la cabecera de cada modalidad:
* **Quiniela:**
  * Genera un número aleatorio de 1 a 4 cifras (ej. `0421`).
  * Selecciona aleatoriamente una posición o rango (ej: a la cabeza o del 1 al 10).
  * Selecciona una jurisdicción al azar (Nación, Provincia, Santa Fe, Córdoba).
  * Sugiere un importe predeterminado (ej: $500).
* **Quini 6:**
  * Elige 6 números únicos aleatorios entre 00 y 45 y los marca visualmente en la cuadrícula.
* **Loto Plus:**
  * Elige 6 números únicos aleatorios entre 0 y 45 y un número Plus aleatorio entre 0 y 9.
* **Loto 5:**
  * Elige 5 números únicos aleatorios entre 0 y 36.
* **Quiniela Poceada:**
  * Elige 8 números únicos aleatorios entre 00 y 99.

El evento actualiza el carrito y el cálculo de costo en tiempo real con las mismas validaciones.

---

### Módulo 4: Seeders de Datos de Prueba

Se creará la clase `database/seeders/JugadasSeeder.php`:
* Poblará la base de datos con **25 boletas de prueba** distribuidas en:
  * Fechas escalonadas en los últimos 30 días (para verificar inmediatamente los filtros de fecha y rangos).
  * Las 5 modalidades representadas de forma individual y combinada.
  * Diversidad de estados: `procesado`, `pendiente`, `error_envio`.
  * Diversidad de resultados: `ganadora` (con montos de premio realistas), `no_ganadora` y `pendiente_sorteo`.
  * Vinculadas tanto al usuario cliente principal como a los demás clientes generados por `ClientesSeeder`.
* Se invocará desde `DatabaseSeeder.php`.

---

## 3. Plan de Implementación Paso a Paso

1. **Paso 1: Migración y Modelo User**:
   * Crear migración para el campo `saldo` en `users`.
   * Agregar métodos auxiliares de saldo en [User](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/User.php).
2. **Paso 2: Billetera y Control de Fondos**:
   * Actualizar [JugadaController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/JugadaController.php) con la validación de saldo y débito atómico.
   * Actualizar [JugadaAdminController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/Admin/JugadaAdminController.php) para acreditar premios en el saldo.
   * Crear ruta y método de recarga simulada (`/billetera/recargar`).
   * Mostrar saldo en la barra de navegación ([`layouts/app.blade.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/views/layouts/app.blade.php)).
3. **Paso 3: Filtros de Categoría y Fechas**:
   * Implementar los filtros en `JugadaController@index`, `DashboardController@index` y `UserController@show`.
   * Integrar el formulario visual de filtros en las vistas Blade.
4. **Paso 4: Botón de Jugada Aleatoria en Frontend**:
   * Agregar los botones en [`resources/views/jugadas/create.blade.php`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/views/jugadas/create.blade.php).
   * Implementar la lógica matemática aleatoria en [`resources/js/jugadas.js`](file:///c:/laragon/www/germanBianchi_agenciaQ/resources/js/jugadas.js).
5. **Paso 5: Seeders de Prueba**:
   * Desarrollar `JugadasSeeder.php` y registrarlo en `DatabaseSeeder.php`.
   * Probar ejecución con `php artisan db:seed`.
6. **Paso 6: Pruebas y Validación**:
   * Crear pruebas con Pest en `tests/Feature/BilleteraYFiltrosTest.php`.
   * Ejecutar suite completa con `php artisan test --compact`.
   * Formatear con `vendor/bin/pint --format agent`.
