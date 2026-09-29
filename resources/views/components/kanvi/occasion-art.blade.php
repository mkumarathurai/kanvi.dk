@props(['name'])
<svg class="occasion-art" viewBox="0 0 80 80" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('party')
            <path d="m13 67 13-42 27 27-40 15Z" fill="#FEF3C7" stroke="#FBBF24"/><path d="m20 44 16 16m-11-30 24 24" stroke="#FBBF24"/><path d="M37 27c-3-10 10-9 7-19m4 28c4-10 15 0 17-13m-8 20 12-2" stroke="#16A34A"/><path d="m22 15 2 5m35-6 3 4M48 60l5 4M65 52l3 3" stroke="#EF4444"/><circle cx="36" cy="14" r="2" fill="#86EFAC" stroke="none"/><circle cx="61" cy="31" r="2" fill="#FBBF24" stroke="none"/>
            @break
        @case('badminton')
            <path d="m17 53 12 12 10-9-12-13-10 10Z" fill="#DCFCE7"/><path d="m27 43 18-30c2-3 6-1 6 2l2 8c4-4 8-1 7 3l-1 7c5-2 8 2 5 6L39 56" fill="#FFFDF8"/><path d="m30 46 19-24m-15 29 23-21m-17 24 21-16m-40 12 12 12M17 53c-5 6 5 18 12 12"/>
            @break
        @case('dinner')
            <path d="M22 11v20c0 8 12 8 12 0V11m-6 0v54m-6-43h12M54 12c-8 6-11 14-11 25h12m0-25v53" stroke="#64748B" stroke-width="3"/>
            @break
        @case('cake')
            <ellipse cx="40" cy="64" rx="26" ry="6" fill="#FEF3C7" stroke="#EF4444"/><path d="M18 42v21c0 7 44 7 44 0V42" fill="#FEE2E2" stroke="#EF4444"/><ellipse cx="40" cy="42" rx="22" ry="7" fill="#FFFDF8" stroke="#EF4444"/><path d="M18 46c5 9 10-5 15 2s8 1 12-1 10 6 17-1m-35-9V24m13 12V21m13 16V24" stroke="#EF4444"/><path d="M27 18v-4m13 0v-4m13 8v-4" stroke="#FBBF24" stroke-width="3"/>
            @break
        @case('house')
            <path d="m10 37 30-26 30 26M19 31v35h42V31M34 66V46h13v20" stroke="#138448"/><path d="M55 23V12h7v17" stroke="#138448"/><path d="M25 34h9v9h-9Z" fill="#DCFCE7" stroke="#138448"/>
            @break
        @case('tree')
            <path d="M36 58h8v12h-8" fill="#FBBF24" stroke="#0B2540"/><path d="m40 15-15 21h9L19 51h10L15 63h50L51 51h10L46 36h9L40 15Z" fill="#DCFCE7" stroke="#138448"/><path d="m40 5 2 5 6 1-5 4 1 5-4-3-5 3 1-5-4-4 6-1 2-5Z" fill="#FBBF24" stroke="#FBBF24"/><circle cx="36" cy="34" r="2" fill="#EF4444" stroke="none"/><circle cx="46" cy="47" r="2" fill="#FBBF24" stroke="none"/><circle cx="32" cy="55" r="2" fill="#EF4444" stroke="none"/>
            @break
    @endswitch
</svg>
