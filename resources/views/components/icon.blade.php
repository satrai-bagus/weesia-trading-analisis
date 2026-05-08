@props(['name' => 'activity'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('activity')
            <path d="M22 12h-4l-3 8-6-16-3 8H2" />
            @break
        @case('arrow-right')
            <path d="M5 12h14" />
            <path d="m12 5 7 7-7 7" />
            @break
        @case('arrow-up-right')
            <path d="M7 17 17 7" />
            <path d="M7 7h10v10" />
            @break
        @case('bar-chart')
            <path d="M4 19V9" />
            <path d="M12 19V5" />
            <path d="M20 19v-7" />
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
            <path d="M10 21h4" />
            @break
        @case('bot')
            <path d="M12 8V4H8" />
            <rect x="4" y="8" width="16" height="12" rx="3" />
            <path d="M9 13h.01" />
            <path d="M15 13h.01" />
            <path d="M9 17h6" />
            @break
        @case('brain')
            <path d="M9 3a3 3 0 0 0-3 3v1a4 4 0 0 0-2 7 4 4 0 0 0 4 5h1V3Z" />
            <path d="M15 3a3 3 0 0 1 3 3v1a4 4 0 0 1 2 7 4 4 0 0 1-4 5h-1V3Z" />
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" />
            <path d="M3 9h18" />
            <path d="M8 3v4" />
            <path d="M16 3v4" />
            @break
        @case('check-circle')
            <path d="M9 12 11 14 15 9" />
            <circle cx="12" cy="12" r="9" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break
        @case('clipboard')
            <rect x="8" y="3" width="8" height="4" rx="1" />
            <path d="M9 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3" />
            <path d="M8 12h8" />
            <path d="M8 16h6" />
            @break
        @case('database')
            <ellipse cx="12" cy="5" rx="7" ry="3" />
            <path d="M5 5v6c0 1.7 3.1 3 7 3s7-1.3 7-3V5" />
            <path d="M5 11v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6" />
            @break
        @case('gauge')
            <path d="M20 14a8 8 0 1 0-16 0" />
            <path d="m14 10-4 4" />
            <path d="M12 14h.01" />
            @break
        @case('image-plus')
            <path d="M16 5h6" />
            <path d="M19 2v6" />
            <rect x="3" y="5" width="14" height="14" rx="2" />
            <path d="m3 15 4-4 3 3 2-2 5 5" />
            @break
        @case('layers')
            <path d="m12 2 9 5-9 5-9-5 9-5Z" />
            <path d="m3 12 9 5 9-5" />
            <path d="m3 17 9 5 9-5" />
            @break
        @case('lock')
            <rect x="5" y="11" width="14" height="10" rx="2" />
            <path d="M8 11V7a4 4 0 0 1 8 0v4" />
            @break
        @case('log-out')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="M16 17l5-5-5-5" />
            <path d="M21 12H9" />
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 7 9 6 9-6" />
            @break
        @case('menu')
            <path d="M4 6h16" />
            <path d="M4 12h16" />
            <path d="M4 18h16" />
            @break
        @case('refresh')
            <path d="M21 12a9 9 0 0 1-15.5 6.3L3 16" />
            <path d="M3 16h6v6" />
            <path d="M3 12A9 9 0 0 1 18.5 5.7L21 8" />
            <path d="M21 8h-6V2" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
            @break
        @case('send')
            <path d="m22 2-7 20-4-9-9-4 20-7Z" />
            <path d="M22 2 11 13" />
            @break
        @case('settings')
            <path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z" />
            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1A2 2 0 1 1 7.1 4.2l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1A2 2 0 1 1 19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.1a2 2 0 1 1 0 4H21a1.7 1.7 0 0 0-1.6 1Z" />
            @break
        @case('shield-check')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            <path d="m9 12 2 2 4-5" />
            @break
        @case('sliders')
            <path d="M4 21v-7" />
            <path d="M4 10V3" />
            <path d="M12 21v-9" />
            <path d="M12 8V3" />
            <path d="M20 21v-5" />
            <path d="M20 12V3" />
            <path d="M2 14h4" />
            <path d="M10 8h4" />
            <path d="M18 16h4" />
            @break
        @case('sparkles')
            <path d="M12 3l1.4 4.3L18 9l-4.6 1.7L12 15l-1.4-4.3L6 9l4.6-1.7L12 3Z" />
            <path d="M5 16l.7 2.1L8 19l-2.3.9L5 22l-.7-2.1L2 19l2.3-.9L5 16Z" />
            <path d="M19 14l.6 1.8 1.9.7-1.9.7L19 19l-.6-1.8-1.9-.7 1.9-.7L19 14Z" />
            @break
        @case('target')
            <circle cx="12" cy="12" r="9" />
            <circle cx="12" cy="12" r="5" />
            <circle cx="12" cy="12" r="1" />
            @break
        @case('trending-up')
            <path d="m3 17 6-6 4 4 8-8" />
            <path d="M14 7h7v7" />
            @break
        @case('trash')
            <path d="M3 6h18" />
            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
            <path d="m6 6 1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14" />
            @break
        @case('user')
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21a8 8 0 0 1 16 0" />
            @break
        @case('video')
            <rect x="3" y="6" width="13" height="12" rx="2" />
            <path d="m22 8-6 4 6 4V8Z" />
            @break
        @case('plus')
            <path d="M12 5v14" />
            <path d="M5 12h14" />
            @break
        @case('edit')
            <path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5" />
            <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5Z" />
            @break
        @case('wallet')
            <path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5" />
            <path d="M16 13h.01" />
            @break
        @case('x')
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
            @break
        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
