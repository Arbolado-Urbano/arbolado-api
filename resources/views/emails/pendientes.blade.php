@component('mail::message')

# Actividades pendientes de revisión

@if ($especiesCount > 0)
  **Especies**: {{ $especiesCount }}
@endif

@if ($aportesCount > 0)
  **Aportes**: {{ $aportesCount }}
@endif

@endcomponent
