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
            $liveSession = $liveSession ?? null;
            $canAccess = fn ($signal) => $accessMap[$signal->id] ?? false;
            $selectionFor = fn ($signal) => $selectionMap->get($signal->id);
            $entryFor = fn ($signal) => $signal->entry_price ?: ($priceMap[$signal->ticker] ?? null);
            $liveFor = fn ($signal) => $priceMap[$signal->ticker] ?? null;
            $liveRefFor = fn ($signal) => $liveFor($signal) ?: $entryFor($signal);
            $usd = fn ($value) => $value ? '$'.$fmt($value) : '-';
            $percent = fn ($value) => $value === null ? '-' : ($value > 0 ? '+' : '').rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.').'%';
            $roiTo = fn ($signal, $target) => $target ? $signal->leveragedMoveTo((float) $target, $liveRefFor($signal)) : null;
            $sideBadge = fn ($signal) => $signal->position_side === \App\Models\TradeSignal::SIDE_SHORT
                ? 'border-rose-500/30 bg-rose-500/10 text-rose-300'
                : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300';
            $selectedRows = $signalSelections->map(function ($selection) use ($canAccess, $fmt) {
                $signal = $selection->tradeSignal;

                if (! $signal) {
                    return null;
                }

                // Signal yang sudah selesai (arsip) selalu terbuka gratis.
                $unlocked = $signal->status !== \App\Models\TradeSignal::STATUS_ACTIVE || $canAccess($signal);

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
            $archiveCount = $archiveCount ?? 0;
            $tickerFilters = $signals->pluck('ticker')->unique()->values();
        @endphp

        {{-- ===== Header band (slim) ===== --}}
        <section class="relative overflow-hidden border-b border-ink-700/60 bg-[linear-gradient(180deg,rgba(18,18,16,0.96),rgba(5,5,5,1))] pt-24 sm:pt-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="user-dashboard-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                            <path d="M 42 0 L 0 0 0 42" fill="none" stroke="#17d183" stroke-width="0.7" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#user-dashboard-grid)" />
                </svg>
            </div>
            <div class="pointer-events-none absolute -right-24 -top-10 h-72 w-72 rounded-full bg-gold-500/10 blur-[130px]"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-6 sm:px-6 sm:pb-8">
                <div class="reveal min-w-0">
                    <div class="inline-flex w-fit items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Trader Desk</span>
                    </div>
                    <h1 class="mt-4 font-display text-3xl leading-[1.05] text-ink-50 sm:text-5xl">Meja Trader.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-ink-200 sm:text-base">Semua analisa live, level kunci, dan posisi kamu dalam satu layar - update harga real-time tiap 30 detik.</p>
                </div>

                {{-- Notifikasi belum dibaca --}}
                @include('dashboard.partials.alert-digest')

                {{-- KPI strip --}}
                <div class="reveal mt-7 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/40 lg:grid-cols-4">
                    <x-stat-card label="Analisa Aktif" :value="$stats['active']" :change="'dari '.$stats['total'].' total analisa'" tone="emerald" />
                    <x-stat-card label="Winrate" :value="$stats['winrate'].'%'" :change="$stats['hitTp'].' kena TP - '.$stats['hitSl'].' kena SL'" tone="emerald" />
                    <x-stat-card label="Posisi Kamu" :value="$positionCount" :change="$watchlistCount.' pantauan tersimpan'" />
                    <x-stat-card label="Arsip Gratis" :value="$archiveCount" change="bukti hasil TP / SL" />
                </div>
            </div>
        </section>

        {{-- ===== Main dashboard grid ===== --}}
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

            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px] xl:grid-cols-[minmax(0,1fr)_340px]">
                {{-- ===== LEFT: Analisa panel ===== --}}
                <div id="signals" class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5" data-signal-carousel data-signal-filter-root data-page-size="6">
                    <div class="mb-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="trending-up" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-2xl leading-none text-ink-50">Analisa Trading</h2>
                                <div class="mt-1 inline-flex items-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">
                                    <span class="relative inline-flex h-1.5 w-1.5">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"></span>
                                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                    </span>
                                    Real-time
                                </div>
                            </div>
                        </div>
                        @if ($signals->count() > 6)
                            <div class="flex w-full items-center justify-between gap-2 sm:w-auto sm:justify-end">
                                <span data-carousel-status class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300"></span>
                                <button type="button" data-carousel-prev aria-label="Analisa sebelumnya" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100 disabled:cursor-not-allowed disabled:opacity-35">
                                    <x-icon name="arrow-right" class="h-4 w-4 rotate-180" />
                                </button>
                                <button type="button" data-carousel-next aria-label="Analisa berikutnya" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/25 bg-gold-500/10 text-gold-100 transition-all hover:border-gold-400/70 disabled:cursor-not-allowed disabled:opacity-35">
                                    <x-icon name="arrow-right" class="h-4 w-4" />
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Filter bar --}}
                    <div class="mb-4 rounded-2xl border border-ink-700/60 bg-ink-800/35 p-3">
                        <div class="grid grid-cols-1 gap-2 min-[520px]:grid-cols-[minmax(0,1fr)_170px_auto]">
                            <label class="relative">
                                <span class="sr-only">Cari ticker crypto</span>
                                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-300" />
                                <input
                                    type="search"
                                    data-signal-search
                                    placeholder="Cari ticker, contoh BTC"
                                    class="min-h-11 w-full rounded-xl border border-ink-700/70 bg-ink-900/80 py-2.5 pl-10 pr-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50"
                                >
                            </label>
                            <label>
                                <span class="sr-only">Filter arah posisi</span>
                                <select data-signal-side-filter class="min-h-11 w-full rounded-xl border border-ink-700/70 bg-ink-900/80 px-3 py-2.5 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    <option value="all">Semua Posisi</option>
                                    <option value="{{ \App\Models\TradeSignal::SIDE_LONG }}">Long Only</option>
                                    <option value="{{ \App\Models\TradeSignal::SIDE_SHORT }}">Short Only</option>
                                </select>
                            </label>
                            <button type="button" data-signal-filter-clear class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-ink-700/70 bg-ink-900/80 px-4 text-sm font-medium text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">
                                <x-icon name="refresh" class="h-3.5 w-3.5" />
                                Reset
                            </button>
                        </div>

                        <div class="mt-2.5 flex items-center justify-between gap-3">
                            <div class="flex min-w-0 flex-1 gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                <button type="button" data-signal-ticker-filter="all" class="shrink-0 rounded-full border border-gold-500/50 bg-gold-500/15 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-100" aria-pressed="true">Semua</button>
                                @foreach ($tickerFilters as $ticker)
                                    <button type="button" data-signal-ticker-filter="{{ preg_replace('/[^a-z0-9]/', '', strtolower($ticker)) }}" class="shrink-0 rounded-full border border-ink-700/70 bg-ink-800/50 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 transition-colors hover:border-gold-500/40 hover:text-gold-100" aria-pressed="false">{{ $ticker }}</button>
                                @endforeach
                            </div>
                            <span data-signal-filter-count class="hidden shrink-0 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 sm:inline"></span>
                        </div>
                    </div>

                    {{-- Compact card grid --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-carousel-list>
                        @forelse ($signals as $signal)
                            @php $unlocked = $canAccess($signal); $cost = $signal->coinCostValue(); $selection = $selectionFor($signal); @endphp
                            @if ($unlocked)
                                <article data-carousel-item data-signal-row data-signal-ticker="{{ $signal->ticker }}" data-signal-side="{{ $signal->position_side }}" data-signal-leverage="{{ $signal->leverageValue() }}" data-signal-tp1="{{ $signal->take_profit }}" data-signal-tp2="{{ $signal->take_profit_2 }}" data-signal-sl="{{ $signal->stop_loss }}" data-signal-live="{{ $liveFor($signal) }}" data-filter-ticker="{{ $signal->ticker }}" data-filter-ticker-key="{{ preg_replace('/[^a-z0-9]/', '', strtolower($signal->ticker)) }}" data-filter-side="{{ $signal->position_side }}" data-filter-status="{{ $signal->status }}" data-filter-access="open" class="flex flex-col gap-3 rounded-2xl border border-ink-700/60 bg-ink-900 p-3.5 transition-colors hover:border-gold-500/30 hover:bg-ink-800/40 sm:p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <x-crypto-icon :ticker="$signal->ticker" />
                                            <div class="min-w-0">
                                                <div class="truncate font-display text-xl leading-none text-ink-50">{{ $signal->ticker }}</div>
                                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                    <span class="inline-flex rounded-full border px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.14em] {{ $sideBadge($signal) }}">{{ $signal->sideLabel() }} {{ $signal->leverageValue() }}x</span>
                                                    <span class="inline-flex rounded-full border border-ink-600/60 bg-ink-800/70 px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.14em] text-ink-300">{{ $signal->termLabel() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="inline-flex shrink-0 rounded-full border px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.16em] {{ $toneClass($signal) }}">{{ $signal->statusLabel() }}</span>
                                    </div>

                                    <x-live-signal-chart variant="mini" :signal="$signal" :entry="$entryFor($signal)" :current="$liveFor($signal)" />

                                    <div class="grid grid-cols-2 gap-2">
                                        <div data-entry-level class="min-w-0 rounded-xl border border-gold-500/20 bg-ink-800/40 px-2.5 py-2">
                                            <div class="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-300">Entry (Live)</div>
                                            <div data-level-value class="mt-0.5 truncate font-mono text-[11px] text-gold-200">{{ $usd($liveRefFor($signal)) }}</div>
                                        </div>
                                        <div data-roi-tp1 class="min-w-0 rounded-xl border border-emerald-500/20 bg-ink-800/40 px-2.5 py-2">
                                            <div class="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-300">ROI TP1</div>
                                            <div data-level-value class="mt-0.5 truncate font-mono text-[11px] text-emerald-300">{{ $percent($roiTo($signal, $signal->take_profit)) }}</div>
                                        </div>
                                        <div data-roi-tp2 class="min-w-0 rounded-xl border {{ $signal->take_profit_2 ? 'border-emerald-500/20' : 'border-ink-700/60' }} bg-ink-800/40 px-2.5 py-2">
                                            <div class="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-300">ROI TP2</div>
                                            <div data-level-value class="mt-0.5 truncate font-mono text-[11px] {{ $signal->take_profit_2 ? 'text-emerald-300' : 'text-ink-300' }}">{{ $signal->take_profit_2 ? $percent($roiTo($signal, $signal->take_profit_2)) : '-' }}</div>
                                        </div>
                                        <div data-roi-sl class="min-w-0 rounded-xl border border-rose-500/20 bg-ink-800/40 px-2.5 py-2">
                                            <div class="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-300">Risk SL</div>
                                            <div data-level-value class="mt-0.5 truncate font-mono text-[11px] text-rose-300">{{ $percent($roiTo($signal, $signal->stop_loss)) }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-auto flex items-center justify-between gap-2 border-t border-ink-700/50 pt-3">
                                        <div class="flex items-center gap-1.5">
                                            <form method="POST" action="{{ route('signals.positions.store', $signal) }}">
                                                @csrf
                                                <input type="hidden" name="type" value="{{ \App\Models\UserSignalPosition::TYPE_POSITION }}">
                                                <button type="submit" title="Tandai sebagai posisi" class="inline-flex h-9 items-center gap-1.5 rounded-xl border px-2.5 font-mono text-[9px] uppercase tracking-[0.12em] transition-colors {{ $selection?->type === \App\Models\UserSignalPosition::TYPE_POSITION ? 'border-emerald-500/40 bg-emerald-500/15 text-emerald-100' : 'border-ink-600/70 text-ink-300 hover:border-emerald-500/40 hover:text-emerald-100' }}">
                                                    <x-icon name="target" class="h-3.5 w-3.5" />
                                                    Posisi
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('signals.positions.store', $signal) }}">
                                                @csrf
                                                <input type="hidden" name="type" value="{{ \App\Models\UserSignalPosition::TYPE_WATCHLIST }}">
                                                <button type="submit" title="Simpan ke pantauan" class="inline-flex h-9 items-center gap-1.5 rounded-xl border px-2.5 font-mono text-[9px] uppercase tracking-[0.12em] transition-colors {{ $selection?->type === \App\Models\UserSignalPosition::TYPE_WATCHLIST ? 'border-gold-500/40 bg-gold-500/15 text-gold-100' : 'border-ink-600/70 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">
                                                    <x-icon name="clipboard" class="h-3.5 w-3.5" />
                                                    Pantau
                                                </button>
                                            </form>
                                        </div>
                                        <button type="button" data-modal-open="signal-detail-{{ $signal->id }}" class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-gold-500/30 bg-gold-500/10 px-3 font-mono text-[9px] uppercase tracking-[0.12em] text-gold-100 transition-colors hover:border-gold-400/70 hover:bg-gold-500/15">
                                            Detail & P/L
                                            <x-icon name="arrow-up-right" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </article>
                            @else
                                <article data-carousel-item data-filter-ticker="{{ $signal->ticker }}" data-filter-ticker-key="{{ preg_replace('/[^a-z0-9]/', '', strtolower($signal->ticker)) }}" data-filter-side="{{ $signal->position_side }}" data-filter-status="{{ $signal->status }}" data-filter-access="locked" class="flex flex-col gap-3 rounded-2xl border border-ink-700/60 bg-ink-900 p-3.5 transition-colors hover:border-gold-500/30 hover:bg-ink-800/40 sm:p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <x-crypto-icon :ticker="$signal->ticker" />
                                            <div class="min-w-0">
                                                <div class="truncate font-display text-xl leading-none text-ink-50">{{ $signal->ticker }}</div>
                                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                    <span class="inline-flex rounded-full border px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.14em] {{ $sideBadge($signal) }}">{{ $signal->sideLabel() }} {{ $signal->leverageValue() }}x</span>
                                                    <span class="inline-flex rounded-full border border-ink-600/60 bg-ink-800/70 px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.14em] text-ink-300">{{ $signal->termLabel() }}</span>
                                                </div>
                                                <div class="mt-1.5 truncate font-mono text-[9px] uppercase tracking-[0.14em] text-ink-400">Publish {{ $signal->created_at->format('d M H:i') }} oleh {{ optional($signal->createdBy)->name }}</div>
                                            </div>
                                        </div>
                                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full border border-gold-500/30 bg-gold-500/10 px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.16em] text-gold-200">
                                            <x-icon name="lock" class="h-3 w-3" />
                                            Terkunci
                                        </span>
                                    </div>

                                    <div class="relative flex h-[220px] flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-gold-500/25 bg-[radial-gradient(circle_at_50%_30%,rgba(23,209,131,0.1),transparent_60%)]">
                                        <div class="pointer-events-none absolute inset-0 opacity-30">
                                            <svg class="h-full w-full" viewBox="0 0 360 200" preserveAspectRatio="none">
                                                <path d="M0,140 L40,120 L80,128 L120,90 L160,104 L200,68 L240,80 L280,46 L320,56 L360,40" fill="none" stroke="rgba(23,209,131,0.5)" stroke-width="1.5" stroke-dasharray="3 6" />
                                            </svg>
                                        </div>
                                        <span class="relative inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                            <x-icon name="lock" class="h-5 w-5" />
                                        </span>
                                        <div class="relative mt-3 font-display text-lg text-ink-50">Analisa Terkunci</div>
                                        <div class="relative mt-1 font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">Entry - TP - SL - ROI tersembunyi</div>
                                        <div class="relative mt-3 flex gap-2">
                                            @foreach (['TP1', 'TP2', 'SL'] as $label)
                                                <span class="inline-flex items-center gap-1 rounded-full border border-ink-700 bg-ink-900/80 px-2.5 py-1 font-mono text-[9px] text-ink-300">
                                                    {{ $label }} <span class="select-none blur-[5px]">*****</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-ink-700/50 pt-3">
                                        <div>
                                            <div class="font-mono text-[8px] uppercase tracking-[0.18em] text-ink-300">Biaya Buka</div>
                                            <div class="text-sm text-ink-50"><span class="font-display text-xl text-gold-200">{{ $cost }}</span> koin</div>
                                        </div>
                                        @if ($coinBalance < $cost)
                                            <a href="{{ route('checkout') }}"
                                               class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-gold-500/40 bg-gold-500/10 px-3 font-mono text-[9px] uppercase tracking-[0.12em] text-gold-100 transition-colors hover:border-gold-400/70 hover:bg-gold-500/20">
                                                <x-icon name="wallet" class="h-3.5 w-3.5" />
                                                Kurang {{ $cost - $coinBalance }} token - Top Up
                                            </a>
                                        @else
                                            <button type="button"
                                                    data-unlock-trigger
                                                    data-signal-id="{{ $signal->id }}"
                                                    data-signal-ticker="{{ $signal->ticker }}"
                                                    data-signal-side="{{ $signal->sideLabel() }}"
                                                    data-signal-leverage="{{ $signal->leverageValue() }}"
                                                    data-signal-cost="{{ $cost }}"
                                                    data-signal-action="{{ route('signals.unlock', $signal) }}"
                                                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-gold-500 px-3 font-mono text-[9px] font-semibold uppercase tracking-[0.12em] text-ink-900 transition-colors hover:bg-gold-300">
                                                <x-icon name="lock" class="h-3.5 w-3.5" />
                                                Buka Analisa
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @endif
                        @empty
                            <div class="rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center sm:col-span-2">
                                <div class="font-display text-2xl text-ink-50">Belum ada analisa trading.</div>
                                <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Analisa dari admin akan muncul di sini setelah dipublish.</p>
                            </div>
                        @endforelse
                    </div>

                    <div data-signal-filter-empty class="hidden rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
                        <div class="font-display text-2xl text-ink-50">Filter belum menemukan analisa.</div>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Coba ticker lain, ubah posisi, atau reset filter untuk melihat semua analisa.</p>
                    </div>
                </div>

                {{-- ===== RIGHT: sidebar ===== --}}
                <aside class="space-y-5">
                    {{-- Akses --}}
                    <div class="reveal rounded-2xl border {{ $hasSubscription ? 'border-emerald-500/25' : 'border-gold-500/25' }} bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border {{ $hasSubscription ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-gold-500/25 bg-gold-500/10 text-gold-200' }}">
                                <x-icon :name="$hasSubscription ? 'shield-check' : 'wallet'" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <div class="font-display text-lg leading-none text-ink-50">{{ $hasSubscription ? 'Akses Penuh Aktif' : 'Mode Per-Analisa' }}</div>
                                <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">{{ $hasSubscription ? 'Subscription' : 'Bayar pakai koin' }}</div>
                            </div>
                        </div>

                        @if ($hasSubscription)
                            <p class="mt-3 text-xs leading-relaxed text-ink-300">Aktif sampai <span class="text-emerald-200">{{ $subscriptionUntil->format('d M Y') }}</span> - semua {{ $signals->count() }} analisa aktif terbuka otomatis.</p>
                        @else
                            <div class="mt-3 flex items-end justify-between gap-3 rounded-xl border border-ink-700/60 bg-ink-800/40 p-3">
                                <div>
                                    <div class="font-mono text-[8px] uppercase tracking-[0.18em] text-ink-300">Saldo Koin</div>
                                    <div class="mt-1 font-display text-2xl leading-none text-gold-200">{{ number_format($coinBalance, 0, ',', '.') }}</div>
                                </div>
                                <div class="text-right font-mono text-[9px] uppercase tracking-[0.16em] text-ink-300">{{ $lockedCount }} terkunci<br>{{ $unlockedCount }} terbuka</div>
                            </div>
                            <a href="{{ route('checkout') }}" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-gold-300 to-gold-500 px-4 py-2.5 text-sm font-semibold text-ink-900 shadow-[0_10px_30px_-12px_rgba(23,209,131,0.8)] transition-all hover:shadow-[0_16px_44px_-14px_rgba(23,209,131,1)]">
                                <x-icon name="wallet" class="h-4 w-4" />
                                Beli Token
                            </a>
                        @endif

                        <a href="{{ route('user.archive') }}" class="mt-3 flex items-center justify-between gap-2 rounded-xl border border-ink-700/60 bg-ink-800/30 px-3 py-2.5 text-xs text-ink-200 transition-colors hover:border-gold-500/40 hover:text-gold-100">
                            <span class="inline-flex items-center gap-2"><x-icon name="database" class="h-3.5 w-3.5" /> Arsip TP/SL gratis</span>
                            <span class="font-mono text-[10px] text-gold-200">{{ $archiveCount }}</span>
                        </a>
                    </div>

                    {{-- Live session --}}
                    @if ($liveSession)
                        <a href="{{ route('user.live') }}" class="reveal group block rounded-2xl border {{ $liveSession->statusTone() === 'rose' ? 'border-rose-500/30' : 'border-gold-500/25' }} bg-ink-900/75 p-4 backdrop-blur-xl transition-all hover:border-gold-400/50 sm:p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border {{ $liveSession->statusTone() === 'rose' ? 'border-rose-500/30 bg-rose-500/10 text-rose-200' : 'border-gold-500/25 bg-gold-500/10 text-gold-200' }}">
                                        <x-icon name="video" class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <div class="truncate font-display text-base leading-tight text-ink-50">{{ $liveSession->title }}</div>
                                        <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.16em] text-ink-300">{{ $liveSession->scheduledAtLocal()->translatedFormat('D, d M - H:i') }} WIB - {{ $liveSession->providerLabel() }}</div>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.14em] {{ $liveSession->statusTone() === 'rose' ? 'border-rose-500/30 bg-rose-500/10 text-rose-200' : 'border-gold-500/30 bg-gold-500/10 text-gold-200' }}">
                                    @if ($liveSession->statusTone() === 'rose')
                                        <span class="inline-flex h-1.5 w-1.5 animate-pulse rounded-full bg-rose-400"></span>
                                    @endif
                                    {{ $liveSession->statusLabel() }}
                                </span>
                            </div>
                        </a>
                    @endif

                    {{-- Posisi Saya --}}
                    <div id="watchlist" class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                    <x-icon name="clipboard" class="h-5 w-5" />
                                </span>
                                <div>
                                    <h2 class="font-display text-lg leading-none text-ink-50">Posisi Saya</h2>
                                    <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">{{ $positionCount }} posisi - {{ $watchlistCount }} pantauan</div>
                                </div>
                            </div>
                        </div>

                        @forelse ($selectedRows->take(4) as $row)
                            <div class="flex items-center gap-3 border-b border-ink-700/50 py-2.5 last:border-0">
                                <x-crypto-icon :ticker="$row['ticker']" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="truncate font-display text-sm text-ink-50">{{ $row['ticker'] }}</span>
                                        <span class="inline-flex shrink-0 rounded-full border px-2 py-0.5 font-mono text-[8px] uppercase tracking-[0.14em] {{ $row['type_tone'] === 'emerald' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-gold-500/25 bg-gold-500/10 text-gold-200' }}">{{ $row['type_label'] }}</span>
                                    </div>
                                    <div class="mt-0.5 truncate font-mono text-[9px] uppercase tracking-[0.12em] text-ink-300">
                                        {{ $row['locked'] ? 'Terkunci - '.$row['cost'].' koin' : 'TP1 '.$row['tp1'].' - SL '.$row['sl'] }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="py-2 text-xs leading-relaxed text-ink-300">Belum ada posisi tersimpan. Tandai analisa sebagai <span class="text-emerald-200">Posisi</span> atau <span class="text-gold-200">Pantauan</span> dari kartu analisa.</p>
                        @endforelse

                        <a href="{{ route('user.positions') }}" class="mt-3 flex items-center justify-between gap-2 rounded-xl border border-ink-700/60 bg-ink-800/30 px-3 py-2.5 text-xs text-ink-200 transition-colors hover:border-gold-500/40 hover:text-gold-100">
                            <span>Buka Meja Posisi</span>
                            <x-icon name="arrow-up-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>

                    {{-- Aktivitas --}}
                    <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="bell" class="h-5 w-5" />
                            </span>
                            <h2 class="font-display text-lg leading-none text-ink-50">Aktivitas</h2>
                        </div>
                        <div class="space-y-3">
                            @forelse ($timeline as $item)
                                <div class="flex gap-3 border-b border-ink-700/50 pb-3 last:border-0 last:pb-0">
                                    <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-gold-400 shadow-[0_0_12px_rgba(23,209,131,0.8)]"></span>
                                    <div class="min-w-0">
                                        <p class="text-xs leading-relaxed text-ink-100">{{ $item }}</p>
                                        <div class="mt-0.5 font-mono text-[8px] uppercase tracking-[0.18em] text-ink-300">baru saja</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-xs leading-relaxed text-ink-300">Belum ada analisa dari admin untuk ditampilkan.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Edukasi --}}
                    <a href="{{ route('education.index') }}" class="reveal group block rounded-2xl border border-gold-500/20 bg-[linear-gradient(120deg,rgba(23,209,131,0.08),rgba(18,18,16,0.85)_60%)] p-4 backdrop-blur-xl transition-all hover:border-gold-400/45 sm:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                    <x-icon name="brain" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <div class="font-display text-lg leading-none text-ink-50">Pusat Edukasi</div>
                                    <p class="mt-1 text-xs leading-relaxed text-ink-300">Cara baca analisa, risk plan, dan eksekusi entry.</p>
                                </div>
                            </div>
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gold-500/30 bg-gold-500/10 text-gold-100 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5">
                                <x-icon name="arrow-up-right" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>

                    {{-- Risk plan --}}
                    <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="gauge" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-lg leading-none text-ink-50">Risk Plan</h2>
                                <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">Rules</div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <x-progress label="Daily Risk" value="54" />
                            <x-progress label="Trade Capacity" value="75" />
                            <x-progress label="Discipline Score" value="88" />
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        {{-- ===== Detail modals (per analisa terbuka) ===== --}}
        @foreach ($signals as $signal)
            @if ($canAccess($signal))
                @php $selection = $selectionFor($signal); @endphp
                <div data-modal="signal-detail-{{ $signal->id }}" hidden class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-ink-900/85 p-3 backdrop-blur-xl sm:p-6">
                    <div class="relative my-4 w-full max-w-3xl rounded-2xl border border-gold-500/20 bg-ink-900/95 p-4 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.9)] sm:my-8 sm:rounded-3xl sm:p-6">
                        <button type="button" data-modal-close aria-label="Tutup" class="absolute right-4 top-4 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border border-ink-600/70 bg-ink-900/80 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                            <x-icon name="x" class="h-4 w-4" />
                        </button>

                        <div class="flex items-start gap-3 pr-12">
                            <x-crypto-icon :ticker="$signal->ticker" size="lg" />
                            <div class="min-w-0">
                                <div class="font-display text-2xl leading-none text-ink-50 sm:text-3xl">{{ $signal->ticker }}</div>
                                <div class="mt-2 font-mono text-[9px] uppercase tracking-[0.18em] text-ink-300">Publish {{ $signal->created_at->format('d M H:i') }} oleh {{ optional($signal->createdBy)->name }}</div>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.16em] {{ $sideBadge($signal) }}">{{ $signal->sideLabel() }} {{ $signal->leverageValue() }}x</span>
                                    <span class="inline-flex rounded-full border border-ink-500/40 bg-ink-800/70 px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.16em] text-ink-200">{{ $signal->termLabel() }}</span>
                                    <span class="inline-flex rounded-full border px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.16em] {{ $toneClass($signal) }}">{{ $signal->statusLabel() }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.16em] text-emerald-200">
                                        <x-icon name="check-circle" class="h-3 w-3" />
                                        {{ $signal->status !== \App\Models\TradeSignal::STATUS_ACTIVE ? 'Arsip Gratis' : ($hasSubscription ? 'Subscriber' : 'Terbuka') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-live-signal-chart variant="panel" :signal="$signal" :entry="$entryFor($signal)" :current="$liveFor($signal)" />
                        </div>

                        <div data-live-roi-scope class="mt-4">
                            <div data-signal-row data-signal-ticker="{{ $signal->ticker }}" data-signal-side="{{ $signal->position_side }}" data-signal-leverage="{{ $signal->leverageValue() }}" data-signal-tp1="{{ $signal->take_profit }}" data-signal-tp2="{{ $signal->take_profit_2 }}" data-signal-sl="{{ $signal->stop_loss }}" data-signal-live="{{ $liveFor($signal) }}">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
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
                            </div>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-ink-300">
                            {{ $signal->status === \App\Models\TradeSignal::STATUS_ACTIVE ? 'Analisa masih berjalan. Estimasi ROI memakai harga live realtime sebagai entry.' : ($signal->status === \App\Models\TradeSignal::STATUS_HIT_SL ? 'Harga menyentuh stop loss. Evaluasi risk sebelum entry berikutnya.' : 'Harga sudah menyentuh target.') }}
                        </p>

                        <div class="mt-4 rounded-2xl border border-ink-700/60 bg-ink-800/45 p-4"
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
                                    <input type="number" step="any" min="0" inputmode="decimal" data-entry-input placeholder="contoh: {{ $liveRefFor($signal) }}" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-900 px-3 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
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

                        <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-ink-700/60 bg-ink-800/45 p-4 sm:flex-row sm:items-center sm:justify-between">
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
                    </div>
                </div>
            @endif
        @endforeach

        {{-- ===== Fullscreen chart modal ===== --}}
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

        {{-- ===== Registration success modal ===== --}}
        <div data-modal="registration-success" @if (! session('registration_success')) hidden @endif class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-ink-900/85 p-4 backdrop-blur-xl sm:items-center sm:p-6">
            <div class="relative w-full max-w-lg rounded-2xl border border-gold-500/20 bg-ink-900/95 p-5 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.9)] sm:rounded-3xl sm:p-7">
                <button type="button" data-modal-close aria-label="Tutup" class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                    <x-icon name="x" class="h-4 w-4" />
                </button>

                <div class="pr-10">
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                        <x-icon name="check-circle" class="h-7 w-7" />
                    </span>
                    <div class="mt-5 font-mono text-[10px] uppercase tracking-[0.26em] text-gold-300">Registrasi Berhasil</div>
                    <h2 class="mt-3 font-display text-3xl leading-tight text-ink-50">Selamat datang, {{ auth()->user()->name }}.</h2>
                    <p class="mt-3 text-sm leading-relaxed text-ink-300">
                        Akun kamu sudah aktif dan siap digunakan untuk melihat analisa trading, menyimpan pantauan, dan mengelola akses analisa dari dashboard.
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <a href="#signals" data-modal-close class="inline-flex items-center justify-center gap-2 rounded-xl bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-900 transition-colors hover:bg-gold-300">
                        <x-icon name="target" class="h-4 w-4" />
                        Lihat Analisa
                    </a>
                    <a href="{{ route('checkout') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-ink-700 px-5 py-3 text-sm text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">
                        <x-icon name="wallet" class="h-4 w-4" />
                        Beli Token
                    </a>
                </div>
            </div>
        </div>

        {{-- ===== Unlock confirm modal ===== --}}
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
