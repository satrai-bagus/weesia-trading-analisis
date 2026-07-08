<x-layouts.app title="Live Session - Weesia">
    <x-dashboard-shell active="live">
        @php
            $featuredStatus = $featured?->status();
            $featuredIsLive = $featuredStatus === \App\Models\LiveSession::STATUS_LIVE;
            $featuredIsToday = $featured && $featured->scheduledAtLocal()->isToday();
            $featuredJoinable = $featured && $featured->isJoinable();
            $upcomingCount = $upcoming->count();
            $liveNowCount = $upcoming->filter(fn ($s) => $s->status() === \App\Models\LiveSession::STATUS_LIVE)->count();
            $todayCount = $upcoming->filter(fn ($s) => $s->scheduledAtLocal()->isToday())->count();
            $totalCount = $upcomingCount + $past->count();
        @endphp

        <section class="relative overflow-hidden border-b border-ink-700/60 bg-[linear-gradient(180deg,rgba(18,18,16,0.96),rgba(5,5,5,1))] pt-24 sm:pt-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="live-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                            <path d="M 42 0 L 0 0 0 42" fill="none" stroke="#17d183" stroke-width="0.7" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#live-grid)" />
                </svg>
            </div>
            <div class="pointer-events-none absolute -left-24 top-32 h-72 w-72 rounded-full bg-gold-500/10 blur-[120px]"></div>
            <div class="pointer-events-none absolute -right-24 top-10 h-96 w-96 rounded-full {{ $featuredIsLive ? 'bg-rose-500/10' : 'bg-gold-500/5' }} blur-[140px]"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-8 sm:px-6 sm:pb-10">
                <div class="reveal flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.3em] text-ink-300 transition-colors hover:text-gold-200">
                            <x-icon name="arrow-right" class="h-3 w-3 rotate-180" />
                            Kembali ke Analisa
                        </a>
                        <div class="mt-4 inline-flex w-fit items-center gap-3">
                            <span class="h-px w-10 bg-gold-500/60"></span>
                            <span class="font-mono text-[10px] uppercase tracking-[0.3em] {{ $featuredIsLive ? 'text-rose-300' : 'text-gold-300' }}">Live Trading Session</span>
                        </div>
                        <h1 class="mt-5 max-w-3xl font-display text-4xl leading-[1.05] text-ink-50 sm:text-6xl">
                            {{ $featuredIsLive ? 'On Air Sekarang.' : ($featuredIsToday ? 'Jadwal Hari Ini.' : 'Live Trading.') }}
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-ink-200">Jadwal sesi live trading bareng admin - countdown realtime, akses langsung ke meeting room, plus arsip sesi yang sudah berlalu.</p>
                    </div>

                    <div class="grid w-full grid-cols-2 gap-3 sm:grid-cols-4 lg:w-auto lg:max-w-xl">
                        <div class="rounded-2xl border {{ $liveNowCount > 0 ? 'border-rose-500/30 bg-rose-500/10' : 'border-ink-700/70 bg-ink-900/60' }} p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] {{ $liveNowCount > 0 ? 'text-rose-200/80' : 'text-ink-300' }}">Live Sekarang</div>
                            <div class="mt-2 font-display text-3xl {{ $liveNowCount > 0 ? 'text-rose-200' : 'text-ink-50' }}">{{ $liveNowCount }}</div>
                        </div>
                        <div class="rounded-2xl border {{ $todayCount > 0 ? 'border-gold-500/30 bg-gold-500/10' : 'border-ink-700/70 bg-ink-900/60' }} p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] {{ $todayCount > 0 ? 'text-gold-200/80' : 'text-ink-300' }}">Hari Ini</div>
                            <div class="mt-2 font-display text-3xl {{ $todayCount > 0 ? 'text-gold-200' : 'text-ink-50' }}">{{ $todayCount }}</div>
                        </div>
                        <div class="rounded-2xl border border-ink-700/70 bg-ink-900/60 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-300">Akan Datang</div>
                            <div class="mt-2 font-display text-3xl text-ink-50">{{ $upcomingCount }}</div>
                        </div>
                        <div class="rounded-2xl border border-ink-700/70 bg-ink-900/60 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-300">Total Sesi</div>
                            <div class="mt-2 font-display text-3xl text-ink-50">{{ $totalCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 sm:py-10">
            @if (session('status'))
                <div class="reveal rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif

            @if ($featured)
                <div class="reveal">
                    <x-live-session-banner :session="$featured" />
                </div>
            @endif

            @if ($upcoming->count() > 1 || ($featured && $past->isEmpty()))
                <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                            <x-icon name="calendar" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="font-display text-2xl leading-none text-ink-50">Jadwal Mendatang</h2>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">{{ $upcomingCount }} sesi</div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse ($upcoming->skip($featured ? 1 : 0) as $session)
                            @php
                                $sStatus = $session->status();
                                $sIsLive = $sStatus === \App\Models\LiveSession::STATUS_LIVE;
                                $sIsToday = $session->scheduledAtLocal()->isToday();
                                $sJoinable = $session->isJoinable();
                                $sBadgeTone = $sIsLive
                                    ? 'border-rose-500/40 bg-rose-500/15 text-rose-100'
                                    : ($sIsToday
                                        ? 'border-gold-400/50 bg-gold-500/15 text-gold-100'
                                        : 'border-ink-700/70 bg-ink-800/60 text-ink-200');
                                $sBadgeLabel = $sIsLive ? 'Live' : ($sIsToday ? 'Hari Ini' : 'Akan Datang');
                            @endphp
                            <article class="rounded-2xl border {{ $sIsLive ? 'border-rose-500/30' : 'border-ink-700/60' }} bg-ink-900/60 p-4 transition-colors hover:bg-ink-800/45 sm:p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    @if ($session->cover_image_path)
                                        <div class="overflow-hidden rounded-xl border border-ink-700/60 bg-ink-800/40 sm:w-40 sm:shrink-0">
                                            <img src="{{ asset('storage/'.$session->cover_image_path) }}" alt="{{ $session->title }}" class="aspect-video w-full object-cover">
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.22em] {{ $sBadgeTone }}">
                                                @if ($sIsLive || $sIsToday)
                                                    <span class="relative inline-flex h-2 w-2">
                                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $sIsLive ? 'bg-rose-400' : 'bg-gold-300' }} opacity-80"></span>
                                                        <span class="relative inline-flex h-2 w-2 rounded-full {{ $sIsLive ? 'bg-rose-400' : 'bg-gold-300' }}"></span>
                                                    </span>
                                                @endif
                                                {{ $sBadgeLabel }}
                                            </span>
                                            <span class="rounded-full border border-ink-700/70 bg-ink-800/60 px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200">{{ $session->providerLabel() }}</span>
                                            <span class="rounded-full border border-ink-700/70 bg-ink-800/60 px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200">{{ $session->duration_minutes }}m</span>
                                        </div>
                                        <h3 class="mt-2 font-display text-xl leading-tight text-ink-50">{{ $session->title }}</h3>
                                        @if ($session->description)
                                            <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-ink-300">{{ $session->description }}</p>
                                        @endif
                                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">
                                            <span class="inline-flex items-center gap-1.5">
                                                <x-icon name="calendar" class="h-3 w-3 text-gold-300" />
                                                {{ $session->scheduledAtLocal()->translatedFormat('l, d M Y') }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <x-icon name="clock" class="h-3 w-3 text-gold-300" />
                                                {{ $session->scheduledAtLocal()->format('H:i') }} WIB
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                                        @if ($sJoinable)
                                            <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener"
                                               class="inline-flex items-center gap-2 rounded-xl {{ $sIsLive ? 'bg-gradient-to-b from-rose-400 to-rose-600 text-ink-50' : 'bg-gradient-to-b from-gold-300 to-gold-500 text-ink-900' }} px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] transition-all hover:brightness-110">
                                                {{ $sIsLive ? 'Join' : 'Buka Link' }}
                                                <x-icon name="arrow-up-right" class="h-3.5 w-3.5" />
                                            </a>
                                        @endif
                                        <button type="button" data-copy-link="{{ $session->meeting_link }}"
                                                class="inline-flex items-center gap-2 rounded-xl border border-ink-600/70 bg-ink-900/60 px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-ink-200 transition-all hover:border-gold-500/40 hover:text-gold-100">
                                            <x-icon name="clipboard" class="h-3.5 w-3.5" />
                                            <span data-copy-label>Copy</span>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @empty
                            @if (! $featured)
                                <div class="rounded-2xl border border-dashed border-ink-700/60 bg-ink-800/30 px-6 py-12 text-center">
                                    <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full border border-ink-700/70 bg-ink-800/60 text-ink-400">
                                        <x-icon name="calendar" class="h-5 w-5" />
                                    </div>
                                    <p class="mt-3 text-sm text-ink-300">Belum ada sesi mendatang.</p>
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>
            @endif

            @if (! $featured && $upcoming->isEmpty())
                <div class="reveal rounded-2xl border border-dashed border-ink-700/60 bg-ink-800/30 px-6 py-16 text-center">
                    <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full border border-gold-500/30 bg-gold-500/10 text-gold-200">
                        <x-icon name="video" class="h-6 w-6" />
                    </div>
                    <h2 class="mt-4 font-display text-2xl text-ink-50">Belum Ada Jadwal Live</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink-300">Sesi live trading dari admin akan tampil di sini begitu jadwal di-publish. Cek lagi nanti ya!</p>
                </div>
            @endif

            @if ($past->isNotEmpty())
                <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-ink-700/70 bg-ink-800/60 text-ink-300">
                            <x-icon name="clock" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="font-display text-2xl leading-none text-ink-50">Arsip Sesi</h2>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">{{ $past->count() }} sesi terakhir</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        @foreach ($past as $session)
                            <div class="rounded-2xl border border-ink-700/60 bg-ink-800/35 p-4">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full border border-ink-700/70 bg-ink-800/60 px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Selesai</span>
                                    <span class="rounded-full border border-ink-700/70 bg-ink-800/60 px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">{{ $session->providerLabel() }}</span>
                                </div>
                                <div class="mt-2 truncate font-display text-base text-ink-100">{{ $session->title }}</div>
                                <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-400">
                                    {{ $session->scheduledAtLocal()->translatedFormat('d M Y') }} - {{ $session->scheduledAtLocal()->format('H:i') }} WIB
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <script>
            document.querySelectorAll('[data-copy-link]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const link = btn.dataset.copyLink;
                    const label = btn.querySelector('[data-copy-label]');
                    try {
                        await navigator.clipboard.writeText(link);
                        if (label) {
                            const original = label.textContent;
                            label.textContent = 'Tersalin!';
                            setTimeout(() => { label.textContent = original; }, 1500);
                        }
                    } catch (e) {
                        window.open(link, '_blank', 'noopener');
                    }
                });
            });
        </script>
    </x-dashboard-shell>
</x-layouts.app>
