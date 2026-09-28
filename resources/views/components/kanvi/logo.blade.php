@props(['mark' => false])
<span {{ $attributes->class(['brand-logo', 'brand-mark' => $mark]) }} aria-hidden="true">{!! file_get_contents(public_path($mark ? 'brand/kanvi-mark.svg' : 'brand/kanvi-logo.svg')) !!}</span>
