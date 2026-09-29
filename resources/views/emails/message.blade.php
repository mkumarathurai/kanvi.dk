@extends('emails.layouts.base')

@section('content')
    <x-email.heading>{{ $heading }}</x-email.heading>
    <p style="margin:0 0 24px; font-size:16px; line-height:1.6; color:#555550;">{{ $introduction }}</p>
    @if ($details)
        <x-email.info-box>
            @foreach ($details as $detail)
                <p style="margin:{{ $loop->first ? '0' : '8px 0 0' }}; {{ $loop->first ? 'color:#20201E; font-weight:600;' : '' }}">{{ $detail }}</p>
            @endforeach
        </x-email.info-box>
    @endif
    <x-email.button :href="$actionUrl">{{ $actionLabel }}</x-email.button>
    @foreach ($paragraphs as $paragraph)
        <p style="margin:24px 0 0; font-size:16px; line-height:1.6; color:#555550;">{{ $paragraph }}</p>
    @endforeach
@endsection
