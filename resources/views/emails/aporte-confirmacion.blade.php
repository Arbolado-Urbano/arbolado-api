@component('mail::message')

# Nuevo aporte recibido

Gracias por colaborar con Arbolado Urbano.

Tu aporte fue recibido con éxito y será analizado para su integración a nuestro sistema.

Recibirás otro correo una vez realizado el análisis.

Estos son los datos que recibimos:

**Nombre**: {{ $data['name'] }}

**Correo**: <a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a>

@isset($data['website'])
  **Sitio web**: {{ $data['website'] }}
@endisset

**Datos**:

<ul>
  <li>
    <b>Ubicación:</b> <a href="https://maps.google.com/?q={{$data['coordinates']}}">{{$data['coordinates']}}</a>
  </li>
  @isset($data['height'])
    <li>
      <b>Altura:</b> {{$data['height']}}m
    </li>
  @endisset
  @isset($data['diameterTrunk'])
    <li>
      <b>Diámetro tronco:</b> {{$data['diameterTrunk']}}m
    </li>
  @endisset
  @isset($data['diameterCanopy'])
    <li>
      <b>Diámetro copa:</b> {{$data['diameterCanopy']}}m
    </li>
  @endisset
  @isset($data['inclination'])
    <li>
      <b>Inclinación:</b> {{$data['inclination']}}º
    </li>
  @endisset
  @isset($data['health'])
    <li>
      <b>Estado fitosanitario:</b> {{strtoupper($data['health'])}}
    </li>
  @endisset
  @isset($data['development'])
    <li>
      <b>Etapa de desarrollo:</b> {{strtoupper($data['development'])}}
    </li>
  @endisset
  @isset($data['species'])
    <li>
      <b>Especie:</b> {{$data['species']}}
    </li>
  @endisset
  @isset($data['notes'])
    <li>
      <b>Notas:</b><br />
      {!! nl2br(e($data['notes'])) !!}
    </li>
  @endisset
</ul>

@endcomponent
