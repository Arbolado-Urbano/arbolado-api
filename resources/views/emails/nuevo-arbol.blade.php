@component('mail::message')

# Nuevo árbol recibido

**Censista**: {{ !empty($data['censista_nombre']) ? $data['censista_nombre'] . ' (' . $data['censista_codigo'] . ')' : $data['censista_codigo'] }}

**Datos**:

<ul>
  <li>
    <b>ID:</b> {{ $data['id_censo'] }}
  </li>
  <li>
    <b>Ubicación:</b> <a href="https://maps.google.com/?q={{$data['lat']}},{{$data['lng']}}">{{$data['lat']}},{{$data['lng']}}</a>
  </li>
  <li>
    <b>Manzana:</b> {{strtoupper($data['block'])}}
  </li>
  <li>
    <b>Orientación:</b> {{strtoupper($data['orientation'])}}
  </li>
  <li>
    <b>Especie:</b> {{ !empty($data['especie_nombre_comun']) ? $data['especie_nombre_comun'] . ' (' . $data['especie_nombre_cientifico'] . ')' : $data['especie_nombre_cientifico'] }}
  </li>
  @if (!empty($data['altura']))
    <li>
      <b>Altura:</b> {{$data['altura']}}m
    </li>
  @endif
  @if (!empty($data['diametro_a_p']))
    <li>
      <b>Diámetro tronco:</b> {{$data['diametro_a_p']}}m
    </li>
  @endif
  @if (!empty($data['diametro_copa']))
    <li>
      <b>Diámetro copa:</b> {{$data['diametro_copa']}}m
    </li>
  @endif
  @if (!empty($data['inclinacion']))
    <li>
      <b>Inclinación:</b> {{$data['inclinacion']}}º
    </li>
  @endif
  @if (!empty($data['estado_fitosanitario']))
    <li>
      <b>Estado fitosanitario:</b> {{ucfirst($data['estado_fitosanitario'])}}
    </li>
  @endif
  @if (!empty($data['etapa_desarrollo']))
    <li>
      <b>Etapa de desarrollo:</b> {{ucfirst($data['etapa_desarrollo'])}}
    </li>
  @endif
  @if (!empty($data['notas']))
    <li>
      <b>Notas:</b><br />
      {!! nl2br(e($data['notas'])) !!}
    </li>
  @endif
</ul>

@endcomponent
