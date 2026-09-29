{!! $heading !!}

{!! $introduction !!}
@foreach ($details as $detail)

{!! $detail !!}
@endforeach

{!! $actionLabel !!}:
{!! $actionUrl !!}
@foreach ($paragraphs as $paragraph)

{!! $paragraph !!}
@endforeach

Venlig hilsen
Kanvi

Vi gør det nemmere at finde ud af det sammen.
{!! config('app.url') !!}
@if ($reason)

{!! $reason !!}
@endif

© {{ now()->year }} Kanvi
