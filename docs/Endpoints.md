# Rutas de la API

| Ruta            | Método | Descripción                                                         |
| --------------- | ------ | ------------------------------------------------------------------- |
| /fuentes/{slug} | GET    | Mostrar árboles de una fuente                                       |
| /especies       | GET    | Listar todas las especies                                           |
| /arboles (GET)  | GET    | Generar el archivo `/public/arboles.pmtiles`                        |
| /arboles/{id}   | GET    | Mostrar los detalles de un árbol                                    |
| /usuarios       | POST   | Obtener la fuente a la que pertenece un usuario                     |
| /arboles (POST) | POST   | Agregar un nuevo árbol                                              |
| /aportes        | POST   | Agregar un nuevo aporte                                             |
| /identificar    | POST   | Identificar una especie a partir de fotos usando la API de PlantNet |

- Las rutas que devuelven datos responden en formato `application/json`.
- Las rutas `POST` esperan el cuerpo de la petición en formato `multipart/form-data`.

---

## `GET /fuentes/{slug}`

Devuelve los árboles pertenecientes a una fuente específica.

### Parámetros de ruta

| Parámetro | Tipo   | Requerido | Descripción                             |
| --------- | ------ | --------- | --------------------------------------- |
| `slug`    | string | Sí        | Identificador único (slug) de la fuente |

### Respuesta exitosa — `200 OK`

```ts
type Source = {
    id: number;
    nombre: string;
    descripcion: string;
    facebook?: string;
    instagram?: string;
    twitter?: string;
    url?: string;
};

type Record = {
    id: number;
    altura?: string;
    diametro_a_p?: string;
    inclinacion?: string;
    fecha_creacion: string;
    estado_fitosanitario?: string;
    etapa_desarrollo?: string;
    source: Source;
};

type Species = {
    id: number;
    url?: string;
    icono?: string;
    color?: string;
    nombre_cientifico: string;
    nombre_comun?: string;
    comestible?: string;
    medicinal?: string;
};

type Tree = {
    id: number;
    lat: number;
    lng: number;
    especie_id: number;
    species: Species;
    records: Record[];
};

type Response = Tree[];
```

### Códigos de estado

| Código | Descripción                         |
| ------ | ----------------------------------- |
| `200`  | Éxito. Devuelve el array de árboles |

---

## `GET /especies`

Devuelve el listado completo de especies disponibles en el sistema.
Los campos nulos, vacíos o con valor `0` son omitidos de cada objeto.

No recibe parámetros.

### Respuesta exitosa — `200 OK`

```ts
type Species = {
    id: number;
    nombre_cientifico: string;
    nombre_comun?: string;
    url?: string;
    icono?: string;
    color?: string;
    comestible?: string;
    medicinal?: string;
};

type Response = Species[];
```

### Códigos de estado

| Código | Descripción                          |
| ------ | ------------------------------------ |
| `200`  | Éxito. Devuelve el array de especies |

---

## `GET /arboles`

Genera el archivo `/public/arboles.pmtiles` con los datos de todos los árboles no removidos.

### Parámetros de consulta

| Parámetro | Requerido | Descripción                                                          |
| --------- | --------- | -------------------------------------------------------------------- |
| `forzar`  | No        | Si se incluye, regenera el archivo completo en lugar de actualizarlo |

### Respuesta exitosa — `200 OK`

- No retorna datos

### Códigos de estado

| Código | Descripción                                  |
| ------ | -------------------------------------------- |
| `200`  | Éxito. El trabajo de generación fue iniciado |

---

## `GET /arboles/{id}`

Devuelve los detalles completos de un árbol específico, incluyendo su especie, familia, tipo y registros asociados.

### Parámetros de ruta

| Parámetro | Tipo    | Requerido | Descripción                   |
| --------- | ------- | --------- | ----------------------------- |
| `id`      | integer | Sí        | Identificador único del árbol |

### Respuesta exitosa — `200 OK`

```ts
type Source = {
    id: number;
    nombre: string;
    descripcion: string;
    facebook?: string;
    instagram?: string;
    twitter?: string;
    url?: string;
};

type Record = {
    id: number;
    altura?: string;
    diametro_a_p?: string;
    inclinacion?: string;
    fecha_creacion: string;
    estado_fitosanitario?: string;
    etapa_desarrollo?: string;
    source: Source;
};

type Response = {
    id: number;
    calle?: string;
    calle_altura?: string;
    espacio_verde?: string;
    streetview?: string;
    lat: number;
    lng: number;
    species: {
        id: number;
        nombre_cientifico: string;
        nombre_comun: string;
        origen: string;
        procedencia_exotica?: string;
        icono: string;
        url?: string;
        family: { id: number; familia: string };
        type: { id: number; tipo: string };
    };
    records: Record[];
};
```

### Códigos de estado

| Código | Descripción                               |
| ------ | ----------------------------------------- |
| `200`  | Éxito. Devuelve el objeto árbol           |
| `404`  | No existe un árbol con el `id` indicado   |
| `422`  | El parámetro `id` no superó la validación |

---

## `POST /usuarios`

Determina a qué fuente pertenece un usuario a partir de su código.

### Cuerpo de la petición

| Campo     | Tipo   | Requerido | Descripción                          |
| --------- | ------ | --------- | ------------------------------------ |
| `code`    | string | Sí        | Código de identificación del usuario |
| `captcha` | string | Sí        | Token de verificación captcha        |

### Respuesta exitosa — `200 OK`

```ts
type Response = {
    slug: string;
};
```

### Códigos de estado

| Código | Descripción                                      |
| ------ | ------------------------------------------------ |
| `200`  | Éxito. Devuelve el slug de la fuente del usuario |
| `404`  | No existe un usuario con el `code` indicado      |
| `422`  | Algún campo del cuerpo no superó la validación   |

---

## `POST /arboles`

Agrega un nuevo árbol al sistema.

### Cuerpo de la petición

| Campo            | Tipo   | Requerido | Descripción                                                               |
| ---------------- | ------ | --------- | ------------------------------------------------------------------------- |
| `code`           | string | Sí        | Código de identificación del usuario que registra el árbol                |
| `coordinates`    | string | Sí        | Coordenadas geográficas en formato `latitud,longitud` (ej. `-34.6,-58.4`) |
| `species`        | string | No\*      | Nombre de la especie del árbol. Requerido si no se provee `speciesUrl`    |
| `speciesUrl`     | string | No\*      | Slug identificador de la especie. Requerido si no se provee `species`     |
| `captcha`        | string | Sí        | Token de verificación captcha                                             |
| `block`          | string | Sí        | Manzana o bloque donde se encuentra el árbol                              |
| `orientation`    | string | Sí        | Orientación o frente del árbol respecto a la vía pública                  |
| `height`         | string | No        | Altura del árbol                                                          |
| `diameterTrunk`  | string | No        | Diámetro del tronco del árbol                                             |
| `diameterCanopy` | string | No        | Diámetro de la copa del árbol                                             |
| `inclination`    | string | No        | Inclinación del árbol                                                     |
| `health`         | string | No        | Estado fitosanitario del árbol                                            |
| `development`    | string | No        | Estado de desarrollo del árbol                                            |
| `notes`          | string | No        | Observaciones o notas adicionales sobre el árbol                          |

_\* Al menos uno de los dos es requerido._

### Respuesta exitosa — `200 OK`

```ts
type Response = {}; // Objeto JSON vacío
```

### Códigos de estado

| Código | Descripción                                                                   |
| ------ | ----------------------------------------------------------------------------- |
| `200`  | Éxito. El árbol fue creado correctamente                                      |
| `401`  | El `code` de usuario no corresponde a ningún usuario registrado               |
| `404`  | El `speciesUrl` fue provisto pero no se encontró ninguna especie con ese slug |
| `422`  | Algún campo del cuerpo no superó la validación                                |

---

## `POST /aportes`

Agrega un nuevo aporte (árbol pendiente de aprobación por un administrador).

### Cuerpo de la petición

| Campo            | Tipo   | Requerido | Descripción                                                               |
| ---------------- | ------ | --------- | ------------------------------------------------------------------------- |
| `email`          | string | Sí        | Correo electrónico del contribuyente                                      |
| `name`           | string | Sí        | Nombre del contribuyente                                                  |
| `coordinates`    | string | Sí        | Coordenadas geográficas en formato `latitud,longitud` (ej. `-34.6,-58.4`) |
| `species`        | string | No\*      | Nombre de la especie del árbol. Requerido si no se provee `speciesUrl`    |
| `speciesUrl`     | string | No\*      | Slug identificador de la especie. Requerido si no se provee `species`     |
| `captcha`        | string | Sí        | Token de verificación captcha                                             |
| `website`        | string | No        | Sitio web del contribuyente                                               |
| `height`         | string | No        | Altura del árbol                                                          |
| `diameterTrunk`  | string | No        | Diámetro del tronco del árbol                                             |
| `diameterCanopy` | string | No        | Diámetro de la copa del árbol                                             |
| `inclination`    | string | No        | Inclinación del árbol                                                     |
| `health`         | string | No        | Estado fitosanitario del árbol                                            |
| `development`    | string | No        | Estado de desarrollo del árbol                                            |
| `notes`          | string | No        | Observaciones o notas adicionales sobre el árbol                          |

_\* Al menos uno de los dos es requerido._

### Respuesta exitosa — `200 OK`

```ts
type Response = {}; // Objeto JSON vacío
```

### Códigos de estado

| Código | Descripción                                                                   |
| ------ | ----------------------------------------------------------------------------- |
| `200`  | Éxito. El aporte fue registrado y queda pendiente de aprobación               |
| `404`  | El `speciesUrl` fue provisto pero no se encontró ninguna especie con ese slug |
| `422`  | Algún campo del cuerpo no superó la validación                                |

---

## `POST /identificar`

Envía fotos de un árbol a la API de PlantNet para identificar su especie.

### Cuerpo de la petición

| Campo     | Tipo   | Requerido | Descripción                               |
| --------- | ------ | --------- | ----------------------------------------- |
| `captcha` | string | Sí        | Token de verificación captcha             |
| `images`  | File[] | Sí        | Lista de imágenes del árbol a identificar |

### Respuesta exitosa — `200 OK`

```ts
type Response = PlantNetResponse; // https://my.plantnet.org/doc/api/identify
```

### Códigos de estado

| Código | Descripción                                    |
| ------ | ---------------------------------------------- |
| `200`  | Éxito. Devuelve la respuesta de PlantNet       |
| `422`  | Algún campo del cuerpo no superó la validación |
