## **Sistema de Simulación Transaccional en línea para Agencia de Quiniela**

## 1\. Introducción y Propósito del Sistema

El presente documento define de manera exhaustiva y profesional los lineamientos arquitectónicos, las reglas de negocio y las especificaciones técnicas para el desarrollo de una plataforma web de simulación de apuestas de lotería. El sistema tiene fines estrictamente académicos y de esquematización práctica, diseñado para emular el flujo transaccional de una agencia real. La plataforma permitirá a los usuarios registrados configurar jugadas de diversas modalidades, validará matemáticamente las entradas, calculará costos dinámicos y emitirá comprobantes digitales (PDF), notificando simultáneamente al personal administrativo para su validación manual en un entorno de backoffice seguro.

## 2\. Arquitectura de Software y Stack Tecnológico

La solución adopta el patrón de arquitectura MVC (Modelo-Vista-Controlador), garantizando la separación de responsabilidades (\*Separation of Concerns\*) para facilitar el mantenimiento y la escalabilidad del código fuente.

> * **Backend Framework:** Laravel (PHP), encargado del enrutamiento, la seguridad (CSRF, Middleware), la interacción con la base de datos (Eloquent ORM) y el manejo de peticiones HTTP.  
> * **Frontend (Motor de Vistas):** Laravel Blade, complementado con HTML5 y CSS3. La interactividad del lado del cliente (DOM manipulation, cálculos de precios en tiempo real y validación preventiva) será ejecutada enteramente con *Vanilla JavaScript*.  
> * **Capa de Persistencia:** Base de datos relacional MySQL, aprovisionada localmente a través del entorno de desarrollo Laragon.  
> * **Generación de Documentos:** Integración del paquete barryvdh/laravel-dompdf para la renderización de HTML a formato PDF estático.  
> * **Capa de Seguridad (WAF/Anti-Bot):** Cloudflare Turnstile, implementado como barrera de desafío no intrusivo en el formulario de envío.  
> * **Configuración del Entorno:** Es requisito obligatorio incluir un archivo *.env.example* en la raíz del proyecto para facilitar el despliegue local.  
> * **Extensiones PHP Requeridas:** El servidor debe contar con las extensiones *gd*, *intl*, *mbstring* y *pdo\_mysql* debidamente habilitadas.

## 3\. Especificación de Actores, Roles y Casos de Uso

| Rol en Sistema | Descripción y Permisos (Alcance)   |
| :---- | :---- |
| **Cliente (Usuario)** | Usuario estándar. Requiere registro mediante Nombre completo, DNI, Correo electrónico y Contraseña. Tiene acceso a su panel privado para configurar jugadas, visualizar el costo simulado, generar el comprobante PDF y consultar el estado histórico de sus apuestas ("Pendiente" o "Procesado"). |
| **Administrador** | Personal de la agencia. Identificado a través de un atributo en la base de datos (rol \= admin). Posee acceso a un Backoffice centralizado donde visualiza una grilla con todas las jugadas entrantes. Dispondrá de herramientas de filtrado (por estado de jugada y fecha), capacidad de cambiar el estado de una jugada manualmente, herramientas de "Copia rápida" para trasladar datos al portapapeles, funciones de exportación de jugadas a PDF y un botón de "Limpiar Base de Datos" (con confirmación previa). Su función principal es transcribir los datos y gestionar el estado de la jugada. Es el único destinatario de las notificaciones SMTP. El acceso a la ruta /admin estará estrictamente protegido mediante Middleware de Laravel, verificando sesión activa y privilegios de rol. |

## 4\. Modelo de Datos y Aprovisionamiento (Seeders)

El diseño de la base de datos sigue un modelo relacional *Master-Detail* para asegurar la consistencia y escalabilidad, mitigando la redundancia de datos.

> * **Tabla users:** Extiende la migración por defecto de Laravel para incluir campos de DNI y Rol.  
> * **Tabla jugadas (Master):** Contiene la llave primaria, user\_id (Foreign Key), tipo de modalidad, monto total simulado, estado de procesamiento y *timestamps*.  
> * **Tablas de Detalles (Slaves):** Estructuras satelitales (detalles\_quiniela, detalles\_quini6, etc.) vinculadas al ID de la jugada maestra, almacenando los valores específicos según las reglas matemáticas de cada juego.  
> * **Tabla precios\_historicos:** Auditoría de costos con campos id, tipo\_juego, valor, fecha\_inicio y fecha\_fin.

### **4.1 Provision Inicial**

Para facilitar las pruebas unitarias y de integración durante el ciclo de vida del desarrollo local en Laragon, se codificarán *Database Seeders*. Al ejecutar el comando php artisan migrate \--seed, el sistema poblará automáticamente la base de datos con:

> 1. Una cuenta de **Administrador de prueba** pre-configurada (ej. admin@agencia.local).  
> 2. Varias cuentas de **Clientes simulados**.  
> 3. La tabla maestra de **Precios predefinidos** para los juegos de valor fijo.

## 5\. Lógica de Negocio: Validaciones Estrictas y Motor de Precios

Cada modalidad de juego operará bajo un estricto conjunto de restricciones matemáticas. El Frontend (mediante JavaScript) impedirá interactivamente la selección de datos inválidos y calculará en tiempo real el **Costo Total** proyectado antes del envío del formulario.

| Modalidad (Juego) | Restricciones Numéricas y de Formato | Esquema de Precios (Costo)   |
| :---- | :---- | :---- |
| **Quiniela Tradicional** | Números de 1 a 4 cifras (rango \`0000\` \- \`9999\`). Posición seleccionada en el tablero (rango \`1\` \- \`20\`). Selección de jurisdicción obligatoria. | Costo Variable: El usuario define manualmente el importe (ej. $1000). El Costo Total es directamente el importe ingresado. |
| **Quini 6** | Selección obligatoria de exactamente 6 números distintos. Rango permitido: \`00\` al \`45\`. | Valor Fijo (Tabla de Precios): Ej. $1200 por ticket. |
| **Loto Plus** | Selección de 6 números distintos (\`0\` al \`45\`) \+ validación explícita de un único "Número Plus" obligatorio (\`0\` al \`9\`). | Valor Fijo (Tabla de Precios): Ej. $1500 por ticket. |
| **Loto 5** | Selección obligatoria de exactamente 5 números distintos. Rango permitido: \`0\` al \`36\`. | Valor Fijo (Tabla de Precios): Ej. $800 por ticket. |
| **Quiniela Poceada** | Selección obligatoria de exactamente 8 números distintos de dos cifras (rango \`00\` al \`99\`). | Valor Fijo (Tabla de Precios): Ej. $1000 por ticket. **Inmutabilidad de los Datos:** Una vez que una jugada ha sido enviada al servidor, se considera inmutable para el usuario. No se permitirán ediciones ni eliminaciones por parte del cliente para preservar la integridad del registro transaccional. |

### **5.1 Validación de Datos**

Se establece como obligatoria la implementación de Laravel FormRequest para la validación exhaustiva de todas las entradas en el servidor. Mientras que la validación en el cliente (JavaScript) se orienta exclusivamente a la mejora de la experiencia de usuario (UX), la validación *backend* constituye la única fuente de verdad y el pilar fundamental de la seguridad del sistema.

### **5.2 Integridad de Precios e Inmutabilidad**

Al momento de la creación de una jugada, el sistema debe persistir el precio vigente en el registro de la transacción. El Administrador únicamente podrá modificar los precios de los juegos existentes, sin capacidad de crear nuevos tipos de juegos o modalidades. Cualquier actualización posterior en la tabla *precios\_historicos* no tendrá efecto retroactivo sobre las jugadas en estado "Pendiente" o preexistentes, garantizando así la inmutabilidad financiera y la integridad de los datos históricos.

## 6\. Emisión de Comprobantes

La plataforma generará un documento PDF transaccional para cada jugada confirmada. Este comprobante no posee validez legal, siendo su propósito proporcionar un registro visual detallado al usuario.

> * **Encabezado Institucional:** Contendrá el nombre ficticio de la agencia, el logotipo (generado o proporcionado en los *assets*) y los datos de contacto simulados.  
> * **Detalle de la Transacción:** Se listarán los números elegidos, la modalidad, la fecha y hora de la operación (Timestamp del servidor), el ID de rastreo (UUID o Ticket ID) y el Costo Total calculado.  
> * **Cláusula de Exención (Disclaimer):** El pie de página incluirá una marca de agua o texto destacado especificando que el comprobante es *"Exclusivamente una simulación con fines académicos"*.

## 7\. Flujo de Estados y Notificaciones (SMTP)

El sistema implementa un flujo unidireccional de notificaciones centrado en la eficiencia operativa del personal de la agencia:

> 1. El cliente completa el formulario. Cloudflare Turnstile verifica la integridad de la petición (mitigación DDoS y prevención de Spam).  
> 2. Laravel valida la petición (FormRequest), inserta los registros en la base de datos y define el estado inicial como **"Pendiente"**.  
> 3. El sistema encola y despacha un correo electrónico (vía SMTP de Gmail) dirigido exclusivamente a la bandeja de entrada del Administrador. Este correo contiene un resumen tabular con todos los datos de la apuesta.  
> 4. El administrador, tras procesar manualmente los datos en su sistema real, accede al Backoffice de la plataforma local y marca el ticket como **"Procesado"**.  
> 5. **Ausencia de Notificaciones Inversas:** Para minimizar la saturación del servidor SMTP y emular un comportamiento pasivo de consulta, el cliente *no* recibe correos electrónicos cuando cambia el estado. El usuario debe iniciar sesión proactivamente y visitar su "Panel de Historial" para verificar si su jugada ha sido validada.  
> 6. **Estado "Error de envío":** En caso de fallo en el servicio SMTP, la transacción se marcará con este estado. En el Backoffice del administrador, estas jugadas deberán visualizarse destacadas en color rojo para una rápida identificación, permitiendo un reenvío manual de la notificación.

### **7.1 Implementación Técnica de SMTP mediante Contraseñas de Aplicación**

\# Archivo de Configuración Local (.env) \- Entorno Laragon  
MAIL\_MAILER=smtp  
MAIL\_HOST=smtp.gmail.com  
MAIL\_PORT=465  
MAIL\_USERNAME=administracion.agencia.sim@gmail.com  
MAIL\_PASSWORD=\*\*\*\*\*\*\*\*\*\*\*\*\*\*\*\* \# Contraseña de aplicación generada en la cuenta de Google  
MAIL\_ENCRYPTION=tls  
MAIL\_FROM\_ADDRESS=sistema@agenciasimulada.local  
MAIL\_FROM\_NAME="Agencia de Quiniela (Sistema Automatizado)"

### **7.2 Seguridad Transaccional e Idempotencia**

El formulario de envío implementará un sistema de token único para asegurar la idempotencia de la operación. Este mecanismo evitará la duplicidad de jugadas ante eventuales errores de red o la ejecución de un "doble clic" accidental por parte del usuario.

### **7.3 Protección de Rutas Administrativas**

Se reafirma que todas las rutas del Backoffice están blindadas mediante el uso de Middleware especializado (ej. *auth:admin*), impidiendo cualquier intento de acceso no autorizado a través de la manipulación directa de la URL.

## 8\. Requisitos No Funcionales

> * **Gestión de Sesiones:** Se define un tiempo de expiración por inactividad de 5 minutos para garantizar la seguridad de las cuentas de usuario.  
> * **Registro de Sistema Dual (Logging):** Sistema dividido en (A) Logs críticos del sistema en *storage/logs* (estándar de Laravel) y (B) Tabla *audit\_logs* en base de datos para auditoría de acciones y errores SMTP, visible directamente desde el Backoffice del Administrador.  
> * **Recuperación ante Desastres:** Estrategia de backups manuales mediante scripts automáticos de shell/batch que ejecutan 'mysqldump' periódicamente.  
> * **Entorno y Diseño:** El sistema está restringido a un entorno 'Local-only' con un enfoque de diseño 'Desktop-first'.  
> * **Accesibilidad:** Se establece que la accesibilidad (A11y) queda fuera del alcance para la versión actual del proyecto.

## 9\. Testing y DevOps

> * **Control de Versiones:** Uso estandarizado de Git para el seguimiento de cambios y colaboración.

**Estrategia de Pruebas:** La prioridad principal es realizar 'Integration Tests' utilizando PHPUnit para validar el flujo completo: Formulario \-\> Base de Datos \-\> Envío de Correo (SMTP).