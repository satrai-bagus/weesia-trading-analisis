<x-layouts.app title="User Dashboard - Weesia">
    <x-dashboard-shell active="user">
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
            $selectionMap = $selectionMap ?? collect();
            $hasSubscription = $hasSubscription ?? false;
            $coinBalance = $coinBalance ?? 0;
            $subscriptionUntil = $subscriptionUntil ?? null;
            $canAccess = fn ($signal) => $accessMap[$signal->id] ?? false;
            $selectionFor = fn ($signal) => $selectionMap->get($signal->id);
            $entryFor = fn ($signal) => $signal->entry_price ?: ($priceMap[$signal->ticker] ?? null);
            $liveFor = fn ($signal) => $priceMap[$signal->ticker] ?? null;
            $liveRefFor = fn ($signal) => $liveFor($signal) ?: $entryFor($signal);
            $usd = fn ($value) => $value ? '$'.$fmt($value) : '-';
            $percent = fn ($value) => $value === null ? '-' : ($value > 0 ? '+' : '').rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.').'%';
            $roiTo = fn ($signal, $target) => $target ? $signal->leveragedMoveTo((float) $target, $liveRefFor($signal)) : null;
            $selectedRows = $signalSelections->map(function ($selection) use ($canAccess, $fmt) {
                $signal = $selection->tradeSignal;

                if (! $signal) {
                    return null;
                }

                $unlocked = $canAccess($signal);

                return [
                    'signal' => $signal,
                    'ticker' => $signal->ticker,
                    'type' => $selection->type,
                    'type_label' => $selection->typeLabel(),
                    'type_tone' => $selection->typeTone(),
                    'tp1' => $unlocked ? $fmt($signal->take_profit) : null,
                    'tp2' => $unlocked ? ($signal->take_profit_2 ? $fmt($signal->take_profit_2) : '-') : null,
                    'sl' => $unlocked ? $fmt($signal->stop_loss) : null,
                    'status' => $signal->statusLabel(),
                    'locked' => ! $unlocked,
                    'cost' => $signal->coinCostValue(),
                    'selected_human' => optional($selection->selected_at)->diffForHumans(),
                ];
            })->filter()->values();
            $positionCount = $signalSelections->where('type', \App\Models\UserSignalPosition::TYPE_POSITION)->count();
            $watchlistCount = $signalSelections->where('type', \App\Models\UserSignalPosition::TYPE_WATCHLIST)->count();
            $timeline = $signals->take(3)->map(function ($signal) use ($canAccess, $fmt) {
                if (! $canAccess($signal)) {
                    return $signal->ticker.' '.strtolower($signal->statusLabel()).' - belum dibuka';
                }
                return $signal->ticker.' '.strtolower($signal->statusLabel()).' - TP1 '.$fmt($signal->take_profit).', SL '.$fmt($signal->stop_loss);
            });
            $lockedCount = $signals->filter(fn ($s) => ! $canAccess($s))->count();
            $unlockedCount = $signals->count() - $lockedCount;
            $freeArchiveCount = $signals->filter(fn ($s) => $s->status !== \App\Models\TradeSignal::STATUS_ACTIVE)->count();
            $tickerFilters = $signals->pluck('ticker')->unique()->values();
        @endphp

        <section class="relative overflow-hidden border-b border-ink-700/60 bg-[linear-gradient(180deg,rgba(18,18,16,0.96),rgba(5,5,5,1))] pt-24 sm:pt-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="user-dashboard-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                            <path d="M 42 0 L 0 0 0 42" fill="none" stroke="#d4a72c" stroke-width="0.7" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#user-dashboard-grid)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 pb-8 sm:px-6 sm:pb-10">
                <div>
                    <div class="reveal flex min-h-[260px] flex-col justify-end sm:min-h-[320px]">
                        <div class="inline-flex w-fit items-center gap-3">
                            <span class="h-px w-10 bg-gold-500/60"></span>
                            <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">User Dashboard</span>
                        </div>
                        <h1 class="mt-5 max-w-3xl font-display text-4xl leading-[1.05] text-ink-50 sm:text-6xl">Meja kerja trader.</h1>
                        <p class="mt-6 max-w-2xl text-base leading-relaxed text-ink-200">Lihat signal aktif, estimasi profit, posisi pribadi, pantauan market, dan risk plan yang mengikuti harga market secara real-time.</p>
                        <div class="mt-8 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                            <a href="#signals" class="group inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-5 py-3 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(212,167,44,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(212,167,44,1)] sm:px-6">
                                <x-icon name="target" class="h-4 w-4" />
                                Analisa Baru
                                <x-icon name="arrow-up-right" class="h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                            </a>
                            <a href="{{ route('user.positions') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-ink-500/50 bg-ink-800/60 px-5 py-3 text-sm font-medium text-ink-100 backdrop-blur-md transition-all hover:border-gold-500/50 hover:text-gold-100 sm:px-6">
                                <x-icon name="clipboard" class="h-4 w-4" />
                                Posisi Saya
                                <x-icon name="arrow-up-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>

                </div>

                <div class="reveal mt-10 grid grid-cols-1 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/40 sm:grid-cols-2 lg:grid-cols-4">
                    <x-stat-card label="Signal Tersedia" :value="$stats['total']" change="dipublish admin" />
                    <x-stat-card label="Signal Aktif" :value="$stats['active']" change="masih berjalan" tone="emerald" />
                    <x-stat-card label="Winrate Signal" :value="$stats['winrate'].'%'" change="berdasarkan signal selesai" tone="emerald" />
                    <x-stat-card label="Posisi Kamu" :value="$positionCount" :change="$watchlistCount.' pantauan'" />
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

            <div class="reveal flex flex-col items-start justify-between gap-4 rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:flex-row sm:items-center sm:p-5">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border {{ $hasSubscription ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-gold-500/25 bg-gold-500/10 text-gold-200' }}">
                        <x-icon :name="$hasSubscription ? 'shield-check' : 'wallet'" class="h-5 w-5" />
                    </span>
                    <div>
                        @if ($hasSubscription)
                            <div class="font-display text-lg text-ink-50">Akses Penuh Aktif</div>
                            <div class="mt-1 text-xs text-ink-300">Subscription aktif sampai {{ $subscriptionUntil->format('d M Y') }} - semua {{ $signals->count() }} analisa terbuka.</div>
                        @else
                            <div class="font-display text-lg text-ink-50">Mode Per-Analisa</div>
                            <div class="mt-1 text-xs text-ink-300">Saldo {{ number_format($coinBalance, 0, ',', '.') }} koin - {{ $lockedCount }} signal aktif terkunci. {{ $freeArchiveCount > 0 ? $freeArchiveCount.' arsip TP/SL terbuka gratis.' : 'Signal selesai akan otomatis terbuka gratis.' }}</div>
                        @endif
                    </div>
                </div>
                @if ($hasSubscription)
                    <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-emerald-200">
                            <x-icon name="shield-check" class="h-3.5 w-3.5" />
                            Subscriber
                        </span>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div id="signals" class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5" data-signal-carousel data-signal-filter-root data-page-size="7">
                    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="trending-up" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-2xl leading-none text-ink-50">Sinyal Trading</h2>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Real-time</div>
                            </div>
                        </div>
                        @if ($signals->count() > 7)
                            <div class="flex w-full items-center justify-between gap-2 sm:w-auto sm:justify-end">
                                <span data-carousel-status class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300"></span>
                                <button type="button" data-carousel-prev aria-label="Signal sebelumnya" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100 disabled:cursor-not-allowed disabled:opacity-35">
                                    <x-icon name="arrow-right" class="h-4 w-4 rotate-180" />
                                </button>
                                <button type="button" data-carousel-next aria-label="Signal berikutnya" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/25 bg-gold-500/10 text-gold-100 transition-all hover:border-gold-400/70 disabled:cursor-not-allowed disabled:opacity-35">
                                    <x-icon name="arrow-right" class="h-4 w-4" />
                                </button>
                            </div>
                        @else
                            <span class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 px-3 py-2 text-xs text-ink-200">
                                <x-icon name="refresh" class="h-4 w-4" />
                                Live
                            </span>
                        @endif
                    </div>

                    <div class="mb-5 rounded-2xl border border-ink-700/60 bg-ink-800/35 p-3 sm:p-4">
                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.5fr)_180px_180px_auto]">
                            <label class="relative">
                                <span class="sr-only">Cari ticker crypto</span>
                                <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-300" />
                                <input
                                    type="search"
                                    data-signal-search
                                    placeholder="Cari ticker, contoh BTC atau ETH"
                                    class="min-h-12 w-full rounded-2xl border border-ink-700/70 bg-ink-900/80 py-3 pl-11 pr-4 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50"
                                >
                            </label>
                            <label>
                                <span class="sr-only">Filter arah posisi</span>
                                <select data-signal-side-filter class="min-h-12 w-full rounded-2xl border border-ink-700/70 bg-ink-900/80 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    <option value="all">Semua Posisi</option>
                                    <option value="{{ \App\Models\TradeSignal::SIDE_LONG }}">Long Only</option>
                                    <option value="{{ \App\Models\TradeSignal::SIDE_SHORT }}">Short Only</option>
                                </select>
                            </label>
                            <label>
                                <span class="sr-only">Filter status signal</span>
                                <select data-signal-status-filter class="min-h-12 w-full rounded-2xl border border-ink-700/70 bg-ink-900/80 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    <option value="all">Semua Status</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_ACTIVE }}">Aktif / Running</option>
                                    <option value="tp">Sudah TP</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_TP }}">Kena TP1</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_TP2 }}">Kena TP2</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_SL }}">Kena SL</option>
                                    <option value="closed">Arsip TP/SL</option>
                                </select>
                            </label>
                            <button type="button" data-signal-filter-clear class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-ink-700/70 bg-ink-900/80 px-4 text-sm font-medium text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">
                                Reset
                            </button>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <div class="flex min-w-0 flex-1 gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                <button type="button" data-signal-ticker-filter="all" class="shrink-0 rounded-full border border-gold-500/50 bg-gold-500/15 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-100" aria-pressed="true">Semua</button>
                                @foreach ($tickerFilters as $ticker)
                                    <button type="button" data-signal-ticker-filter="{{ preg_replace('/[^a-z0-9]/', '', strtolower($ticker)) }}" class="shrink-0 rounded-full border border-ink-700/70 bg-ink-800/50 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 transition-colors hover:border-gold-500/40 hover:text-gold-100" aria-pressed="false">{{ $ticker }}</button>
                                @endforeach
                            </div>
                            <span data-signal-filter-count class="hidden shrink-0 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 sm:inline"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4" data-carousel-list>
                        @forelse ($signals as $signal)
                            @php $unlocked = $canAccess($signal); $cost = $signal->coinCostValue(); $selection = $selectionFor($signal); @endphp
                            @if ($unlocked)
                                <article data-carousel-item data-signal-row data-signal-ticker="{{ $signal->ticker }}" data-signal-side="{{ $signal->position_side }}" data-signal-leverage="{{ $signal->leverageValue() }}" data-signal-tp1="{{ $signal->take_profit }}" data-signal-tp2="{{ $signal->take_profit_2 }}" data-signal-sl="{{ $signal->stop_loss }}" data-signal-live="{{ $liveFor($signal) }}" data-filter-ticker="{{ $signal->ticker }}" data-filter-ticker-key="{{ preg_replace('/[^a-z0-9]/', '', strtolower($signal->ticker)) }}" data-filter-side="{{ $signal->position_side }}" data-filter-status="{{ $signal->status }}" data-filter-access="{{ $unlocked ? 'open' : 'locked' }}" class="grid grid-cols-1 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-900 transition-colors hover:bg-ink-800/45 lg:grid-cols-[390px_minmax(0,1fr)] xl:grid-cols-[430px_minmax(0,1fr)]">
                                    <x-live-signal-chart :signal="$signal" :entry="$entryFor($signal)" :current="$liveFor($signal)" />
                                    <div class="p-4 sm:p-5">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div class="font-display text-2xl leading-none text-ink-50 sm:text-3xl">{{ $signal->ticker }}</div>
                                                <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.16em] text-ink-300 sm:tracking-[0.22em]">Publish {{ $signal->created_at->format('d M H:i') }} oleh {{ optional($signal->createdBy)->name }}</div>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="inline-flex w-fit rounded-full border border-gold-500/25 bg-gold-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-200">{{ $signal->sideLabel() }} {{ $signal->leverageValue() }}x</span>
                                                <span class="inline-flex w-fit rounded-full border border-ink-500/40 bg-ink-800/70 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-200">{{ $signal->termLabel() }}</span>
                                                <span class="inline-flex w-fit rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] {{ $toneClass($signal) }}">{{ $signal->statusLabel() }}</span>
                                                <span class="inline-flex w-fit items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-emerald-200">
                                                    <x-icon name="check-circle" class="h-3 w-3" />
                                                    {{ $signal->status !== \App\Models\TradeSignal::STATUS_ACTIVE ? 'Arsip Gratis' : ($hasSubscription ? 'Subscriber' : 'Terbuka') }}
                                                </span>
                                                @if ($selection)
                                                    <span class="inline-flex w-fit items-center gap-1 rounded-full border {{ $selection->type === \App\Models\UserSignalPosition::TYPE_POSITION ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-gold-500/25 bg-gold-500/10 text-gold-200' }} px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em]">
                                                        <x-icon :name="$selection->type === \App\Models\UserSignalPosition::TYPE_POSITION ? 'target' : 'clipboard'" class="h-3 w-3" />
                                                        {{ $selection->typeLabel() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                            <x-level label="Take Profit 1" :value="$fmt($signal->take_profit)" tone="emerald" />
                                            <x-level label="Take Profit 2" :value="$signal->take_profit_2 ? $fmt($signal->take_profit_2) : 'Tidak Ada'" :tone="$signal->take_profit_2 ? 'emerald' : 'ink'" />
                                            <x-level label="Stop Loss" :value="$fmt($signal->stop_loss)" tone="rose" />
                                        </div>
                                        <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
                                            <x-level label="Entry (Live)" :value="$usd($liveRefFor($signal))" tone="gold" data-entry-level />
                                            <x-level label="ROI TP1" :value="$percent($roiTo($signal, $signal->take_profit))" tone="emerald" data-roi-tp1 />
                                            <x-level label="ROI TP2" :value="$signal->take_profit_2 ? $percent($roiTo($signal, $signal->take_profit_2)) : '-'" tone="emerald" data-roi-tp2 />
                                            <x-level label="Risk SL" :value="$percent($roiTo($signal, $signal->stop_loss))" tone="rose" data-roi-sl />
                                        </div>
                                        <p class="mt-4 text-sm leading-relaxed text-ink-300">
                                            {{ $signal->status === \App\Models\TradeSignal::STATUS_ACTIVE ? 'Signal masih berjalan. Estimasi ROI memakai harga live realtime sebagai entry.' : ($signal->status === \App\Models\TradeSignal::STATUS_HIT_SL ? 'Harga menyentuh stop loss. Evaluasi risk sebelum entry berikutnya.' : 'Harga sudah mencapai target profit.') }}
                                        </p>

                                        <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-ink-700/60 bg-ink-800/45 p-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                                    <x-icon name="clipboard" class="h-4 w-4" />
                                                </span>
                                                <div>
                                                    <div class="font-display text-sm text-ink-50">Halaman Posisi</div>
                                                    <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">
                                                        {{ $selection ? $selection->typeLabel().' tersimpan' : 'Belum dipilih' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-2 min-[420px]:flex-row">
                                                <form method="POST" action="{{ route('signals.positions.store', $signal) }}">
                                                    @csrf
                                                    <input type="hidden" name="type" value="{{ \App\Models\UserSignalPosition::TYPE_POSITION }}">
                                                    <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl border px-4 text-sm font-medium transition-colors min-[420px]:w-auto {{ $selection?->type === \App\Models\UserSignalPosition::TYPE_POSITION ? 'border-emerald-500/40 bg-emerald-500/15 text-emerald-100' : 'border-ink-600/70 text-ink-200 hover:border-emerald-500/40 hover:text-emerald-100' }}">
                                                        <x-icon name="target" class="h-4 w-4" />
                                                        Posisi
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('signals.positions.store', $signal) }}">
                                                    @csrf
                                                    <input type="hidden" name="type" value="{{ \App\Models\UserSignalPosition::TYPE_WATCHLIST }}">
                                                    <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl border px-4 text-sm font-medium transition-colors min-[420px]:w-auto {{ $selection?->type === \App\Models\UserSignalPosition::TYPE_WATCHLIST ? 'border-gold-500/40 bg-gold-500/15 text-gold-100' : 'border-ink-600/70 text-ink-200 hover:border-gold-500/40 hover:text-gold-100' }}">
                                                        <x-icon name="clipboard" class="h-4 w-4" />
                                                        Pantauan
                                                    </button>
                                                </form>
                                                @if ($selection)
                                                    <form method="POST" action="{{ route('signals.positions.destroy', $signal) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl border border-rose-500/25 px-4 text-sm font-medium text-rose-200 transition-colors hover:border-rose-400/50 hover:bg-rose-500/10 min-[420px]:w-auto">
                                                            <x-icon name="x" class="h-4 w-4" />
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-5 rounded-2xl border border-ink-700/60 bg-ink-800/45 p-4"
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
                                    </div>
                                </article>
                            @else
                                <article data-carousel-item data-filter-ticker="{{ $signal->ticker }}" data-filter-ticker-key="{{ preg_replace('/[^a-z0-9]/', '', strtolower($signal->ticker)) }}" data-filter-side="{{ $signal->position_side }}" data-filter-status="{{ $signal->status }}" data-filter-access="{{ $unlocked ? 'open' : 'locked' }}" class="grid grid-cols-1 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-900 transition-colors hover:bg-ink-800/45">
                                    <div class="grid grid-cols-1 lg:grid-cols-[390px_minmax(0,1fr)] xl:grid-cols-[430px_minmax(0,1fr)]">
                                        <div class="relative flex h-[260px] items-center justify-center overflow-hidden border-b border-ink-700/60 bg-[radial-gradient(circle_at_50%_30%,rgba(212,167,44,0.12),transparent_60%)] lg:h-auto lg:border-b-0 lg:border-r">
                                            <div class="pointer-events-none absolute inset-0 opacity-30">
                                                <svg class="h-full w-full" viewBox="0 0 360 200" preserveAspectRatio="none">
                                                    <path d="M0,140 L40,120 L80,128 L120,90 L160,104 L200,68 L240,80 L280,46 L320,56 L360,40" fill="none" stroke="rgba(212,167,44,0.5)" stroke-width="1.5" stroke-dasharray="3 6" />
                                                </svg>
                                            </div>
                                            <div class="relative flex flex-col items-center gap-3 text-center">
                                                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                                    <x-icon name="lock" class="h-6 w-6" />
                                                </span>
                                                <div class="font-display text-xl text-ink-50">Analisa Terkunci</div>
                                                <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Buka pakai koin</div>
                                            </div>
                                        </div>
                                        <div class="p-4 sm:p-5">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <div class="font-display text-2xl leading-none text-ink-50 sm:text-3xl">{{ $signal->ticker }}</div>
                                                    <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.16em] text-ink-300 sm:tracking-[0.22em]">Publish {{ $signal->created_at->format('d M H:i') }} oleh {{ optional($signal->createdBy)->name }}</div>
                                                </div>
                                                <div class="flex flex-wrap gap-2">
                                                    <span class="inline-flex w-fit rounded-full border border-gold-500/25 bg-gold-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-200">{{ $signal->sideLabel() }} {{ $signal->leverageValue() }}x</span>
                                                    <span class="inline-flex w-fit rounded-full border border-ink-500/40 bg-ink-800/70 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-200">{{ $signal->termLabel() }}</span>
                                                    <span class="inline-flex w-fit rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] {{ $toneClass($signal) }}">{{ $signal->statusLabel() }}</span>
                                                </div>
                                            </div>

                                            <div class="mt-5 grid grid-cols-1 gap-3 min-[420px]:grid-cols-3">
                                                @foreach (['Take Profit 1', 'Take Profit 2', 'Stop Loss'] as $label)
                                                    <div class="rounded-2xl border border-ink-700 bg-ink-800/50 p-3">
                                                        <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">{{ $label }}</div>
                                                        <div class="mt-1 flex items-center gap-2 font-mono text-sm text-ink-300">
                                                            <x-icon name="lock" class="h-3.5 w-3.5" />
                                                            <span class="select-none blur-[6px]">******</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <p class="mt-4 text-sm leading-relaxed text-ink-300">Buka analisa ini untuk lihat entry, target profit, stop loss, dan ROI lengkap.</p>

                                            <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-gold-500/30 bg-gold-500/5 p-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                                        <x-icon name="wallet" class="h-4 w-4" />
                                                    </span>
                                                    <div>
                                                        <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Biaya Buka</div>
                                                        <div class="text-sm text-ink-50"><span class="font-display text-2xl text-gold-200">{{ $cost }}</span> koin</div>
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
                                                    {{ $coinBalance < $cost ? 'Saldo kurang' : 'Buka Analisa' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endif
                        @empty
                            <div class="rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
                                <div class="font-display text-2xl text-ink-50">Belum ada signal trading.</div>
                                <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Signal dari admin akan muncul di sini setelah dipublish.</p>
                            </div>
                        @endforelse
                    </div>

                    <div data-signal-filter-empty class="hidden rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
                        <div class="font-display text-2xl text-ink-50">Filter belum menemukan signal.</div>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Coba ticker lain, ubah status, atau reset filter untuk melihat semua analisa.</p>
                    </div>
                </div>

                <a href="{{ route('user.positions') }}" id="watchlist" class="reveal group block overflow-hidden rounded-2xl border border-gold-500/20 bg-[linear-gradient(120deg,rgba(212,167,44,0.08),rgba(18,18,16,0.85)_55%)] p-5 backdrop-blur-xl transition-all hover:border-gold-400/45 hover:shadow-[0_28px_70px_-40px_rgba(212,167,44,0.6)] sm:p-6">
                    <div class="flex flex-col items-start gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-4">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                <x-icon name="clipboard" class="h-5 w-5" />
                            </span>
                            <div>
                                <div class="font-mono text-[10px] uppercase tracking-[0.28em] text-gold-300">Trader Desk</div>
                                <h2 class="mt-2 font-display text-2xl leading-none text-ink-50 sm:text-3xl">Buka Meja Posisi Saya</h2>
                                <p class="mt-2 max-w-xl text-sm leading-relaxed text-ink-300">{{ $positionCount }} posisi aktif & {{ $watchlistCount }} pantauan tersusun rapi dengan chart live, level kunci, dan kalkulator P/L pribadi di tiap analisa.</p>
                            </div>
                        </div>
                        <div class="flex w-full items-center justify-between gap-3 lg:w-auto">
                            <div class="flex gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-emerald-200">
                                    <x-icon name="target" class="h-3 w-3" />
                                    {{ $positionCount }} posisi
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full border border-gold-500/30 bg-gold-500/10 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200">
                                    <x-icon name="clipboard" class="h-3 w-3" />
                                    {{ $watchlistCount }} pantauan
                                </span>
                            </div>
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-gold-500/30 bg-gold-500/10 text-gold-100 transition-transform group-hover:translate-x-1">
                                <x-icon name="arrow-up-right" class="h-5 w-5" />
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <aside class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="gauge" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-2xl leading-none text-ink-50">Risk Plan</h2>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Rules</div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <x-progress label="Daily Risk" value="54" />
                        <x-progress label="Trade Capacity" value="75" />
                        <x-progress label="Discipline Score" value="88" />
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/50">
                        <div class="bg-ink-900 p-4">
                            <x-icon name="check-circle" class="h-5 w-5 text-emerald-300" />
                            <div class="mt-3 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Rules</div>
                            <div class="mt-1 text-sm text-ink-50">Active</div>
                        </div>
                        <div class="bg-ink-900 p-4">
                            <x-icon name="database" class="h-5 w-5 text-emerald-300" />
                            <div class="mt-3 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Journal</div>
                            <div class="mt-1 text-sm text-ink-50">42 logs</div>
                        </div>
                    </div>
                </div>

                <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="bell" class="h-5 w-5" />
                            </span>
                            <h2 class="font-display text-2xl leading-none text-ink-50">Aktivitas</h2>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 px-3 py-2 text-xs text-ink-200">
                            <x-icon name="search" class="h-4 w-4" />
                            Cari
                        </span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($timeline as $item)
                            <div class="flex gap-3 border-b border-ink-700/60 pb-4 last:border-0 last:pb-0">
                                <span class="mt-1 h-2 w-2 rounded-full bg-gold-400 shadow-[0_0_14px_rgba(212,167,44,0.8)]"></span>
                                <div>
                                    <p class="text-sm leading-relaxed text-ink-100">{{ $item }}</p>
                                    <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">baru saja</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm leading-relaxed text-ink-300">Belum ada signal dari admin untuk ditampilkan.</div>
                        @endforelse
                    </div>
                </div>
            </aside>
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
    </x-dashboard-shell>
</x-layouts.app>
