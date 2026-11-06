@component('mail::message')
Hola{{ !empty($datos['nombre']) ? ' ' . $datos['nombre'] : '' }}:

@if(($datos['cantidad'] ?? 1) === 1)
**¡Tu árbol fue agregado al mapa! 🙌**
@else
**¡Tus árboles fueron agregados al mapa! 🙌**
@endif

🔍 En el siguiente enlace, podés ver si los datos que enviaste fueron cargados correctamente y comentar si hay algo que ajustar:

@component('mail::button', ['url' => $datos['url_mapa']])
Ver mis árboles en el mapa
@endcomponent

🥳 ¡Muy bueno poder seguir sumando árboles al mapa!
Desde ya muchas gracias por tu tiempo y ojalá sigamos en contacto.

🤖 De paso te contamos que la revisión de los registros que nos llegan no es automática, sino que cada uno es verificado para procurar la mejor calidad de los datos.

Si te gusta el trabajo que hacemos, podés colaborar compartiendo el proyecto en las redes y/o [con un cafecito](https://cafecito.app/arboladomapa).

Saludos,

**Martín y equipo.**

[arboladourbano.com](https://arboladourbano.com)
@endcomponent
