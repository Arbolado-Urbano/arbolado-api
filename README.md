# Arbolado API

 Mapa colaborativo del arbolado en espacios públicos.

 El proyecto está actualmente funcionado en [Arbolado Urbano](https://arboladourbano.com).

 ## Arbolado Client

 Este repositorio contiene una API, el cual se comunica con un cliente que se encuentra en este otro repositorio: [Arbolado Client](https://github.com/Arbolado-Urbano/arbolado-client)

 ## Dependencias

- [PHP](https://www.php.net/) y [Composer](https://getcomposer.org/)

## Dependencias para desarrollo

- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/install/)

## Instalación para desarrollo

1. Crear un archivo .env y copiar el contenido de .env.example reemplazando los valores de las variables de ser necesario.
2. Ejecutar el comando `composer install` para instalar las dependencias del proyecto.
3. Levantar una instancia de una base de datos [MySQL](https://www.mysql.com/). Para esto hay 2 opciones:
    - Hacerlo manualmente instalando MySQL y creando una base de datos.
    - Levantar un container de Docker con la base de datos:
      1. Ejecutar el comando `php artisan docker:pull` para descargar las últimas versiones de las imágenes de Docker necesarias.
      2. Ejecutar el comando `php artisan docker:up` para levantar una instancia de la base de datos con Docker.
4. Ejecutar el comando `php artisan migrate` para inicializar la base de datos.
5. Opcional: Si se desea cargar la base de datos, obtener una copia de la base de datos en formato SQL y ejectuar el comando `docker exec -i arbolado-api-db-1 mysql -u root arbolado < [backup.sql]` donde `[backup.sql]` es la ruta al archivo SQL.
- Notas:
        - El archivo SQL debe contener únicamente los datos de la base y no la estructura.
        - Al exportar los datos de la base asegurarse de que los chequeos de claves foráneas están deshabilitados (`Disable foreign key checks`).
        - No exportar la tabla `migrations` si existe.

## Ejecución para desarrollo

1. Ejecutar el comando `php artisan docker:up` para levantar una instancia de la base de datos con Docker o levantar el servidor MySQL local si se optó por esta opción en la instalación del proyecto.
2. Ejecutar el comando `php artisan serve` para levantar una instancia del serivdor de desarrollo.
2. Acceder a [http://localhost:8080](http://localhost:8080).

## Instalación para producción

1. En caso de no contar con uno, crear un [token classic en Github](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens#creating-a-personal-access-token-classic) con los permisos de read/writer packages, y ejecutar el siguiente comando: `"[token]" | docker login ghcr.io -u [user] --password-stdin`
2. Ejecutar el comando `php artisan docker:push` para construir y publicar la imagen de Docker para producción.
3. Ejecutar el comando `docker compose pull` desde el servidor correspondiente para actualizar la imagen de Docker.
4. Reiniciar el container de Docker para actualizarlo.
