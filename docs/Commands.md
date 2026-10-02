# Comandos personalizados

| Comando                      | Descripción                    |
| ---------------------------- | ------------------------------ |
| [`docker:up`](#dockerup)     | Levanta el contenedor de MySQL |
| [`docker:pull`](#dockerpull) | Actualiza la imagen de MySQL   |
| [`docker:down`](#dockerdown) | Detiene el contenedor de MySQL |

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
