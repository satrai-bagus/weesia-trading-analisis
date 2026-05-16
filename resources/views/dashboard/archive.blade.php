<x-layouts.app title="Arsip Analisa - Weesia">
    <x-dashboard-shell active="archive">
        @php
            $fmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 8, '.', ','), '0'), '.');
            $toneClass = fn ($signal) => match ($signal->statusTone()) {
                'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
                default => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
            };
            $priceMap = $priceMap ?? [];
            $signals = $signals ?? collect();
            $tickerFilters = $tickerFilters ?? collect();
            $tpCount = $tpCount ?? 0;
            $slCount = $slCount ?? 0;
            $entryFor = fn ($signal) => $signal->entry_price ?: ($priceMap[$signal->ticker] ?? null);
            $closeFor = fn ($signal) => $signal->closePrice() ?: $entryFor($signal);
            $usd = fn ($value) => $value ? '$'.$fmt($value) : '-';
            $percent = fn ($value) => $value === null ? '-' : ($value > 0 ? '+' : '').rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.').'%';
            $roiClose = fn ($signal) => $signal->closePrice() ? $signal->leveragedMoveTo((float) $signal->closePrice(), (float) $signal->entry_price) : null;
        @endphp

        <section class="relative overflow-hidden border-b border-ink-700/60 bg-[linear-gradient(180deg,rgba(18,18,16,0.96),rgba(5,5,5,1))] pt-24 sm:pt-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="archive-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                            <path d="M 42 0 L 0 0 0 42" fill="none" stroke="#d4a72c" stroke-width="0.7" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#archive-grid)" />
                </svg>
            </div>
            <div class="pointer-events-none absolute -right-24 top-10 h-96 w-96 rounded-full bg-emerald-500/5 blur-[140px]"></div>
            <div class="pointer-events-none absolute -left-24 top-32 h-72 w-72 rounded-full bg-rose-500/5 blur-[120px]"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-8 sm:px-6 sm:pb-10">
                <div class="reveal flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.3em] text-ink-300 transition-colors hover:text-gold-200">
                            <x-icon name="arrow-right" class="h-3 w-3 rotate-180" />
                            Kembali ke Analisa
                        </a>
                        <div class="mt-4 inline-flex w-fit items-center gap-3">
                            <span class="h-px w-10 bg-gold-500/60"></span>
                            <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Arsip Gratis</span>
                        </div>
                        <h1 class="mt-5 max-w-3xl font-display text-4xl leading-[1.05] text-ink-50 sm:text-6xl">Riwayat Signal.</h1>
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-ink-200">Semua analisa yang sudah selesai berjalan - kena Take Profit atau Stop Loss - terbuka gratis sebagai catatan publik. Chart-nya berhenti di titik akhir sebagai bukti hasil.</p>
                    </div>

                    <div class="grid w-full grid-cols-3 gap-3 lg:w-auto lg:max-w-md">
                        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-emerald-200/80">Kena TP</div>
                            <div class="mt-2 font-display text-3xl text-emerald-200">{{ $tpCount }}</div>
                        </div>
                        <div class="rounded-2xl border border-rose-500/20 bg-rose-500/5 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-rose-200/80">Kena SL</div>
                            <div class="mt-2 font-display text-3xl text-rose-200">{{ $slCount }}</div>
                        </div>
                        <div class="rounded-2xl border border-ink-700/70 bg-ink-900/60 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-300">Winrate</div>
                            <div class="mt-2 font-display text-3xl text-ink-50">{{ ($tpCount + $slCount) > 0 ? round(($tpCount / ($tpCount + $slCount)) * 100) : 0 }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 sm:py-8">
            @if ($signals->isEmpty())
                <div class="reveal rounded-2xl border border-dashed border-gold-500/25 bg-ink-900/60 p-10 text-center backdrop-blur-md sm:p-14">
                    <div class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                        <x-icon name="database" class="h-7 w-7" />
                    </div>
                    <h2 class="mt-6 font-display text-3xl text-ink-50">Arsip masih kosong.</h2>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Setiap signal yang sudah kena Take Profit atau Stop Loss akan otomatis masuk ke sini sebagai riwayat publik.</p>
                    <a href="{{ route('user.dashboard') }}" class="mt-7 inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(212,167,44,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(212,167,44,1)]">
                        <x-icon name="target" class="h-4 w-4" />
                        Lihat Analisa Aktif
                    </a>
                </div>
            @else
                <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-3 backdrop-blur-xl sm:p-4" data-archive-filter-root>
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filter status arsip">
                            <button type="button" data-archive-status-filter="all" aria-pressed="true" class="inline-flex items-center gap-2 rounded-full border border-gold-500/50 bg-gold-500/15 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-100 transition-colors">
                                <x-icon name="activity" class="h-3.5 w-3.5" />
                                Semua
                                <span class="rounded-full bg-gold-500/20 px-1.5 py-0.5 text-[9px] text-gold-100">{{ $signals->count() }}</span>
                            </button>
                            <button type="button" data-archive-status-filter="tp" aria-pressed="false" class="inline-flex items-center gap-2 rounded-full border border-ink-700/70 bg-ink-800/50 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-colors hover:border-emerald-500/40 hover:text-emerald-100">
                                <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                Kena TP
                                <span class="rounded-full bg-ink-700/60 px-1.5 py-0.5 text-[9px] text-ink-200">{{ $tpCount }}</span>
                            </button>
                            <button type="button" data-archive-status-filter="hit_sl" aria-pressed="false" class="inline-flex items-center gap-2 rounded-full border border-ink-700/70 bg-ink-800/50 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-colors hover:border-rose-500/40 hover:text-rose-100">
                                <x-icon name="x" class="h-3.5 w-3.5" />
                                Kena SL
                                <span class="rounded-full bg-ink-700/60 px-1.5 py-0.5 text-[9px] text-ink-200">{{ $slCount }}</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <span data-archive-counter class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $signals->count() }} dari {{ $signals->count() }} arsip</span>
                            <button type="button" data-archive-filter-clear class="inline-flex h-9 items-center gap-2 rounded-full border border-ink-700/70 bg-ink-900/60 px-3 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">
                                <x-icon name="refresh" class="h-3 w-3" />
                                Reset
                            </button>
                        </div>
                    </div>

                    @if ($tickerFilters->isNotEmpty())
                        <div class="mt-3 flex items-center gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                            <button type="button" data-archive-ticker-filter="all" aria-pressed="true" class="shrink-0 rounded-full border border-gold-500/50 bg-gold-500/15 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-100">Semua</button>
                            @foreach ($tickerFilters as $ticker)
                                <button type="button" data-archive-ticker-filter="{{ preg_replace('/[^a-z0-9]/', '', strtolower($ticker)) }}" aria-pressed="false" class="shrink-0 rounded-full border border-ink-700/70 bg-ink-800/50 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 transition-colors hover:border-gold-500/40 hover:text-gold-100">{{ $ticker }}</button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div data-archive-grid class="grid grid-cols-1 gap-5">
                    @foreach ($signals as $signal)
                        @php
                            $closePrice = $signal->closePrice();
                            $entryPrice = $signal->entry_price;
                            $roiActual = $roiClose($signal);
                            $tickerKey = preg_replace('/[^a-z0-9]/', '', strtolower($signal->ticker));
                            $statusGroup = $signal->status === \App\Models\TradeSignal::STATUS_HIT_SL ? 'hit_sl' : 'tp';
                            $accentClass = $statusGroup === 'tp'
                                ? 'border-emerald-500/25 hover:border-emerald-500/50'
                                : 'border-rose-500/25 hover:border-rose-500/50';
                        @endphp

                        <article
                            data-archive-item
                            data-filter-ticker="{{ $tickerKey }}"
                            data-filter-status="{{ $signal->status }}"
                            data-filter-status-group="{{ $statusGroup }}"
                            class="overflow-hidden rounded-3xl border {{ $accentClass }} bg-gradient-to-b from-ink-900/95 to-ink-900/70 shadow-[0_30px_80px_-50px_rgba(0,0,0,0.9)] backdrop-blur-xl transition-all">
                            <div class="grid grid-cols-1 lg:grid-cols-[420px_minmax(0,1fr)] xl:grid-cols-[480px_minmax(0,1fr)]">
                                <x-live-signal-chart :signal="$signal" :entry="$entryPrice" :current="$closePrice" :frozen="true" />

                                <div class="flex min-w-0 flex-col p-5 sm:p-6">
                                    <div class="flex flex-col gap-3 border-b border-ink-700/50 pb-5 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="font-display text-3xl leading-none text-ink-50 sm:text-4xl">{{ $signal->ticker }}</div>
                                            <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">
                                                Publish {{ $signal->created_at->format('d M H:i') }}
                                                @if ($signal->auto_hit_at)
                                                    - Closed {{ $signal->auto_hit_at->diffForHumans() }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center gap-1 rounded-full border border-gold-500/30 bg-gold-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-200">
                                                <x-icon name="trending-up" class="h-3 w-3" />
                                                {{ $signal->sideLabel() }} {{ $signal->leverageValue() }}x
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full border border-ink-500/40 bg-ink-800/70 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-200">
                                                {{ $signal->termLabel() }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] {{ $toneClass($signal) }}">
                                                <x-icon name="check-circle" class="h-3 w-3" />
                                                {{ $signal->statusLabel() }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-emerald-200">
                                                <x-icon name="lock" class="h-3 w-3" />
                                                Arsip Gratis
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                        <x-level label="Take Profit 1" :value="$fmt($signal->take_profit)" tone="emerald" />
                                        <x-level label="Take Profit 2" :value="$signal->take_profit_2 ? $fmt($signal->take_profit_2) : 'Tidak Ada'" :tone="$signal->take_profit_2 ? 'emerald' : 'ink'" />
                                        <x-level label="Stop Loss" :value="$fmt($signal->stop_loss)" tone="rose" />
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
                                        <x-level label="Entry Publish" :value="$usd($entryPrice)" tone="gold" />
                                        <x-level label="Harga Akhir" :value="$usd($closePrice)" :tone="$statusGroup === 'tp' ? 'emerald' : 'rose'" />
                                        <x-level label="Hasil Aktual" :value="$percent($roiActual)" :tone="$statusGroup === 'tp' ? 'emerald' : 'rose'" />
                                        <x-level label="Coin Cost" :value="$signal->coinCostValue() . ' koin'" tone="gold" />
                                    </div>

                                    <p class="mt-4 text-sm leading-relaxed text-ink-300">
                                        @if ($signal->status === \App\Models\TradeSignal::STATUS_HIT_SL)
                                            Signal kena Stop Loss. Catatan ini berguna untuk evaluasi risk plan & timing entry.
                                        @elseif ($signal->status === \App\Models\TradeSignal::STATUS_HIT_TP2)
                                            Signal kena Take Profit 2. Strategi short term/long term berjalan sesuai rencana.
                                        @else
                                            Signal kena Take Profit 1. Konfirmasi target awal tercapai.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div data-archive-empty class="hidden rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
                    <div class="font-display text-2xl text-ink-50">Filter belum menemukan arsip.</div>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Coba ticker lain atau ubah tab Kena TP / Kena SL.</p>
                </div>
            @endif
        </section>

        <div data-modal="fullscreen-chart" hidden class="fixed inset-0 z-[80] bg-ink-900/95 p-3 backdrop-blur-xl sm:p-6">
            <div class="absolute left-3 top-3 z-10 max-w-[70vw] rounded-2xl border border-gold-500/20 bg-ink-900/80 px-3 py-2 backdrop-blur-md sm:left-6 sm:top-6 sm:px-4 sm:py-3">
                <div data-fullscreen-ticker class="truncate font-display text-xl leading-none text-ink-50 sm:text-2xl"></div>
                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-300">Fullscreen Chart</div>
            </div>
            <button type="button" data-modal-close class="absolute right-3 top-3 z-10 inline-flex h-11 w-11 items-center justify-center rounded-full border border-gold-500/30 bg-gold-500/10 text-gold-100 backdrop-blur-md transition-all hover:border-gold-400/70 sm:right-6 sm:top-6" aria-label="Tutup fullscreen">
                <x-icon name="x" class="h-5 w-5" />
            </button>
            <div class="relative h-full w-full overflow-hidden rounded-2xl border border-ink-700/70 bg-ink-900 pt-16 sm:pt-0">
                <div data-fullscreen-host class="h-full w-full"></div>
            </div>
        </div>

        <script>
            (() => {
                const root = document.querySelector('[data-archive-filter-root]');
                if (!root) return;
                const grid = document.querySelector('[data-archive-grid]');
                if (!grid) return;
                const counter = document.querySelector('[data-archive-counter]');
                const empty = document.querySelector('[data-archive-empty]');
                const items = Array.from(grid.querySelectorAll('[data-archive-item]'));
                const total = items.length;
                let statusFilter = 'all';
                let tickerFilter = 'all';

                const apply = () => {
                    let visible = 0;
                    items.forEach((item) => {
                        const matchesStatus = statusFilter === 'all' || item.dataset.filterStatusGroup === statusFilter || item.dataset.filterStatus === statusFilter;
                        const matchesTicker = tickerFilter === 'all' || item.dataset.filterTicker === tickerFilter;
                        const show = matchesStatus && matchesTicker;
                        item.classList.toggle('hidden', !show);
                        if (show) visible += 1;
                    });
                    if (counter) counter.textContent = `${visible} dari ${total} arsip`;
                    if (empty) empty.classList.toggle('hidden', visible !== 0);
                    grid.classList.toggle('hidden', visible === 0 && total > 0);
                };

                const setActive = (selector, value, attr) => {
                    document.querySelectorAll(selector).forEach((btn) => {
                        const isActive = btn.getAttribute(attr) === value;
                        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                        if (isActive) {
                            btn.classList.add('border-gold-500/50', 'bg-gold-500/15', 'text-gold-100');
                            btn.classList.remove('border-ink-700/70', 'bg-ink-800/50', 'text-ink-300');
                        } else {
                            btn.classList.remove('border-gold-500/50', 'bg-gold-500/15', 'text-gold-100');
                            btn.classList.add('border-ink-700/70', 'bg-ink-800/50', 'text-ink-300');
                        }
                    });
                };

                document.querySelectorAll('[data-archive-status-filter]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        statusFilter = btn.getAttribute('data-archive-status-filter');
                        setActive('[data-archive-status-filter]', statusFilter, 'data-archive-status-filter');
                        apply();
                    });
                });

                document.querySelectorAll('[data-archive-ticker-filter]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        tickerFilter = btn.getAttribute('data-archive-ticker-filter');
                        setActive('[data-archive-ticker-filter]', tickerFilter, 'data-archive-ticker-filter');
                        apply();
                    });
                });

                const clearBtn = document.querySelector('[data-archive-filter-clear]');
                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        statusFilter = 'all';
                        tickerFilter = 'all';
                        setActive('[data-archive-status-filter]', 'all', 'data-archive-status-filter');
                        setActive('[data-archive-ticker-filter]', 'all', 'data-archive-ticker-filter');
                        apply();
                    });
                }

                apply();
            })();
        </script>
    </x-dashboard-shell>
</x-layouts.app>
