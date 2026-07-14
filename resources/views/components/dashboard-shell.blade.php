@props(['active' => 'user'])

@php
    $alertPayload = app(\App\Http\Controllers\DashboardController::class)->alertsPayload();
    $alertCount = $alertPayload['unread_count'] ?? 0;
    $alertItems = $alertPayload['items'] ?? [];
    $alertToneClass = fn (string $tone) => match ($tone) {
        'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
        'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
        default => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
    };
    $shellUser = auth()->user();
    $isUserRole = $shellUser->role !== 'admin';
    $shellCoinBalance = $isUserRole ? (int) $shellUser->coin_balance : 0;
    $shellHasSubscription = $isUserRole ? $shellUser->hasActiveSubscription() : false;

    $navLiveSession = $isUserRole
        ? app(\App\Http\Controllers\DashboardController::class)->upcomingLiveSession()
        : null;
    $navLiveStatus = $navLiveSession?->status();
    $navLiveIsLive = $navLiveStatus === \App\Models\LiveSession::STATUS_LIVE;
    $navLiveIsToday = $navLiveSession && $navLiveSession->scheduledAtLocal()->isToday();
    $navLivePillTone = $navLiveIsLive
        ? 'border-rose-500/60 bg-rose-500/15 text-rose-100'
        : ($navLiveIsToday
            ? 'border-gold-400/60 bg-gold-500/15 text-gold-100'
            : 'border-gold-500/30 bg-gold-500/5 text-gold-200');
    $navLivePillDot = ($navLiveIsLive || $navLiveIsToday) ? 'bg-rose-400' : 'bg-gold-300';
    $navLivePillLabel = $navLiveIsLive
        ? 'Live'
        : ($navLiveIsToday
            ? 'Live Session'
            : $navLiveSession?->scheduledAtLocal()->translatedFormat('D, d M'));

    // Sidebar (desktop, khusus role user)
    $sideNav = [
        ['key' => 'user', 'url' => route('user.dashboard'), 'label' => 'Dashboard', 'icon' => 'bar-chart'],
        ['key' => 'positions', 'url' => route('user.positions'), 'label' => 'Posisi Saya', 'icon' => 'clipboard'],
        ['key' => 'archive', 'url' => route('user.archive'), 'label' => 'Arsip Analisa', 'icon' => 'database'],
        ['key' => 'education', 'url' => route('education.index'), 'label' => 'Edukasi', 'icon' => 'brain'],
    ];
    $isOrdersPage = request()->routeIs('orders.*');
    $pageTitle = match (true) {
        $isOrdersPage => 'Pesanan Saya',
        default => match ($active) {
            'user' => 'Dashboard',
            'positions' => 'Posisi Saya',
            'archive' => 'Arsip Analisa',
            'live' => 'Live Session',
            'education' => 'Edukasi',
            'checkout' => 'Beli Token',
            default => 'Dashboard',
        },
    };
    $shellInitials = collect(explode(' ', trim((string) $shellUser->name)))
        ->filter()
        ->map(fn ($word) => mb_substr($word, 0, 1))
        ->take(2)
        ->implode('');
@endphp

<main class="min-h-screen overflow-x-hidden bg-ink-900 text-ink-100">
    @if ($isUserRole)
        {{-- ===== Sidebar desktop (khusus user) ===== --}}
        <aside class="fixed inset-y-0 left-0 z-50 hidden w-64 flex-col border-r border-gold-500/10 bg-ink-900/95 backdrop-blur-xl lg:flex" aria-label="Navigasi dashboard">
            <a href="{{ route('landing') }}" class="flex h-20 shrink-0 items-center gap-3 border-b border-gold-500/10 px-5">
                <x-brand-mark />
                <span class="min-w-0 leading-none">
                    <span class="block truncate font-mono text-[14px] font-semibold uppercase tracking-[0.3em] text-ink-50">Weesia</span>
                    <span class="mt-1 block truncate font-mono text-[8px] uppercase tracking-[0.24em] text-gold-400/80">FibPath Analyzer</span>
                </span>
            </a>

            <nav class="scrollbar-slim flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <div class="px-2 pb-2 font-mono text-[9px] uppercase tracking-[0.3em] text-ink-400">Menu</div>
                @foreach ($sideNav as $item)
                    <a href="{{ $item['url'] }}"
                       @if ($active === $item['key']) aria-current="page" @endif
                       class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm transition-all {{ $active === $item['key'] ? 'border-gold-500/40 bg-gold-500/10 text-gold-100' : 'border-transparent text-ink-300 hover:border-ink-600/70 hover:bg-ink-800/50 hover:text-ink-100' }}">
                        <x-icon :name="$item['icon']" class="h-4 w-4 shrink-0 {{ $active === $item['key'] ? 'text-gold-200' : 'text-ink-400' }}" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
                @if ($navLiveSession)
                    <a href="{{ route('user.live') }}"
                       @if ($active === 'live') aria-current="page" @endif
                       title="{{ $navLiveSession->title }} - {{ $navLiveSession->scheduledAtLocal()->translatedFormat('D, d M Y H:i') }} WIB"
                       class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm transition-all {{ $active === 'live' ? 'border-gold-500/40 bg-gold-500/10 text-gold-100' : 'border-transparent text-ink-300 hover:border-ink-600/70 hover:bg-ink-800/50 hover:text-ink-100' }}">
                        <x-icon name="video" class="h-4 w-4 shrink-0 {{ $active === 'live' ? 'text-gold-200' : 'text-ink-400' }}" />
                        <span class="min-w-0 flex-1 truncate">Live Session</span>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2 py-0.5 font-mono text-[8px] uppercase tracking-[0.16em] {{ $navLivePillTone }}">
                            <span class="relative inline-flex h-1.5 w-1.5">
                                @if ($navLiveIsLive || $navLiveIsToday)
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $navLivePillDot }} opacity-80"></span>
                                @endif
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full {{ $navLivePillDot }}"></span>
                            </span>
                            {{ $navLivePillLabel }}
                        </span>
                    </a>
                @endif

                <div class="px-2 pb-2 pt-5 font-mono text-[9px] uppercase tracking-[0.3em] text-ink-400">Akun</div>
                <a href="{{ route('checkout') }}"
                   class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm transition-all {{ $active === 'checkout' && ! $isOrdersPage ? 'border-gold-500/40 bg-gold-500/10 text-gold-100' : 'border-transparent text-ink-300 hover:border-ink-600/70 hover:bg-ink-800/50 hover:text-ink-100' }}">
                    <x-icon name="wallet" class="h-4 w-4 shrink-0 {{ $active === 'checkout' && ! $isOrdersPage ? 'text-gold-200' : 'text-ink-400' }}" />
                    Beli Token
                </a>
                <a href="{{ route('orders.index') }}"
                   class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm transition-all {{ $isOrdersPage ? 'border-gold-500/40 bg-gold-500/10 text-gold-100' : 'border-transparent text-ink-300 hover:border-ink-600/70 hover:bg-ink-800/50 hover:text-ink-100' }}">
                    <x-icon name="clock" class="h-4 w-4 shrink-0 {{ $isOrdersPage ? 'text-gold-200' : 'text-ink-400' }}" />
                    Pesanan Saya
                </a>
            </nav>

            <div class="px-4 pb-4">
                @if ($shellHasSubscription)
                    <div class="rounded-2xl border border-emerald-500/25 bg-[linear-gradient(135deg,rgba(52,211,153,0.14),rgba(18,18,16,0.9)_70%)] p-4">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                            <x-icon name="shield-check" class="h-4 w-4" />
                        </span>
                        <div class="mt-3 font-display text-lg leading-tight text-ink-50">Akses Penuh Aktif</div>
                        <p class="mt-1 text-xs leading-relaxed text-ink-300">Semua analisa terbuka sampai {{ optional($shellUser->subscription_until)->format('d M Y') }}.</p>
                        <a href="{{ route('checkout') }}" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-2 text-xs font-semibold text-emerald-100 transition-colors hover:bg-emerald-500/20">
                            Perpanjang
                            <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                @else
                    <div class="relative overflow-hidden rounded-2xl border border-gold-500/30 bg-[linear-gradient(135deg,rgba(23,209,131,0.22),rgba(18,18,16,0.92)_65%)] p-4">
                        <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-gold-500/25 blur-2xl"></div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                            <x-icon name="sparkles" class="h-4 w-4" />
                        </span>
                        <div class="mt-3 font-display text-lg leading-tight text-ink-50">Akses Penuh</div>
                        <p class="mt-1 text-xs leading-relaxed text-ink-300">Buka semua analisa aktif tanpa koin per-analisa.</p>
                        <a href="{{ route('checkout') }}" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-gold-300 to-gold-500 px-4 py-2 text-xs font-semibold text-ink-900 shadow-[0_10px_28px_-10px_rgba(23,209,131,0.8)] transition-all hover:shadow-[0_14px_36px_-10px_rgba(23,209,131,1)]">
                            Upgrade Sekarang
                            <x-icon name="arrow-up-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                @endif
            </div>

            <div class="border-t border-ink-700/60 p-3">
                <div class="flex items-center gap-3 px-3 py-2.5">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-gold-500/30 bg-gradient-to-b from-gold-500/25 to-gold-500/5 font-display text-base text-gold-100">{{ $shellInitials ?: 'W' }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm text-ink-50">{{ $shellUser->name }}</span>
                        @if ($shellHasSubscription)
                            <span class="mt-0.5 flex items-center gap-1 font-mono text-[9px] uppercase tracking-[0.2em] text-emerald-300">
                                <x-icon name="shield-check" class="h-3 w-3" />
                                Subscriber
                            </span>
                        @else
                            <span class="mt-0.5 block font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">Trader</span>
                        @endif
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-sm text-ink-300 transition-all hover:border-rose-500/30 hover:bg-rose-500/5 hover:text-rose-200">
                        <x-icon name="log-out" class="h-4 w-4 shrink-0" />
                        Keluar
                    </button>
                </form>
            </div>
        </aside>
    @endif

    <header class="fixed left-0 right-0 top-0 z-40 border-b border-gold-500/10 bg-ink-900/75 backdrop-blur-xl {{ $isUserRole ? 'lg:left-64' : '' }}">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:h-20 sm:px-6">
            <a href="{{ route('landing') }}" class="flex min-w-0 items-center gap-2 sm:gap-3 {{ $isUserRole ? 'lg:hidden' : '' }}">
                <x-brand-mark />
                <span class="min-w-0 leading-none">
                    <span class="block truncate font-mono text-[13px] font-semibold uppercase tracking-[0.3em] text-ink-50 sm:text-[15px] sm:tracking-[0.34em]">Weesia</span>
                    <span class="mt-1 block truncate font-mono text-[8px] uppercase tracking-[0.22em] text-gold-400/80 max-[380px]:hidden sm:text-[9px] sm:tracking-[0.25em]">FibPath Analyzer</span>
                </span>
            </a>
            @if ($isUserRole)
                <div class="hidden min-w-0 lg:block">
                    <div class="truncate font-display text-xl leading-none text-ink-50">{{ $pageTitle }}</div>
                    <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.26em] text-ink-400">Weesia Trader Desk</div>
                </div>
            @endif

            @php
                $navLink = fn (string $key, string $url, string $label) => sprintf(
                    '<a href="%s" class="whitespace-nowrap rounded-full border px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] transition-all %s">%s</a>',
                    $url,
                    $active === $key
                        ? 'border-gold-500/50 bg-gold-500/10 text-gold-100'
                        : 'border-transparent text-ink-300 hover:border-ink-600 hover:text-ink-100',
                    $label,
                );
            @endphp
            @if (auth()->user()->role === 'admin')
                <nav class="hidden items-center gap-1 md:flex">
                    {!! $navLink('admin', route('admin.dashboard'), 'Dashboard') !!}
                    {!! $navLink('admin-signals', route('admin.signals'), 'Signal') !!}
                    {!! $navLink('admin-users', route('admin.users'), 'User') !!}
                    {!! $navLink('admin-orders', route('admin.orders'), 'Pesanan') !!}
                    {!! $navLink('admin-live-sessions', route('admin.live-sessions'), 'Live Session') !!}
                    {!! $navLink('admin-education', route('admin.education'), 'Edukasi') !!}
                </nav>
            @else
                <nav class="hidden items-center gap-1 md:flex lg:hidden">
                    {!! $navLink('user', route('user.dashboard'), 'Analisa') !!}
                    {!! $navLink('positions', route('user.positions'), 'Posisi Saya') !!}
                    {!! $navLink('archive', route('user.archive'), 'Arsip Gratis') !!}
                    @if ($navLiveSession)
                        <a href="{{ route('user.live') }}"
                           aria-label="Halaman sesi live trading"
                           title="{{ $navLiveSession->title }} - {{ $navLiveSession->scheduledAtLocal()->translatedFormat('D, d M Y H:i') }} WIB"
                           class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] transition-all hover:brightness-110 {{ $active === 'live' ? 'border-gold-500/70 bg-gold-500/15 text-gold-50' : $navLivePillTone }}">
                            <span class="relative inline-flex h-2 w-2">
                                @if ($navLiveIsLive || $navLiveIsToday)
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $navLivePillDot }} opacity-80"></span>
                                @endif
                                <span class="relative inline-flex h-2 w-2 rounded-full {{ $navLivePillDot }}"></span>
                            </span>
                            {{ $navLivePillLabel }}
                        </a>
                    @endif
                    {!! $navLink('education', route('education.index'), 'Edukasi') !!}
                </nav>
            @endif

            <div class="flex items-center gap-2 sm:gap-3">
                @if ($isUserRole)
                    <a href="{{ route('checkout') }}"
                       aria-label="Saldo koin - klik untuk top up"
                       class="inline-flex items-center gap-2 rounded-full border border-gold-500/30 bg-gold-500/10 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200 transition-all hover:border-gold-400/70 hover:text-gold-100">
                        <x-icon name="wallet" class="h-3.5 w-3.5" />
                        <span>{{ number_format($shellCoinBalance, 0, ',', '.') }} <span class="hidden sm:inline">koin</span></span>
                    </a>
                    <a href="{{ route('checkout') }}"
                       class="hidden md:inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-4 py-1.5 font-mono text-[10px] font-semibold uppercase tracking-[0.2em] text-ink-900 shadow-[0_10px_30px_-10px_rgba(23,209,131,0.7)] transition-all hover:shadow-[0_14px_40px_-10px_rgba(23,209,131,0.95)]">
                        {{ $shellHasSubscription ? 'Perpanjang' : 'Beli Token' }}
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                @endif

                <div class="hidden text-right {{ $isUserRole ? '' : 'lg:block' }}">
                    <div class="text-sm text-ink-50">{{ auth()->user()->name }}</div>
                    <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ auth()->user()->role }}</div>
                </div>

                <div class="relative" data-alert-bell>
                    <button type="button"
                            data-alert-toggle
                            aria-label="Notifikasi analisa"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/20 bg-gold-500/10 text-gold-100 transition-all hover:border-gold-400/70">
                        <x-icon name="bell" class="h-4 w-4" />
                        <span data-alert-badge
                              class="pointer-events-none absolute -top-1 -right-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full border border-ink-900 bg-rose-500 px-1 font-mono text-[10px] font-semibold text-ink-50 {{ $alertCount > 0 ? '' : 'hidden' }}">{{ $alertCount }}</span>
                    </button>

                    <div data-alert-panel class="fixed left-4 right-4 top-[4.5rem] hidden max-h-[70vh] origin-top-right overflow-hidden rounded-2xl border border-ink-700/70 bg-ink-900/95 shadow-[0_30px_60px_-30px_rgba(0,0,0,0.9)] backdrop-blur-xl sm:absolute sm:left-auto sm:right-0 sm:top-auto sm:mt-3 sm:w-80 sm:max-w-[90vw]">
                        <div class="flex items-center justify-between border-b border-ink-700/60 px-4 py-3">
                            <div>
                                <div class="font-display text-sm leading-none text-ink-50">Notifikasi</div>
                                <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.22em] text-ink-300">Auto-hit feed</div>
                            </div>
                            <button type="button" data-alert-clear class="font-mono text-[10px] uppercase tracking-[0.2em] text-gold-300 transition-colors hover:text-gold-100">Tandai dibaca</button>
                        </div>
                        <div data-alert-list class="scrollbar-slim max-h-80 overflow-y-auto">
                            @forelse ($alertItems as $item)
                                <div data-alert-item="{{ $item['id'] }}" class="flex items-start gap-3 border-b border-ink-700/50 px-4 py-3 last:border-0 {{ $item['is_unread'] ? 'bg-gold-500/5' : '' }}">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $item['tone'] === 'rose' ? 'bg-rose-400 shadow-[0_0_10px_rgba(244,63,94,0.7)]' : ($item['tone'] === 'emerald' ? 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.7)]' : 'bg-gold-400') }}"></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="truncate font-display text-sm text-ink-50">{{ $item['ticker'] }}</div>
                                            <span class="shrink-0 rounded-full border px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] {{ $alertToneClass($item['tone']) }}">{{ $item['status_label'] }}</span>
                                        </div>
                                        <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">{{ $item['side'] }} {{ $item['leverage'] }}x - {{ $item['auto_hit_human'] }}</div>
                                    </div>
                                </div>
                            @empty
                                <div data-alert-empty class="px-4 py-6 text-center text-xs text-ink-300">Belum ada analisa yang menyentuh level.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="{{ $isUserRole ? 'lg:hidden' : '' }}">
                    @csrf
                    <button aria-label="Logout" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/20 bg-gold-500/10 text-gold-100 transition-all hover:border-gold-400/70">
                        <x-icon name="log-out" class="h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>

        @if (auth()->user()->role === 'admin')
            <nav class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 pb-3 md:hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="Admin navigation mobile">
                {!! $navLink('admin', route('admin.dashboard'), 'Dashboard') !!}
                {!! $navLink('admin-signals', route('admin.signals'), 'Signal') !!}
                {!! $navLink('admin-users', route('admin.users'), 'User') !!}
                {!! $navLink('admin-orders', route('admin.orders'), 'Pesanan') !!}
                {!! $navLink('admin-live-sessions', route('admin.live-sessions'), 'Live Session') !!}
                {!! $navLink('admin-education', route('admin.education'), 'Edukasi') !!}
            </nav>
        @else
            <nav class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 pb-3 md:hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="User navigation mobile">
                {!! $navLink('user', route('user.dashboard'), 'Analisa') !!}
                {!! $navLink('positions', route('user.positions'), 'Posisi Saya') !!}
                {!! $navLink('archive', route('user.archive'), 'Arsip Gratis') !!}
                @if ($navLiveSession)
                    <a href="{{ route('user.live') }}"
                       aria-label="Halaman sesi live trading"
                       class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] transition-all hover:brightness-110 {{ $active === 'live' ? 'border-gold-500/70 bg-gold-500/15 text-gold-50' : $navLivePillTone }}">
                        <span class="relative inline-flex h-2 w-2">
                            @if ($navLiveIsLive || $navLiveIsToday)
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $navLivePillDot }} opacity-80"></span>
                            @endif
                            <span class="relative inline-flex h-2 w-2 rounded-full {{ $navLivePillDot }}"></span>
                        </span>
                        {{ $navLivePillLabel }}
                    </a>
                @endif
                {!! $navLink('education', route('education.index'), 'Edukasi') !!}
                {!! $navLink('checkout', route('checkout'), 'Beli Token') !!}
                {!! $navLink('orders', route('orders.index'), 'Pesanan') !!}
            </nav>
        @endif
    </header>

    <div data-alert-toast-host class="pointer-events-none fixed left-4 right-4 top-20 z-[60] flex flex-col gap-3 sm:left-auto sm:right-6 sm:top-24 sm:w-80 sm:max-w-[90vw]"></div>

    <div class="{{ $isUserRole ? 'lg:pl-64' : '' }}">
        {{ $slot }}
    </div>
</main>
