# Arbolado API

Mapa colaborativo del arbolado en espacios públicos.

El proyecto está actualmente funcionado en [Arbolado Urbano](https://arboladourbano.com).

## Arbolado Client

Este repositorio contiene una API que se comunica con [Arbolado Client](https://github.com/Arbolado-Urbano/arbolado-client)

## Dependencias

- [PHP](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Docker](https://docs.docker.com/get-docker/) (Opcional para desarrollo)

## Instalación para desarrollo

1.  Crear un archivo `.env` y copiar el contenido de `.env.example` reemplazando los valores de las variables de ser necesario.

2.  Instalar las dependencias del proyecto `composer install`.

3.  Generar una clave de aplicación `php artisan key:generate`.

4.  Levantar una instancia de una base de datos [MySQL](https://www.mysql.com/). Para esto hay 2 opciones:
    - Hacerlo manualmente instalando MySQL y creando una base de datos.
    - Levantar un container de Docker con la base de datos `php artisan docker:up`.

5.  Inicializar la base de datos `php artisan migrate`.

6.  (Opcional) Restaurar la base de datos `docker exec -i arbolado-api-db-1 mysql -u root arbolado < [backup.sql]`.
    - El backup debe contener únicamente datos, no estructura.
    - Deshabilitar `foreign key checks` al exportar.
    - No incluir la tabla `migrations`.

7.  Obtener o compilar el binario de [tippecanoe](https://github.com/felt/tippecanoe) y copiarlo en la carpeta `/resources/bin/`.

## Ejecución para desarrollo

1. Levantar el servidor de la base de datos. Si se optó por usar Docker ejecutar `php artisan docker:up`.

2. Ejecutar el comando `php artisan serve` para levantar una instancia del serivdor de desarrollo.
    - La primera vez, y cada vez que se desee actualizar el archivo `arboles.pmtiles`: `php artisan pmtiles` (esto puede demorar algunos minutos).

## Instalación para producción

1. Clonar el proyecto en el servidor.

2. Crear un archivo `.env` y copiar el contenido de `.env.example` reemplazando los valores de las variables de ser necesario.

3. Instalar las dependencias del proyecto `composer install`.

4. Generar una clave de aplicación `php artisan key:generate`.

5. Inicializar la base de datos `php artisan migrate`.

6. Obtener o compilar el binario de [tippecanoe](https://github.com/felt/tippecanoe) y copiarlo en la carpeta `/resources/bin/`.
    - Puede ser que algunos clientes FTP corrompan este archivo al subirlo al servidor ya que lo tratan como archivo de texto en lugar de un archivo binario.

7. Si se usa Apache incluir el archivo `.htaccess` correspondiente en la carpeta `/public`.

8. Configurar trabajos de Cron para las procesar la cola de trabajos y las tareas programadas:
    - `php artisan schedule:run >> /dev/null 2>&1`
    - `php artisan queue:work --stop-when-empty >> /dev/null 2>&1`

- Nota: El servidor debe configurarse para apuntar a la carpeta `/public`.

## Comandos personalizados disponibles

| Comando                   | Descripción                               |
| ------------------------- | ----------------------------------------- |
| `php artisan docker:up`   | Levanta un container de MySQL             |
| `php artisan docker:pull` | Actualiza la imagen de MySQL              |
| `php artisan docker:down` | Detiene el container de MySQL             |
| `php artisan pmtiles`     | Genera el archivo /public/arboles.pmtiles |

## Rutas de la API

| Ruta              | Método | Descripción                                                         |
| ----------------- | ------ | ------------------------------------------------------------------- |
| `/fuentes/{slug}` | GET    | Mostrar árboles de una fuente                                       |
| `/especies`       | GET    | Listar todas las especies                                           |
| `/arboles`        | GET    | Generar el archivo `arboles.pmtiles`                                |
| `/arboles/{id}`   | GET    | Mostrar los detalles de un árbol                                    |
| `/usuarios`       | POST   | Obtener la fuente a la que pertenece un usuario                     |
| `/arboles`        | POST   | Agregar un nuevo árbol                                              |
| `/aportes`        | POST   | Agregar un nuevo aporte                                             |
| `/identificar`    | POST   | Identificar una especie a partir de fotos usando la API de PlantNet |
