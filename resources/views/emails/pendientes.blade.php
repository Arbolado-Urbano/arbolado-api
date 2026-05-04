@component('mail::message')

# Actividades pendientes de revisión

@if (count($especies) > 0)
  <h2>
    Especies
  </h2>
  <div style="margin-left:16px">
    <b>IDs:</b>
    <ul>
      @foreach ($especies as $especie)
      <li>
        {{ $especie->id }}
      </li>
      @endforeach
    </ul>
    <b>Total:</b> {{ count($especies) }}
  </div>
  <hr style="margin:16px 0" />
@endif

@if (count($aportes) > 0)
  <h2>
    Aportes
  </h2>
  <div style="margin-left:16px">
    <b>IDs:</b>
    <ul>
      @foreach ($aportes as $aporte)
        <li>
        {{ $aporte->id }}
        </li>
      @endforeach
    </ul>
    <b>Total:</b> {{ count($aportes) }}
  </div>
  <hr style="margin:16px 0" />
@endif

@endcomponent
