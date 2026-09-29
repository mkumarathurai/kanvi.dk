@props(['name', 'size' => 22])
<svg {{ $attributes }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('phone') <path d="m7 3 3 5-3 3c2 3 3 4 6 6l3-3 5 3c0 4-3 5-6 4C8 19 5 16 3 9 2 6 3 3 7 3Z"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 10h18M7 14h3m4 0h3m-10 3h3"/> @break
        @case('people') <circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 5v2"/> @break
        @case('check') <circle cx="12" cy="12" r="9"/><path d="m7 12 3 3 7-7"/> @break
        @case('bolt') <path d="m13 2-9 12h7l-1 8 10-13h-8z"/> @break
        @case('link') <path d="m10 13 4-4m-6 6-2 2a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m4 2 2-2a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0" transform="translate(1 0)"/> @break
        @case('mail') <rect x="2" y="4" width="20" height="16" rx="3"/><path d="m3 6 9 7 9-7"/> @break
        @case('share') <path d="M12 15V2m-4 4 4-4 4 4M7 10H4v11h16V10h-3"/> @break
        @case('chat') <path d="M21 11a9 9 0 0 1-9 9 11 11 0 0 1-4-1l-6 2 2-6a9 9 0 1 1 17-4Z"/><path d="M8 9h8m-8 4h5"/> @break
        @case('trophy') <path d="M8 3h8v5a4 4 0 0 1-8 0Zm4 9v7m-4 2h8M8 5H4v3a4 4 0 0 0 4 4m8-7h4v3a4 4 0 0 1-4 4"/> @break
        @case('lock') <rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/> @break
        @case('edit') <path d="m15 4 5 5M4 20l5-1L21 7a3.5 3.5 0 0 0-5-5L4 14z"/> @break
    @endswitch
</svg>
