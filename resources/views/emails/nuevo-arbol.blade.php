@component('mail::message')

# Nuevo árbol recibido

**Censista**: {{ !empty($data['censista_nombre']) ? $data['censista_nombre'] . ' (' . $data['censista_codigo'] . ')' : $data['censista_codigo'] }}

**Datos**:

<ul>
  <li>
    <b>Enlace:</b> <a href="{{config('app.client_url')}}/arbol/{{$data['arbol_id']}}">Ver en Arbolado Urbano</a>
  </li>
  <li>
    <b>ID:</b>
    @if ($data['id_censo'] != null)
      {{ $data['id_censo'] }}
    @else
      {{ $data['arbol_id'] }}
    @endif
  </li>
  <li>
    <b>Ubicación:</b> <a href="https://maps.google.com/?q={{$data['lat']}},{{$data['lng']}}">{{$data['lat']}},{{$data['lng']}}</a>
  </li>
  @isset($data['block'])
    <li>
      <b>Manzana:</b> {{strtoupper($data['block'])}}
    </li>
  @endisset
  <li>
    <b>Calle:</b> {{$data['street']}}
  </li>
  @isset($data['streetNumber'])
    <li>
      <b>Altura:</b> {{$data['streetNumber']}}
    </li>
  @endisset
  <li>
    <b>Especie:</b> {{ !empty($data['especie_nombre_comun']) ? $data['especie_nombre_comun'] . ' (' . $data['especie_nombre_cientifico'] . ')' : $data['especie_nombre_cientifico'] }}
  </li>
  @isset($data['altura'])
    <li>
      <b>Altura:</b> {{$data['altura']}}m
    </li>
  @endisset
  @isset($data['diametro_a_p'])
    <li>
      <b>Diámetro tronco:</b> {{$data['diametro_a_p']}}m
    </li>
  @endisset
  @isset($data['diametro_copa'])
    <li>
      <b>Diámetro copa:</b> {{$data['diametro_copa']}}m
    </li>
  @endisset
  @isset($data['inclinacion'])
    <li>
      <b>Inclinación:</b> {{$data['inclinacion']}}º
    </li>
  @endisset
  @isset($data['estado_fitosanitario'])
    <li>
      <b>Estado fitosanitario:</b> {{ucfirst($data['estado_fitosanitario'])}}
    </li>
  @endisset
  @isset($data['etapa_desarrollo'])
    <li>
      <b>Etapa de desarrollo:</b> {{ucfirst($data['etapa_desarrollo'])}}
    </li>
  @endisset
  @isset($data['notas'])
    <li>
      <b>Notas:</b><br />
      {!! nl2br(e($data['notas'])) !!}
    </li>
  @endisset
</ul>

@endcomponent
