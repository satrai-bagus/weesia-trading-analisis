@props(['session'])

@php
    $status = $session->status();
    $isLive = $status === \App\Models\LiveSession::STATUS_LIVE;
    $isJoinable = $session->isJoinable();
    $providerLabel = $session->providerLabel();
    $scheduledLocal = $session->scheduledAtLocal();
    $hasCover = (bool) $session->cover_image_path;
    $coverUrl = $hasCover ? asset('storage/'.$session->cover_image_path) : null;
    $isToday = $scheduledLocal->isToday();
    $isTodayUpcoming = $isToday && ! $isLive;

    $providerAccent = match ($session->provider) {
        \App\Models\LiveSession::PROVIDER_ZOOM => 'text-sky-300',
        \App\Models\LiveSession::PROVIDER_GMEET => 'text-emerald-300',
        default => 'text-gold-300',
    };

    $rootBorder = $isLive
        ? 'border-rose-500/50'
        : ($isTodayUpcoming ? 'border-gold-400/70 animate-[live-today-pulse_2.4s_ease-in-out_infinite]' : 'border-gold-500/40');
@endphp

<div id="live-session"
     class="reveal relative overflow-hidden rounded-3xl border scroll-mt-24 sm:scroll-mt-28 {{ $rootBorder }} bg-ink-900/85 backdrop-blur-xl"
     data-live-session
     data-starts-at="{{ $session->scheduled_at->toIso8601String() }}"
     data-ends-at="{{ $session->endsAt()->toIso8601String() }}">

    @if ($isTodayUpcoming)
        <style>
            @keyframes live-today-pulse {
                0%, 100% { box-shadow: 0 0 0 0 rgba(23,209,131,0.0), 0 18px 50px -28px rgba(23,209,131,0.55); border-color: rgba(23,209,131,0.55); }
                50% { box-shadow: 0 0 0 6px rgba(23,209,131,0.10), 0 24px 70px -28px rgba(23,209,131,0.85); border-color: rgba(78,230,161,0.95); }
            }
            @keyframes live-today-sweep {
                0% { transform: translateX(-100%); }
                60%, 100% { transform: translateX(100%); }
            }
        </style>
        <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-px overflow-hidden">
            <span class="absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-transparent via-gold-300/90 to-transparent" style="animation: live-today-sweep 2.6s ease-in-out infinite;"></span>
        </div>
        <div class="absolute right-4 top-4 z-10 inline-flex items-center gap-2 rounded-full border border-gold-400/60 bg-gold-500/15 px-3 py-1.5 font-mono text-[10px] font-semibold uppercase tracking-[0.25em] text-gold-100 shadow-[0_10px_30px_-8px_rgba(23,209,131,0.7)]">
            <span class="relative inline-flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-gold-300 opacity-80"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-gold-300"></span>
            </span>
            Hari Ini
        </div>
    @endif

    <div class="pointer-events-none absolute inset-0 {{ $isLive ? 'bg-gradient-to-br from-rose-950/60 via-ink-900 to-ink-900' : 'bg-gradient-to-br from-gold-900/30 via-ink-900 to-ink-900' }}"></div>
    <div class="pointer-events-none absolute -top-32 -right-32 h-80 w-80 rounded-full {{ $isLive ? 'bg-rose-500/25' : 'bg-gold-500/20' }} blur-[100px]"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent {{ $isLive ? 'via-rose-400/70' : 'via-gold-400/70' }} to-transparent"></div>

    <div class="relative grid grid-cols-1 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">

        <div class="relative overflow-hidden lg:rounded-l-3xl">
            @if ($hasCover)
                <img src="{{ $coverUrl }}" alt="{{ $session->title }}" class="h-full w-full object-cover lg:absolute lg:inset-0 aspect-video lg:aspect-auto">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink-900/85 via-ink-900/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-ink-900/85"></div>
            @else
                <div class="relative flex aspect-video w-full items-center justify-center overflow-hidden border-b border-ink-700/40 lg:absolute lg:inset-0 lg:aspect-auto lg:border-0">
                    <svg class="absolute inset-0 h-full w-full opacity-20" preserveAspectRatio="none" viewBox="0 0 400 240">
                        <defs>
                            <pattern id="lsb-grid-{{ $session->id }}" width="28" height="28" patternUnits="userSpaceOnUse">
                                <path d="M 28 0 L 0 0 0 28" fill="none" stroke="{{ $isLive ? '#f43f5e' : '#17d183' }}" stroke-width="0.6" />
                            </pattern>
                            <linearGradient id="lsb-fill-{{ $session->id }}" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="{{ $isLive ? '#f43f5e' : '#17d183' }}" stop-opacity="0.4" />
                                <stop offset="100%" stop-color="{{ $isLive ? '#f43f5e' : '#17d183' }}" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#lsb-grid-{{ $session->id }})" />
                        <path d="M0,180 L50,160 L90,168 L130,135 L170,148 L210,118 L250,128 L290,95 L330,108 L370,72 L400,82 L400,240 L0,240 Z" fill="url(#lsb-fill-{{ $session->id }})" />
                        <path d="M0,180 L50,160 L90,168 L130,135 L170,148 L210,118 L250,128 L290,95 L330,108 L370,72 L400,82" fill="none" stroke="{{ $isLive ? '#fb7185' : '#c9f7e0' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="relative inline-flex h-20 w-20 items-center justify-center rounded-full border {{ $isLive ? 'border-rose-500/40 bg-rose-500/15 text-rose-200' : 'border-gold-500/30 bg-gold-500/10 text-gold-200' }}">
                        @if ($isLive)
                            <span class="absolute inset-0 animate-ping rounded-full border-2 border-rose-400/50"></span>
                        @endif
                        <x-icon name="{{ $isLive ? 'activity' : 'video' }}" class="relative h-9 w-9" />
                    </div>
                </div>
            @endif

            @if ($isLive)
                <div class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-full bg-rose-500/95 px-3 py-1.5 font-mono text-[10px] font-semibold uppercase tracking-[0.25em] text-ink-50 shadow-[0_10px_30px_-8px_rgba(244,63,94,0.95)]">
                    <span class="relative inline-flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-80"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                    </span>
                    Live
                </div>
            @endif
        </div>

        <div class="relative space-y-4 p-6 sm:p-7 lg:p-8">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-[10px] uppercase tracking-[0.32em] {{ $isLive ? 'text-rose-200' : 'text-gold-300' }}">
                    {{ $isLive ? 'On Air Sekarang' : 'Live Trading Session' }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-ink-700/70 bg-ink-900/70 px-2.5 py-0.5 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200">
                    <x-icon name="video" class="h-3 w-3 {{ $providerAccent }}" />
                    {{ $providerLabel }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-ink-700/70 bg-ink-900/70 px-2.5 py-0.5 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200">
                    <x-icon name="clock" class="h-3 w-3 text-ink-300" />
                    {{ $session->duration_minutes }}m
                </span>
            </div>

            <h2 class="font-display text-2xl leading-[1.1] text-ink-50 sm:text-3xl lg:text-4xl">{{ $session->title }}</h2>

            @if ($session->description)
                <p class="text-sm leading-relaxed text-ink-200 line-clamp-3">{{ $session->description }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 font-mono text-[11px] uppercase tracking-[0.22em] text-ink-300">
                <span class="inline-flex items-center gap-2">
                    <x-icon name="calendar" class="h-3.5 w-3.5 text-gold-300" />
                    {{ $scheduledLocal->translatedFormat('l, d M Y') }}
                </span>
                <span class="inline-flex items-center gap-2">
                    <x-icon name="clock" class="h-3.5 w-3.5 text-gold-300" />
                    {{ $scheduledLocal->format('H:i') }} WIB
                </span>
            </div>

            @if ($isLive)
                <div class="flex items-center gap-3 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-3">
                    <span class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-500/20 text-rose-200">
                        <span class="absolute inset-0 animate-ping rounded-full border-2 border-rose-400/60"></span>
                        <x-icon name="activity" class="relative h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <div class="font-display text-lg leading-tight text-rose-100">Sedang Berlangsung</div>
                        <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300/80">Klik tombol di bawah untuk masuk room</div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-4 gap-1.5 sm:gap-2" data-live-countdown-grid>
                    @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                        <div class="relative overflow-hidden rounded-xl border border-gold-500/25 bg-ink-900/60 px-1.5 py-2 text-center backdrop-blur-md sm:px-2 sm:py-3">
                            <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-400/50 to-transparent"></div>
                            <div class="font-display text-2xl leading-none text-gold-100 tabular-nums sm:text-3xl" data-countdown-{{ $unit }}>00</div>
                            <div class="mt-1 font-mono text-[8px] uppercase tracking-[0.2em] text-ink-300 sm:text-[9px]">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-2 pt-1">
                @if ($isJoinable)
                    <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener"
                       class="group relative inline-flex flex-1 items-center justify-center gap-2 overflow-hidden rounded-full {{ $isLive ? 'bg-gradient-to-b from-rose-400 to-rose-600 text-ink-50 shadow-[0_18px_50px_-18px_rgba(244,63,94,0.95)] hover:shadow-[0_24px_70px_-18px_rgba(244,63,94,1)]' : 'bg-gradient-to-b from-gold-300 to-gold-500 text-ink-900 shadow-[0_18px_50px_-18px_rgba(23,209,131,0.95)] hover:shadow-[0_24px_70px_-18px_rgba(23,209,131,1)]' }} px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] transition-all">
                        <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                        <span class="relative">{{ $isLive ? 'Join Sekarang' : 'Buka Link' }}</span>
                        <x-icon name="arrow-up-right" class="relative h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                    </a>
                    <button type="button"
                            data-copy-link="{{ $session->meeting_link }}"
                            aria-label="Copy link"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-ink-600/70 bg-ink-900/60 text-ink-200 transition-all hover:border-gold-500/40 hover:text-gold-100">
                        <x-icon name="clipboard" class="h-4 w-4" />
                        <span class="sr-only" data-copy-label>Copy Link</span>
                    </button>
                @else
                    <button type="button"
                            data-copy-link="{{ $session->meeting_link }}"
                            class="group relative inline-flex flex-1 items-center justify-center gap-2 overflow-hidden rounded-full border border-gold-500/40 bg-gold-500/10 px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-gold-100 transition-all hover:border-gold-400/70 hover:bg-gold-500/15">
                        <x-icon name="clipboard" class="h-4 w-4" />
                        <span data-copy-label>Copy Link</span>
                    </button>
                @endif
            </div>
            @unless ($isJoinable)
                <p class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-400">Tombol Join muncul 15 menit sebelum mulai</p>
            @endunless
        </div>
    </div>
</div>

<script>
    (function () {
        const root = document.currentScript.previousElementSibling;
        if (!root || !root.matches('[data-live-session]')) return;

        const startsAt = new Date(root.dataset.startsAt).getTime();
        const endsAt = new Date(root.dataset.endsAt).getTime();
        const grid = root.querySelector('[data-live-countdown-grid]');

        const pad = (n) => String(Math.max(0, n)).padStart(2, '0');

        if (grid) {
            const cells = {
                days: grid.querySelector('[data-countdown-days]'),
                hours: grid.querySelector('[data-countdown-hours]'),
                minutes: grid.querySelector('[data-countdown-minutes]'),
                seconds: grid.querySelector('[data-countdown-seconds]'),
            };
            const tick = () => {
                const diff = startsAt - Date.now();
                if (diff <= 0) {
                    window.location.reload();
                    return;
                }
                const total = Math.floor(diff / 1000);
                const days = Math.floor(total / 86400);
                const hours = Math.floor((total % 86400) / 3600);
                const minutes = Math.floor((total % 3600) / 60);
                const seconds = total % 60;
                cells.days.textContent = pad(days);
                cells.hours.textContent = pad(hours);
                cells.minutes.textContent = pad(minutes);
                cells.seconds.textContent = pad(seconds);
            };
            tick();
            setInterval(tick, 1000);
        }

        if (Date.now() >= startsAt && Date.now() <= endsAt) {
            setTimeout(() => {
                if (Date.now() > endsAt) window.location.reload();
            }, Math.max(1000, endsAt - Date.now()));
        }

        const copyBtn = root.querySelector('[data-copy-link]');
        if (copyBtn) {
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(copyBtn.dataset.copyLink);
                    const label = copyBtn.querySelector('[data-copy-label]');
                    if (label) {
                        const original = label.textContent;
                        label.textContent = 'Tersalin!';
                        label.classList.remove('sr-only');
                        setTimeout(() => {
                            label.textContent = original;
                            label.classList.add('sr-only');
                        }, 1500);
                    }
                } catch (e) {
                    window.open(copyBtn.dataset.copyLink, '_blank', 'noopener');
                }
            });
        }
    })();
</script>
