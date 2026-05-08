<x-layouts.app title="Meja Trader - Weesia">
    <x-dashboard-shell active="positions">
        @php
            $fmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 8, '.', ','), '0'), '.');
            $toneClass = fn ($signal) => match ($signal->statusTone()) {
                'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
                default => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
            };
            $priceMap = $priceMap ?? [];
            $accessMap = $accessMap ?? [];
            $signalSelections = $signalSelections ?? collect();
            $hasSubscription = $hasSubscription ?? false;
            $coinBalance = $coinBalance ?? 0;
            $subscriptionUntil = $subscriptionUntil ?? null;
            $canAccess = fn ($signal) => $accessMap[$signal->id] ?? false;
            $entryFor = fn ($signal) => $signal->entry_price ?: ($priceMap[$signal->ticker] ?? null);
            $liveFor = fn ($signal) => $priceMap[$signal->ticker] ?? null;
            $usd = fn ($value) => $value ? '$'.$fmt($value) : '-';
            $percent = fn ($value) => $value === null ? '-' : ($value > 0 ? '+' : '').rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.').'%';
            $roiTo = fn ($signal, $target) => $target ? $signal->leveragedMoveTo((float) $target, $entryFor($signal)) : null;

            $positionCount = $signalSelections->where('type', \App\Models\UserSignalPosition::TYPE_POSITION)->count();
            $watchlistCount = $signalSelections->where('type', \App\Models\UserSignalPosition::TYPE_WATCHLIST)->count();
            $activeCount = $signalSelections->filter(fn ($s) => $s->tradeSignal && $s->tradeSignal->status === \App\Models\TradeSignal::STATUS_ACTIVE)->count();
            $closedCount = $signalSelections->count() - $activeCount;
            $tickerFilters = $signalSelections->map(fn ($s) => $s->tradeSignal?->ticker)->filter()->unique()->values();
            $totalSelections = $signalSelections->count();
        @endphp

        <section class="relative overflow-hidden border-b border-ink-700/60 bg-[linear-gradient(180deg,rgba(18,18,16,0.96),rgba(5,5,5,1))] pt-24 sm:pt-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="positions-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                            <path d="M 42 0 L 0 0 0 42" fill="none" stroke="#d4a72c" stroke-width="0.7" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#positions-grid)" />
                </svg>
            </div>
            <div class="pointer-events-none absolute -left-24 top-32 h-72 w-72 rounded-full bg-gold-500/10 blur-[120px]"></div>
            <div class="pointer-events-none absolute -right-24 top-10 h-96 w-96 rounded-full bg-emerald-500/5 blur-[140px]"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-8 sm:px-6 sm:pb-10">
                <div class="reveal flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.3em] text-ink-300 transition-colors hover:text-gold-200">
                            <x-icon name="arrow-right" class="h-3 w-3 rotate-180" />
                            Kembali ke Analisa
                        </a>
                        <div class="mt-4 inline-flex w-fit items-center gap-3">
                            <span class="h-px w-10 bg-gold-500/60"></span>
                            <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Trader Desk</span>
                        </div>
                        <h1 class="mt-5 max-w-3xl font-display text-4xl leading-[1.05] text-ink-50 sm:text-6xl">Posisi Saya.</h1>
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-ink-200">Meja kerja kamu - kumpulan analisa yang sudah kamu pasang sebagai posisi atau pantauan, dengan chart live, level kunci, dan kalkulator P/L pribadi di satu tempat.</p>
                    </div>

                    <div class="grid w-full grid-cols-2 gap-3 sm:grid-cols-4 lg:w-auto lg:max-w-xl">
                        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-emerald-200/80">Posisi Aktif</div>
                            <div class="mt-2 font-display text-3xl text-emerald-200">{{ $positionCount }}</div>
                        </div>
                        <div class="rounded-2xl border border-gold-500/20 bg-gold-500/5 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-gold-200/80">Pantauan</div>
                            <div class="mt-2 font-display text-3xl text-gold-200">{{ $watchlistCount }}</div>
                        </div>
                        <div class="rounded-2xl border border-ink-700/70 bg-ink-900/60 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-300">Berjalan</div>
                            <div class="mt-2 font-display text-3xl text-ink-50">{{ $activeCount }}</div>
                        </div>
                        <div class="rounded-2xl border border-ink-700/70 bg-ink-900/60 p-4 backdrop-blur-md">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-300">Selesai</div>
                            <div class="mt-2 font-display text-3xl text-ink-50">{{ $closedCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 sm:py-8">
            @if (session('status'))
                <div class="reveal rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="reveal rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if ($totalSelections === 0)
                <div class="reveal rounded-2xl border border-dashed border-gold-500/25 bg-ink-900/60 p-10 text-center backdrop-blur-md sm:p-14">
                    <div class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                        <x-icon name="clipboard" class="h-7 w-7" />
                    </div>
                    <h2 class="mt-6 font-display text-3xl text-ink-50">Meja kamu masih kosong.</h2>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Pasang analisa sebagai <span class="text-emerald-200">Posisi</span> atau <span class="text-gold-200">Pantauan</span> dari halaman analisa, dan semua chart-nya akan tampil di sini.</p>
                    <a href="{{ route('user.dashboard') }}#signals" class="mt-7 inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(212,167,44,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(212,167,44,1)]">
                        <x-icon name="target" class="h-4 w-4" />
                        Pilih Analisa
                    </a>
                </div>
            @else
                <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-3 backdrop-blur-xl sm:p-4" data-position-filter-root>
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filter tipe posisi">
                            <button type="button" data-position-type-filter="all" aria-pressed="true" class="inline-flex items-center gap-2 rounded-full border border-gold-500/50 bg-gold-500/15 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-100 transition-colors">
                                <x-icon name="activity" class="h-3.5 w-3.5" />
                                Semua
                                <span class="rounded-full bg-gold-500/20 px-1.5 py-0.5 text-[9px] text-gold-100">{{ $totalSelections }}</span>
                            </button>
                            <button type="button" data-position-type-filter="{{ \App\Models\UserSignalPosition::TYPE_POSITION }}" aria-pressed="false" class="inline-flex items-center gap-2 rounded-full border border-ink-700/70 bg-ink-800/50 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-colors hover:border-emerald-500/40 hover:text-emerald-100">
                                <x-icon name="target" class="h-3.5 w-3.5" />
                                Posisi
                                <span class="rounded-full bg-ink-700/60 px-1.5 py-0.5 text-[9px] text-ink-200">{{ $positionCount }}</span>
                            </button>
                            <button type="button" data-position-type-filter="{{ \App\Models\UserSignalPosition::TYPE_WATCHLIST }}" aria-pressed="false" class="inline-flex items-center gap-2 rounded-full border border-ink-700/70 bg-ink-800/50 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-colors hover:border-gold-500/40 hover:text-gold-100">
                                <x-icon name="clipboard" class="h-3.5 w-3.5" />
                                Pantauan
                                <span class="rounded-full bg-ink-700/60 px-1.5 py-0.5 text-[9px] text-ink-200">{{ $watchlistCount }}</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <span data-position-counter class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $totalSelections }} dari {{ $totalSelections }} analisa</span>
                            <button type="button" data-position-filter-clear class="inline-flex h-9 items-center gap-2 rounded-full border border-ink-700/70 bg-ink-900/60 px-3 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">
                                <x-icon name="refresh" class="h-3 w-3" />
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <button type="button" data-position-ticker-filter="all" aria-pressed="true" class="shrink-0 rounded-full border border-gold-500/50 bg-gold-500/15 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-100">Semua</button>
                        @foreach ($tickerFilters as $ticker)
                            <button type="button" data-position-ticker-filter="{{ preg_replace('/[^a-z0-9]/', '', strtolower($ticker)) }}" aria-pressed="false" class="shrink-0 rounded-full border border-ink-700/70 bg-ink-800/50 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 transition-colors hover:border-gold-500/40 hover:text-gold-100">{{ $ticker }}</button>
                        @endforeach
                    </div>
                </div>

                <div data-position-grid class="grid grid-cols-1 gap-5">
                    @foreach ($signalSelections as $selection)
                        @php
                            $signal = $selection->tradeSignal;
                            if (! $signal) { continue; }
                            $unlocked = $canAccess($signal);
                            $cost = $signal->coinCostValue();
                            $tickerKey = preg_replace('/[^a-z0-9]/', '', strtolower($signal->ticker));
                        @endphp

                        <article
                            data-position-item
                            data-filter-type="{{ $selection->type }}"
                            data-filter-ticker="{{ $tickerKey }}"
                            class="overflow-hidden rounded-3xl border border-ink-700/60 bg-gradient-to-b from-ink-900/95 to-ink-900/70 shadow-[0_30px_80px_-50px_rgba(0,0,0,0.9)] backdrop-blur-xl transition-all hover:border-gold-500/30">
                            <div class="grid grid-cols-1 lg:grid-cols-[420px_minmax(0,1fr)] xl:grid-cols-[480px_minmax(0,1fr)]">
                                @if ($unlocked)
                                    <x-live-signal-chart :signal="$signal" :entry="$entryFor($signal)" :current="$liveFor($signal)" />
                                @else
                                    <div class="relative flex min-h-[340px] flex-col items-center justify-center overflow-hidden border-b border-ink-700/60 bg-[radial-gradient(circle_at_50%_30%,rgba(212,167,44,0.12),transparent_60%)] lg:border-b-0 lg:border-r">
                                        <div class="pointer-events-none absolute inset-0 opacity-30">
                                            <svg class="h-full w-full" viewBox="0 0 360 200" preserveAspectRatio="none">
                                                <path d="M0,140 L40,120 L80,128 L120,90 L160,104 L200,68 L240,80 L280,46 L320,56 L360,40" fill="none" stroke="rgba(212,167,44,0.5)" stroke-width="1.5" stroke-dasharray="3 6" />
                                            </svg>
                                        </div>
                                        <span class="relative inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                            <x-icon name="lock" class="h-6 w-6" />
                                        </span>
                                        <div class="relative mt-4 font-display text-xl text-ink-50">Akses Habis</div>
                                        <div class="relative mt-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Buka ulang pakai {{ $cost }} koin</div>
                                    </div>
                                @endif

                                <div class="flex min-w-0 flex-col p-5 sm:p-6">
                                    <div class="flex flex-col gap-3 border-b border-ink-700/50 pb-5 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="font-display text-3xl leading-none text-ink-50 sm:text-4xl">{{ $signal->ticker }}</div>
                                            <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Publish {{ $signal->created_at->format('d M H:i') }} oleh {{ optional($signal->createdBy)->name }}</div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center gap-1 rounded-full border border-gold-500/30 bg-gold-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-200">
                                                <x-icon name="trending-up" class="h-3 w-3" />
                                                {{ $signal->sideLabel() }} {{ $signal->leverageValue() }}x
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] {{ $toneClass($signal) }}">
                                                {{ $signal->statusLabel() }}
                                            </span>
                                            @if ($unlocked)
                                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-emerald-200">
                                                    <x-icon name="check-circle" class="h-3 w-3" />
                                                    Terbuka
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center gap-1 rounded-full border {{ $selection->type === \App\Models\UserSignalPosition::TYPE_POSITION ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-gold-500/25 bg-gold-500/10 text-gold-200' }} px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em]">
                                                <x-icon :name="$selection->type === \App\Models\UserSignalPosition::TYPE_POSITION ? 'target' : 'clipboard'" class="h-3 w-3" />
                                                {{ $selection->typeLabel() }}
                                            </span>
                                        </div>
                                    </div>

                                    @if ($unlocked)
                                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                            <x-level label="Take Profit 1" :value="$fmt($signal->take_profit)" tone="emerald" />
                                            <x-level label="Take Profit 2" :value="$signal->take_profit_2 ? $fmt($signal->take_profit_2) : 'Tidak Ada'" :tone="$signal->take_profit_2 ? 'emerald' : 'ink'" />
                                            <x-level label="Stop Loss" :value="$fmt($signal->stop_loss)" tone="rose" />
                                        </div>

                                        <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-5">
                                            <x-level label="Entry" :value="$usd($entryFor($signal))" tone="gold" />
                                            <x-level label="Live" :value="$usd($liveFor($signal))" data-live-price-card data-live-price-ticker="{{ $signal->ticker }}" />
                                            <x-level label="ROI TP1" :value="$percent($roiTo($signal, $signal->take_profit))" tone="emerald" />
                                            <x-level label="ROI TP2" :value="$signal->take_profit_2 ? $percent($roiTo($signal, $signal->take_profit_2)) : '-'" tone="emerald" />
                                            <x-level label="Risk SL" :value="$percent($roiTo($signal, $signal->stop_loss))" tone="rose" />
                                        </div>

                                        <p class="mt-4 text-sm leading-relaxed text-ink-300">
                                            {{ $signal->status === \App\Models\TradeSignal::STATUS_ACTIVE ? 'Signal masih berjalan. Estimasi ROI memakai entry publish dan leverage signal.' : ($signal->status === \App\Models\TradeSignal::STATUS_HIT_SL ? 'Harga menyentuh stop loss. Evaluasi risk sebelum entry berikutnya.' : 'Harga sudah mencapai target profit.') }}
                                        </p>

                                        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
                                            <div class="rounded-2xl border border-ink-700/60 bg-ink-800/45 p-4"
                                                data-user-entry
                                                data-signal-id="{{ $signal->id }}"
                                                data-signal-ticker="{{ $signal->ticker }}"
                                                data-signal-side="{{ $signal->position_side }}"
                                                data-signal-leverage="{{ $signal->leverageValue() }}"
                                                data-signal-live="{{ $liveFor($signal) }}">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                                        <x-icon name="target" class="h-4 w-4" />
                                                    </span>
                                                    <div class="font-display text-sm text-ink-50">Posisi Pribadi Kamu</div>
                                                    <span class="ml-auto font-mono text-[9px] uppercase tracking-[0.22em] text-ink-300">Live P/L</span>
                                                </div>

                                                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                                                    <label class="block">
                                                        <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Harga Entry Kamu (USD)</span>
                                                        <input type="number" step="any" min="0" inputmode="decimal" data-entry-input placeholder="contoh: {{ $entryFor($signal) }}" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-900 px-3 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                                    </label>
                                                    <div data-entry-result class="rounded-2xl border border-ink-700 bg-ink-900 p-3">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span data-entry-direction class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Belum Diisi</span>
                                                            <span data-entry-icon class="text-ink-300"><x-icon name="activity" class="h-4 w-4" /></span>
                                                        </div>
                                                        <div data-entry-pnl class="mt-1 font-display text-2xl leading-none text-ink-200">-</div>
                                                        <div data-entry-detail class="mt-1 font-mono text-[10px] text-ink-300">Masukkan harga entry untuk lihat estimasi P/L</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex flex-col gap-3 rounded-2xl border border-ink-700/60 bg-ink-800/45 p-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                                        <x-icon name="clipboard" class="h-4 w-4" />
                                                    </span>
                                                    <div class="font-display text-sm text-ink-50">Pengelolaan</div>
                                                    <span class="ml-auto font-mono text-[9px] uppercase tracking-[0.22em] text-ink-300">{{ optional($selection->selected_at)->diffForHumans() ?? 'Baru' }}</span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <form method="POST" action="{{ route('signals.positions.store', $signal) }}">
                                                        @csrf
                                                        <input type="hidden" name="type" value="{{ \App\Models\UserSignalPosition::TYPE_POSITION }}">
                                                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border px-3 text-sm font-medium transition-colors {{ $selection->type === \App\Models\UserSignalPosition::TYPE_POSITION ? 'border-emerald-500/40 bg-emerald-500/15 text-emerald-100' : 'border-ink-600/70 text-ink-200 hover:border-emerald-500/40 hover:text-emerald-100' }}">
                                                            <x-icon name="target" class="h-4 w-4" />
                                                            Posisi
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('signals.positions.store', $signal) }}">
                                                        @csrf
                                                        <input type="hidden" name="type" value="{{ \App\Models\UserSignalPosition::TYPE_WATCHLIST }}">
                                                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border px-3 text-sm font-medium transition-colors {{ $selection->type === \App\Models\UserSignalPosition::TYPE_WATCHLIST ? 'border-gold-500/40 bg-gold-500/15 text-gold-100' : 'border-ink-600/70 text-ink-200 hover:border-gold-500/40 hover:text-gold-100' }}">
                                                            <x-icon name="clipboard" class="h-4 w-4" />
                                                            Pantauan
                                                        </button>
                                                    </form>
                                                </div>
                                                <form method="POST" action="{{ route('signals.positions.destroy', $signal) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-rose-500/25 px-3 text-sm font-medium text-rose-200 transition-colors hover:border-rose-400/50 hover:bg-rose-500/10">
                                                        <x-icon name="x" class="h-4 w-4" />
                                                        Hapus dari Meja
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-gold-500/30 bg-gold-500/5 p-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                                    <x-icon name="wallet" class="h-4 w-4" />
                                                </span>
                                                <div>
                                                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Akses Habis</div>
                                                    <div class="text-sm text-ink-50"><span class="font-display text-2xl text-gold-200">{{ $cost }}</span> koin untuk buka ulang</div>
                                                </div>
                                            </div>
                                            <button type="button"
                                                    data-unlock-trigger
                                                    data-signal-id="{{ $signal->id }}"
                                                    data-signal-ticker="{{ $signal->ticker }}"
                                                    data-signal-side="{{ $signal->sideLabel() }}"
                                                    data-signal-leverage="{{ $signal->leverageValue() }}"
                                                    data-signal-cost="{{ $cost }}"
                                                    data-signal-action="{{ route('signals.unlock', $signal) }}"
                                                    @disabled($coinBalance < $cost)
                                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gold-500 px-5 py-2.5 text-sm font-semibold text-ink-900 transition-colors hover:bg-gold-300 disabled:cursor-not-allowed disabled:bg-ink-700 disabled:text-ink-400 sm:w-auto">
                                                <x-icon name="lock" class="h-4 w-4" />
                                                {{ $coinBalance < $cost ? 'Saldo kurang' : 'Buka Lagi' }}
                                            </button>

                                            <form method="POST" action="{{ route('signals.positions.destroy', $signal) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="Hapus" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-rose-500/25 text-rose-200 transition-colors hover:border-rose-400/50 hover:bg-rose-500/10">
                                                    <x-icon name="x" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div data-position-empty class="hidden rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
                    <div class="font-display text-2xl text-ink-50">Filter belum menemukan analisa.</div>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Coba ticker lain atau ubah tab Posisi/Pantauan.</p>
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

        <div data-modal="unlock-confirm" hidden class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-ink-900/85 p-4 backdrop-blur-xl sm:items-center sm:p-6">
            <div class="relative w-full max-w-md rounded-2xl border border-gold-500/20 bg-ink-900/95 p-4 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.9)] sm:rounded-3xl sm:p-7">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                            <x-icon name="lock" class="h-5 w-5" />
                        </span>
                        <div>
                            <div class="font-display text-xl leading-none text-ink-50">Konfirmasi Pembelian</div>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Buka analisa pakai koin</div>
                        </div>
                    </div>
                    <button type="button" data-modal-close aria-label="Tutup" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-5 rounded-2xl border border-ink-700/60 bg-ink-800/50 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Analisa</div>
                    <div class="mt-1 flex items-center gap-2">
                        <div data-unlock-ticker class="font-display text-2xl text-ink-50">-</div>
                        <span data-unlock-side class="rounded-full border border-gold-500/25 bg-gold-500/10 px-2 py-0.5 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-200">-</span>
                    </div>
                </div>

                <div class="mt-4 space-y-2 rounded-2xl border border-ink-700/60 bg-ink-900 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Saldo Sekarang</span>
                        <span class="font-mono text-sm text-ink-50"><span data-unlock-balance>{{ number_format($coinBalance, 0, ',', '.') }}</span> koin</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300">Biaya</span>
                        <span class="font-mono text-sm text-rose-300">- <span data-unlock-cost>0</span> koin</span>
                    </div>
                    <div class="border-t border-ink-700/60 pt-2"></div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-emerald-300">Saldo Setelah Beli</span>
                        <span class="font-display text-2xl text-emerald-300"><span data-unlock-after>0</span></span>
                    </div>
                </div>

                <p class="mt-4 text-xs leading-relaxed text-ink-300">Pembelian ini sekali bayar dan analisa terbuka selamanya untuk akun kamu.</p>

                <form method="POST" data-unlock-form class="mt-5 flex flex-col-reverse items-stretch gap-3 border-t border-ink-700/60 pt-5 sm:flex-row sm:items-center sm:justify-end">
                    @csrf
                    <button type="button" data-modal-close class="rounded-xl border border-ink-700 px-5 py-2.5 text-sm text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">Batal</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gold-500 px-5 py-2.5 text-sm font-semibold text-ink-900 transition-colors hover:bg-gold-300">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Ya, Buka Sekarang
                    </button>
                </form>
            </div>
        </div>

        <script>
            (() => {
                const root = document.querySelector('[data-position-filter-root]');
                if (!root) return;
                const grid = document.querySelector('[data-position-grid]');
                if (!grid) return;
                const counter = document.querySelector('[data-position-counter]');
                const empty = document.querySelector('[data-position-empty]');
                const items = Array.from(grid.querySelectorAll('[data-position-item]'));
                const total = items.length;
                let typeFilter = 'all';
                let tickerFilter = 'all';

                const apply = () => {
                    let visible = 0;
                    items.forEach((item) => {
                        const matchesType = typeFilter === 'all' || item.dataset.filterType === typeFilter;
                        const matchesTicker = tickerFilter === 'all' || item.dataset.filterTicker === tickerFilter;
                        const show = matchesType && matchesTicker;
                        item.classList.toggle('hidden', !show);
                        if (show) visible += 1;
                    });
                    if (counter) counter.textContent = `${visible} dari ${total} analisa`;
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

                document.querySelectorAll('[data-position-type-filter]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        typeFilter = btn.getAttribute('data-position-type-filter');
                        setActive('[data-position-type-filter]', typeFilter, 'data-position-type-filter');
                        apply();
                    });
                });

                document.querySelectorAll('[data-position-ticker-filter]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        tickerFilter = btn.getAttribute('data-position-ticker-filter');
                        setActive('[data-position-ticker-filter]', tickerFilter, 'data-position-ticker-filter');
                        apply();
                    });
                });

                const clearBtn = document.querySelector('[data-position-filter-clear]');
                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        typeFilter = 'all';
                        tickerFilter = 'all';
                        setActive('[data-position-type-filter]', 'all', 'data-position-type-filter');
                        setActive('[data-position-ticker-filter]', 'all', 'data-position-ticker-filter');
                        apply();
                    });
                }

                apply();
            })();
        </script>
    </x-dashboard-shell>
</x-layouts.app>
