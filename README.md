# Proyecto Centro Comercial

Aplicación Laravel para administrar infraestructuras comerciales, tiendas, clientes, marcas, productos, suscripciones, cobros, pagos y reportes. Usa Laravel 12, Filament 4, Vite, SQLite por defecto y colas en base de datos.

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js y npm
- SQLite habilitado en PHP

## Instalación después de clonar

1. Instalar dependencias PHP:

```bash
composer install
```

2. Instalar dependencias frontend:

```bash
npm install
```

3. Crear el archivo de entorno:

```bash
cp .env.example .env
```

4. Generar la llave de la aplicación:

```bash
php artisan key:generate
```

5. Crear la base SQLite si no existe:

```bash
touch database/database.sqlite
```

6. Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

7. Crear el enlace público de storage:

```bash
php artisan storage:link
```

8. Iniciar el entorno de desarrollo:

```bash
composer run dev
```

Ese comando levanta el servidor Laravel, Vite, logs con Pail y el listener de colas. Por defecto la aplicación queda disponible en `http://127.0.0.1:8000`.

## Accesos iniciales

Los seeders crean estos usuarios administrativos:

| Rol | Email | Password |
| --- | --- | --- |
| Super admin | `superadmin@admin.com` | `password` |
| Admin | `admin@admin.com` | `password` |

Rutas útiles:

- Sitio público: `/`
- Login: `/login`
- Panel administrativo Filament: `/admin`
- Panel cliente: `/cliente/dashboard`
- Directorio público: `/directorio`
- Simulador de suscripciones: `/suscripciones`

## Variable de fecha para debug

La aplicación permite fijar una fecha de sistema desde `.env` para probar flujos dependientes del tiempo, como vencimientos, cobros, notificaciones o liberación de tiendas.

```env
FECHA_SISTEMA=
```

Valores soportados:

- `FECHA_SISTEMA=`: usa la fecha y hora real del sistema.
- `FECHA_SISTEMA=NULL`: usa la fecha y hora real del sistema.
- `FECHA_SISTEMA=15/07/2027`: fuerza la fecha del sistema al `15/07/2027`.

El formato obligatorio es `DD/MM/YYYY`. Cuando se fija una fecha, la aplicación conserva la hora real actual, pero reemplaza el día, mes y año para llamadas como `now()`, `today()` y `Carbon::now()`.

Después de cambiar `FECHA_SISTEMA`, limpiar la configuración si está cacheada:

```bash
php artisan optimize:clear
```

Si hay procesos largos activos, reiniciar el servidor, workers de colas y scheduler para que tomen el nuevo valor.

## Comandos útiles

Levantar todo el entorno de desarrollo:

```bash
composer run dev
```

Levantar solo Laravel:

```bash
php artisan serve
```

Levantar solo Vite:

```bash
npm run dev
```

Compilar assets para producción:

```bash
npm run build
```

Ejecutar colas:

```bash
php artisan queue:listen --tries=1
```

Ejecutar el scheduler manualmente:

```bash
php artisan schedule:run
```

Ver tareas programadas:

```bash
php artisan schedule:list
```

Marcar cobros vencidos según la fecha actual o `FECHA_SISTEMA`:

```bash
php artisan cobros:marcar-vencidos
```

Liberar tiendas con suscripciones expiradas según la fecha actual o `FECHA_SISTEMA`:

```bash
php artisan suscripciones:liberar-expiradas
```

Ver logs en vivo:

```bash
php artisan pail
```

Abrir Tinker:

```bash
php artisan tinker
```

Limpiar caches:

```bash
php artisan optimize:clear
```

Regenerar caches para producción:

```bash
php artisan optimize
```

## Base de datos

El proyecto usa SQLite por defecto:

```env
DB_CONNECTION=sqlite
```

Para reconstruir la base local desde cero:

```bash
php artisan migrate:fresh --seed
```

Este comando elimina los datos existentes. Usarlo solo en entornos locales o de prueba.

## Pruebas y formato

Ejecutar toda la suite:

```bash
php artisan test
```

Ejecutar una prueba específica:

```bash
php artisan test tests/Feature/SystemDateOverrideTest.php
```

Formatear código con Pint:

```bash
php vendor/bin/pint
```

Revisar sintaxis PHP de un archivo:

```bash
php -l app/Providers/AppServiceProvider.php
```

## Archivos públicos y uploads

Los uploads se guardan en `storage/app/public` y se sirven desde `public/storage`. Si las imágenes no cargan, ejecutar:

```bash
php artisan storage:link
```

Las imágenes de fondo de pisos se guardan en:

```text
storage/app/public/infraestructuras/fondos
```

## Producción

Checklist básico para despliegue:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

En producción, dejar `APP_DEBUG=false` y `FECHA_SISTEMA=` salvo que se esté haciendo una prueba controlada.
