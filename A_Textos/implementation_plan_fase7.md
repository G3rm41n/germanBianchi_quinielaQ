# Plan de Integración — Fase 7
## Módulo Financiero: Categorías de Jugadas, Carga de Resultados y Balance de Ganancias / Pérdidas

---

## 1. Diagnóstico y Estado del Proyecto (Etapa Actual: v0.0.5)

El sistema cuenta actualmente con:
- Carrito transaccional multi-modalidad de apuestas ([DetalleQuiniela](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleQuiniela.php), [DetalleQuini6](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleQuini6.php), [DetalleLotoPlus](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleLotoPlus.php), [DetalleLoto5](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleLoto5.php), [DetalleQuinielaPoceada](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/DetalleQuinielaPoceada.php)).
- Estados de gestión de la boleta: `pendiente`, `procesado`, `error_envio` en [Jugada](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/Jugada.php).
- Backoffice administrativo con gestión de precios históricos, listado de jugadas, filtrado y vista detallada por cliente ([UserController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/Admin/UserController.php)).
- **Situación actual**: Todas las jugadas registran su costo (`monto_total`), pero **no existe un concepto de liquidación o resultado económico** (si la jugada resultó ganadora o no, ni el monto del premio). Tampoco hay una vista agrupada por modalidades/categorías de juego.

---

## 2. Decisiones de Diseño y Reglas de Negocio Confirmadas

1. **Determinación de Ganancias y Pérdidas**:
   - **Carga Directa por el Administrador**: En el Backoffice, el administrador podrá marcar cada jugada con su resultado:
     - `pendiente_sorteo`: En juego (no computa como pérdida definitiva ni ganancia aún).
     - `ganadora`: El admin ingresa el importe del premio ganado en pesos ($).
     - `no_ganadora`: La apuesta no obtuvo aciertos (premio = $0, el monto apostado computa como pérdida).
2. **Definiciones Financieras**:
   - **Para el Cliente (Usuario)**:
     - **Total Invertido**: Suma de los montos apostados en todas sus boletas.
     - **Total Ganancias**: Suma de todos los premios otorgados (`monto_premio`).
     - **Total Pérdidas**: Suma de montos apostados en boletas marcadas como `no_ganadora`.
     - **Balance Neto**: `Total Ganancias - Total Invertido` (destacado en verde si es superávit, rojo si es déficit).
   - **Para la Agencia (Administrador)**:
     - **Recaudación Bruta**: Suma del dinero recibido por apuestas de todos los clientes.
     - **Premios Liquidados (Egresos)**: Dinero abonado en premios a jugadores ganadores.
     - **Margen / Ganancia Neta de la Agencia**: `Recaudación Bruta - Premios Liquidados`.
3. **Ubicación en la Interfaz**:
   - **Cliente (`/jugadas`)**: En la cabecera superior del historial existente, con un panel de métricas KPI y la tabla de desglose por categorías.
   - **Admin (`/admin`)**: En el dashboard principal y replicado en el perfil de cada cliente (`/admin/usuarios/{id}`).

---

## 3. Modelo de Datos y Migración

### Migración: `add_resultado_and_premio_to_jugadas_table`
Agregaremos los campos de liquidación económica a la tabla `jugadas`:
```php
Schema::table('jugadas', function (Blueprint $table) {
    $table->enum('resultado', ['pendiente_sorteo', 'ganadora', 'no_ganadora'])
          ->default('pendiente_sorteo')
          ->after('estado');
    $table->decimal('monto_premio', 12, 2)
          ->default(0)
          ->after('resultado');
});
```

### Métodos y Helpers en el Modelo [Jugada](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/Jugada.php)
- `scopeGanadoras($query)`
- `scopeNoGanadoras($query)`
- `scopePendientesSorteo($query)`
- Accessor `balance_neto`: `$this->monto_premio - $this->monto_total`
- Accessor `es_ganadora`: `$this->resultado === 'ganadora'`

---

## 4. Agrupación por Categorías (Modalidades)

Las 5 categorías de juego existentes en el sistema son:
1. 🎰 **Quiniela Tradicional** (`detalles_quiniela`)
2. 6️⃣ **Quini 6** (`detalles_quini6`)
3. ➕ **Loto Plus** (`detalles_lotoplus`)
4. 5️⃣ **Loto 5** (`detalles_loto5`)
5. 🏆 **Quiniela Poceada** (`detalles_quiniela_poceada`)

### Servicio Centralizado de Balance (`App\Services\BalanceService`)
Para evitar duplicar consultas complejas de SQL entre el panel de usuario y el de admin:
```php
namespace App\Services;

class BalanceService
{
    /**
     * Calcula métricas globales (Inversión, Ganancias, Pérdidas, Balance).
     * Si $userId es provisto, calcula para el usuario; si es null, para toda la agencia.
     */
    public function obtenerTotales(?int $userId = null): array;

    /**
     * Devuelve colección con el desglose por cada una de las 5 categorías:
     * - Categoria (Nombre e Icono)
     * - Cantidad de jugadas registradas
     * - Monto total invertido
     * - Ganancias obtenidas
     * - Pérdidas
     * - Balance neto
     */
    public function obtenerDesgloseCategorias(?int $userId = null): array;
}
```

---

## 5. Diseño de Componentes de Interfaz de Usuario (UI/UX)

### 5.1 Vista del Cliente: Cabecera de `resources/views/jugadas/index.blade.php`

```
+-----------------------------------------------------------------------------------------+
| [MIS BOLETAS]                                                        [+ NUEVA JUGADA]   |
|                                                                                         |
| [RESUMEN FINANCIERO PERSONAL]                                                           |
| +-------------------+  +-------------------+  +-------------------+  +----------------+ |
| | TOTAL INVERTIDO   |  | TOTAL GANANCIAS   |  | TOTAL PÉRDIDAS    |  | BALANCE NETO   | |
| | $ 12.500,00       |  | $ 35.000,00       |  | $ 4.500,00        |  | +$ 22.500,00   | |
| +-------------------+  +-------------------+  +-------------------+  +----------------+ |
|                                                                                         |
| [CATEGORÍAS DE JUGADAS]                                                                 |
| +-------------------------------------------------------------------------------------+ |
| | Modalidad             | Jugadas | Invertido ($) | Ganancias ($) | Pérdidas ($) | Balance| |
| +-----------------------+---------+---------------+---------------+--------------+--------+ |
| | 🎰 Quiniela           |    8    | $  8.000,00   | $ 35.000,00   | $ 2.000,00   | +$27.00| |
| | 6️⃣ Quini 6            |    2    | $  2.400,00   | $      0,00   | $ 1.200,00   | -$1.200| |
| | ➕ Loto Plus          |    1    | $  1.500,00   | $      0,00   | $      0,00   | En jueg| |
| | 5️⃣ Loto 5             |    0    | $      0,00   | $      0,00   | $      0,00   |   $0,00| |
| | 🏆 Poceada            |    1    | $  1.000,00   | $      0,00   | $ 1.000,00   | -$1.000| |
| +-----------------------+---------+---------------+---------------+--------------+--------+ |
| | TOTAL CONSOLIDADO     |   12    | $ 12.500,00   | $ 35.000,00   | $ 4.500,00   | +$22.50| |
| +-------------------------------------------------------------------------------------+ |
|                                                                                         |
| [HISTORIAL COMPLETO DE BOLETAS] (Grilla detallada con estado y PDF)                    |
+-----------------------------------------------------------------------------------------+
```

### 5.2 Vista del Administrador: `resources/views/admin/dashboard.blade.php`
1. **Tarjetas de Rendimiento de la Agencia**:
   - 📈 **Recaudación Global** (Total apostado).
   - 💸 **Premios Abonados** (Ganancias de clientes).
   - 🏦 **Margen Neto de la Agencia** (Recaudación - Premios).
2. **Tabla de Balance por Categoría de Juego**:
   - Permite al administrador saber qué modalidad es más rentable para la agencia.
3. **Control Rápido de Escrutinio en la Grilla de Jugadas**:
   - Cada jugada muestra su badge de resultado: `Pendiente de sorteo`, `Ganadora ($X)` o `No Ganadora`.
   - Formulario inline / modal simple:
     - Botón `Marcar Ganadora`: Despliega un input para colocar el monto del premio y guardar.
     - Botón `Marcar No Ganadora`: Asigna premio $0 con 1 click.

---

## 6. Rutas y Controladores Nuevos / Modificados

### Nuevas Rutas Administrativas
- `PATCH /admin/jugadas/{jugada}/resultado` -> `JugadaAdminController@actualizarResultado`

### Controladores Actualizados
- [JugadaController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/JugadaController.php): Inyecta `$totales` y `$categorias` obtenidos de `BalanceService` al método `index()`.
- [DashboardController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/Admin/DashboardController.php): Inyecta métricas globales de la agencia y tabla de categorías de la agencia.
- [UserController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/Admin/UserController.php): En el método `show($user)`, incluye las métricas financieras específicas de ese cliente.

---

## 7. Plan de Ejecución Técnico

1. **Paso 1: Migración**: Crear y ejecutar migración para campos `resultado` y `monto_premio` en `jugadas`.
2. **Paso 2: Modelo y Servicio**: Actualizar [Jugada](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Models/Jugada.php) y codificar `App\Services\BalanceService`.
3. **Paso 3: Controlador Admin**: Añadir el endpoint `actualizarResultado` en [JugadaAdminController](file:///c:/laragon/www/germanBianchi_agenciaQ/app/Http/Controllers/Admin/JugadaAdminController.php).
4. **Paso 4: Vistas**:
   - Incorporar tarjetas de totales y tabla de categorías en `jugadas/index.blade.php`.
   - Incorporar tarjetas de agencia y selector de resultado en `admin/dashboard.blade.php` y `admin/users/show.blade.php`.
5. **Paso 5: Testing (Pest)**: Crear `tests/Feature/BalanceFinancieroTest.php` validando los cálculos, permisos de admin y actualización de resultados.
