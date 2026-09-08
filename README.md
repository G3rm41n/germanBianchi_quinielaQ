# Agencia N°5801

Este repositorio contiene el código fuente de "Agencia N°5801", una plataforma web de simulación académica de agencia de quiniela.

## Requisitos Previos

Asegúrate de tener instalados los siguientes programas en tu entorno de desarrollo:

- PHP >= 8.2 (recomendado 8.2 o superior)
- Composer
- Node.js y npm
- MySQL o MariaDB
- Un servidor web local como Laragon, XAMPP, o Laravel Herd.

## Instalación

Sigue estos pasos para clonar el repositorio y configurar el proyecto en tu entorno local para que la página pueda ser recreada correctamente:

1. **Clonar el repositorio**
   ```bash
   git clone <URL_DEL_REPOSITORIO>
   cd germanBianchi_agenciaQ
   ```

2. **Instalar dependencias de PHP (Backend)**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Node (Frontend)**
   ```bash
   npm install
   npm run build
   ```
   *(Si estás en desarrollo, puedes usar `npm run dev` para compilar los assets en tiempo real)*

4. **Configurar las variables de entorno**
   Copia el archivo de configuración de ejemplo para crear tu propio entorno:
   ```bash
   cp .env.example .env
   ```
   *(En Windows puedes usar `copy .env.example .env`)*

5. **Generar la clave de la aplicación**
   ```bash
   php artisan key:generate
   ```

6. **Configurar la base de datos**
   Abre el archivo `.env` recién creado y actualiza las credenciales para que coincidan con tu base de datos local:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=agencia_quiniela # o el nombre que prefieras
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *Asegúrate de crear una base de datos vacía en tu gestor (por ejemplo, phpMyAdmin o HeidiSQL) antes de continuar.*

7. **Ejecutar las migraciones**
   Para crear las tablas necesarias en la base de datos, ejecuta:
   ```bash
   php artisan migrate
   ```
   *(Si el proyecto cuenta con datos iniciales, puedes usar `php artisan migrate --seed`)*

8. **Levantar el servidor local**
   Si utilizas Laragon, solo debes iniciar los servicios y acceder a la URL local (por ejemplo `http://germanBianchi_agenciaQ.test` o `http://localhost/germanBianchi_agenciaQ/public/`).
   
   Alternativamente, puedes usar el servidor de desarrollo integrado de Laravel:
   ```bash
   php artisan serve
   ```
   Y acceder mediante tu navegador a `http://localhost:8000`.

## Estructura del Repositorio
Este proyecto fue desarrollado utilizando el framework Laravel. Los archivos que son excluidos por defecto (como la carpeta `vendor`, `node_modules` y el archivo `.env`) no se suben al repositorio por razones de seguridad y peso. Los pasos de instalación mencionados arriba reconstruyen esas carpetas automáticamente al ejecutar `composer install` y `npm install`.

---
*Simulación Académica*
