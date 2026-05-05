@component('mail::message')

# Nuevo aporte recibido

Gracias por colaborar con Arbolado Urbano.

Tu aporte fue recibido con éxito y será analizado para su integración a nuestro sistema.

Recibirás otro correo una vez realizado el análisis.

Estos son los datos que recibimos:

**Nombre**: {{ $data['name'] }}

**Correo**: <a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a>

@if (!empty($data['website']))
  **Sitio web**: {{ $data['website'] }}
@endif

**Datos**:

<ul>
  <li>
    <b>Ubicación:</b> <a href="https://maps.google.com/?q={{$data['coordinates']}}">{{$data['coordinates']}}</a>
  </li>
  @if (!empty($data['height']))
    <li>
      <b>Altura:</b> {{$data['height']}}m
    </li>
  @endif
  @if (!empty($data['diameterTrunk']))
    <li>
      <b>Diámetro tronco:</b> {{$data['diameterTrunk']}}m
    </li>
  @endif
  @if (!empty($data['diameterCanopy']))
    <li>
      <b>Diámetro copa:</b> {{$data['diameterCanopy']}}m
    </li>
  @endif
  @if (!empty($data['inclination']))
    <li>
      <b>Inclinación:</b> {{$data['inclination']}}º
    </li>
  @endif
  @if (!empty($data['health']))
    <li>
      <b>Estado fitosanitario:</b> {{strtoupper($data['health'])}}
    </li>
  @endif
  @if (!empty($data['development']))
    <li>
      <b>Etapa de desarrollo:</b> {{strtoupper($data['development'])}}
    </li>
  @endif
  @if (!empty($data['species']))
    <li>
      <b>Especie:</b> {{$data['species']}}
    </li>
  @endif
  @if (!empty($data['notes']))
    <li>
      <b>Notas:</b><br />
      {!! nl2br(e($data['notes'])) !!}
    </li>
  @endif
</ul>

@endcomponent
