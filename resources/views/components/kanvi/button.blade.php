@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['button', $variant]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['button', $variant]) }}>{{ $slot }}</button>
@endif
