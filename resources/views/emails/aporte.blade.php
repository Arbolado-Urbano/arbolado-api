@component('mail::message')

# Nuevo aporte recibido

**Nombre**: {{ $data['name'] }}

**Correo**: <a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a>

@if (isset($data['website']) && $data['website'] !== "")
  **Sitio web**: {{ $data['website'] }}
@endif

**Datos**:

<ul>
  <li>
    <b>Ubicación:</b> <a href="https://maps.google.com/?q={{$data['coordinates']}}">{{$data['coordinates']}}</a>
  </li>
  @if (isset($data['height']) && $data['height'] !== "")
    <li>
      <b>Altura:</b> {{$data['height']}}m
    </li>
  @endif
  @if (isset($data['diameterTrunk']) && $data['diameterTrunk'] !== "")
    <li>
      <b>Diámetro tronco:</b> {{$data['diameterTrunk']}}m
    </li>
  @endif
  @if (isset($data['diameterCanopy']) && $data['diameterCanopy'] !== "")
    <li>
      <b>Diámetro copa:</b> {{$data['diameterCanopy']}}m
    </li>
  @endif
  @if (isset($data['inclination']) && $data['inclination'] !== "")
    <li>
      <b>Inclinación:</b> {{$data['inclination']}}º
    </li>
  @endif
  @if (isset($data['health']) && $data['health'] !== "")
    <li>
      <b>Estado fitosanitario:</b> {{$data['health']}}
    </li>
  @endif
  @if (isset($data['development']) && $data['development'] !== "")
    <li>
      <b>Etapa de desarrollo:</b> {{$data['development']}}
    </li>
  @endif
  @if (isset($data['species']) && $data['species'] !== "")
    <li>
      <b>Especie:</b> {{$data['species']}}
    </li>
  @endif
  @if (isset($data['speciesId']) && $data['speciesId'] !== "")
    <li>
      <b>Especie ID:</b> {{$data['speciesId']}}
    </li>
  @endif
</ul>

@endcomponent
