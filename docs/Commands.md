# Comandos personalizados

| Comando                                | Descripción                      |
| -------------------------------------- | -------------------------------- |
| [`docker:up`](#dockerup)               | Levanta el contenedor de MySQL   |
| [`docker:pull`](#dockerpull)           | Actualiza la imagen de MySQL     |
| [`docker:down`](#dockerdown)           | Detiene el contenedor de MySQL   |
| [`pmtiles:generate`](#pmtilesgenerate) | Genera `/public/arboles.pmtiles` |

---

## `docker:up`

Levanta el contenedor de MySQL definido en la configuración de Docker del proyecto (`docker-compose.yml`).

```bash
php artisan docker:up
```

---

## `docker:pull`

Descarga la versión más reciente de la imagen de MySQL desde el registro de Docker, actualizando la imagen local.

```bash
php artisan docker:pull
```

---

## `docker:down`

Detiene el contenedor de MySQL activo.

```bash
php artisan docker:down
```

---

## `pmtiles:generate`

Genera el archivo `/public/arboles.pmtiles` con los datos de árboles. Por defecto, actualiza el archivo existente de forma incremental. Usar `--force` para regenerarlo desde cero.

```bash
php artisan pmtiles:generate
php artisan pmtiles:generate --force
```

| Opción    | Descripción                                           |
| --------- | ----------------------------------------------------- |
| `--force` | Regenera el archivo completo en lugar de actualizarlo |
