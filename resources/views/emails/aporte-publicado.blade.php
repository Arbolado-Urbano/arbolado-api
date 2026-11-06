@component('mail::message')
Hola{{ !empty($datos['nombre']) ? ' ' . $datos['nombre'] : '' }}:

@if(($datos['cantidad'] ?? 0) === 0)
**Gracias por compartir tus aportes con Arbolado Urbano.**

En esta revisión no pudimos incorporar los registros al mapa.
@elseif(($datos['cantidad'] ?? 0) === 1)
**¡Tu árbol fue agregado al mapa! 🙌**
@else
**¡Tus árboles fueron agregados al mapa! 🙌**
@endif

@if(($datos['cantidad'] ?? 0) > 0)
🔍 En el siguiente enlace, podés ver si los datos que enviaste fueron cargados correctamente y comentar si hay algo que ajustar:

@component('mail::button', ['url' => $datos['url_mapa']])
Ver mis árboles en el mapa
@endcomponent
@endif

@if(($datos['rechazados'] ?? 0) > 0)
También revisamos {{ $datos['rechazados'] }} {{ $datos['rechazados'] === 1 ? 'aporte que no pudimos incorporar' : 'aportes que no pudimos incorporar' }} al mapa. Si querés consultar el motivo o enviarnos información adicional, podés responder a este correo.
@endif

🥳 ¡Muy bueno poder seguir sumando árboles al mapa!
Desde ya muchas gracias por tu tiempo y ojalá sigamos en contacto.

🤖 De paso te contamos que la revisión de los registros que nos llegan no es automática, sino que cada uno es verificado para procurar la mejor calidad de los datos.

Si te gusta el trabajo que hacemos, podés colaborar compartiendo el proyecto en las redes y/o [con un cafecito](https://cafecito.app/arboladomapa).

Saludos,

**Martín y equipo.**

[arboladourbano.com](https://arboladourbano.com)
@endcomponent
