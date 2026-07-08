@php
    $archivedSignals = $archivedSignals ?? collect();
    $archiveTickers = $archiveTickers ?? collect();
    $archiveTpCount = $archivedSignals->where('status', \App\Models\TradeSignal::STATUS_HIT_TP)->count();
    $archiveTp2Count = $archivedSignals->where('status', \App\Models\TradeSignal::STATUS_HIT_TP2)->count();
    $archiveSlCount = $archivedSignals->where('status', \App\Models\TradeSignal::STATUS_HIT_SL)->count();
    $tickerKey = fn ($ticker) => preg_replace('/[^a-z0-9]/', '', strtolower($ticker));
    $closeFor = fn ($signal) => $signal->closePrice() ?: ($signal->entry_price ?: ($priceMap[$signal->ticker] ?? null));
    $roiClose = fn ($signal) => $signal->closePrice() ? $signal->leveragedMoveTo((float) $signal->closePrice(), (float) $signal->entry_price) : null;
    $archiveChipBase = 'inline-flex items-center gap-2 rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-colors';
    $archiveChipIdle = 'border-ink-700/70 bg-ink-800/50 text-ink-300';
    $archiveChipActive = 'border-gold-500/50 bg-gold-500/15 text-gold-100';
@endphp

<div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5" data-admin-archive-root>
    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                <x-icon name="database" class="h-5 w-5" />
            </span>
            <div>
                <h2 class="font-display text-2xl leading-none text-ink-50">Arsip Signal</h2>
                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Kena TP / SL - {{ $archivedSignals->count() }} tersimpan</div>
            </div>
        </div>
        @if ($archivedSignals->isNotEmpty())
            <div class="flex items-center gap-2">
                <span data-admin-archive-counter class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">{{ $archivedSignals->count() }} dari {{ $archivedSignals->count() }} arsip</span>
                <button type="button" data-admin-archive-clear class="inline-flex h-9 items-center gap-2 rounded-full border border-ink-700/70 bg-ink-900/60 px-3 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">
                    <x-icon name="refresh" class="h-3 w-3" />
                    Reset
                </button>
            </div>
        @endif
    </div>

    @if ($archivedSignals->isEmpty())
        <div class="rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
            <div class="font-display text-2xl text-ink-50">Arsip masih kosong.</div>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Signal yang sudah kena Take Profit atau Stop Loss otomatis pindah ke sini, jadi daftar signal aktif tetap rapi.</p>
        </div>
    @else
        <div class="mb-4 space-y-3">
            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filter status arsip signal">
                <button type="button" data-admin-archive-status-filter="all" aria-pressed="true" class="{{ $archiveChipBase }} {{ $archiveChipActive }}">
                    <x-icon name="activity" class="h-3.5 w-3.5" />
                    Semua
                    <span class="rounded-full bg-ink-700/60 px-1.5 py-0.5 text-[9px]">{{ $archivedSignals->count() }}</span>
                </button>
                <button type="button" data-admin-archive-status-filter="{{ \App\Models\TradeSignal::STATUS_HIT_TP }}" aria-pressed="false" class="{{ $archiveChipBase }} {{ $archiveChipIdle }} hover:border-emerald-500/40 hover:text-emerald-100">
                    <x-icon name="check-circle" class="h-3.5 w-3.5" />
                    Kena TP1
                    <span class="rounded-full bg-ink-700/60 px-1.5 py-0.5 text-[9px]">{{ $archiveTpCount }}</span>
                </button>
                <button type="button" data-admin-archive-status-filter="{{ \App\Models\TradeSignal::STATUS_HIT_TP2 }}" aria-pressed="false" class="{{ $archiveChipBase }} {{ $archiveChipIdle }} hover:border-emerald-500/40 hover:text-emerald-100">
                    <x-icon name="check-circle" class="h-3.5 w-3.5" />
                    Kena TP2
                    <span class="rounded-full bg-ink-700/60 px-1.5 py-0.5 text-[9px]">{{ $archiveTp2Count }}</span>
                </button>
                <button type="button" data-admin-archive-status-filter="{{ \App\Models\TradeSignal::STATUS_HIT_SL }}" aria-pressed="false" class="{{ $archiveChipBase }} {{ $archiveChipIdle }} hover:border-rose-500/40 hover:text-rose-100">
                    <x-icon name="x" class="h-3.5 w-3.5" />
                    Kena SL
                    <span class="rounded-full bg-ink-700/60 px-1.5 py-0.5 text-[9px]">{{ $archiveSlCount }}</span>
                </button>
            </div>

            @if ($archiveTickers->isNotEmpty())
                <div class="flex items-center gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="tablist" aria-label="Filter crypto arsip signal">
                    <button type="button" data-admin-archive-ticker-filter="all" aria-pressed="true" class="shrink-0 rounded-full border border-gold-500/50 bg-gold-500/15 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-100">Semua</button>
                    @foreach ($archiveTickers as $ticker)
                        <button type="button" data-admin-archive-ticker-filter="{{ $tickerKey($ticker) }}" aria-pressed="false" class="shrink-0 rounded-full border border-ink-700/70 bg-ink-800/50 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 transition-colors hover:border-gold-500/40 hover:text-gold-100">{{ $ticker }}</button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-4" data-admin-archive-grid>
            @foreach ($archivedSignals as $signal)
                @php
                    $statusGroup = $signal->status === \App\Models\TradeSignal::STATUS_HIT_SL ? 'sl' : 'tp';
                    $roiActual = $roiClose($signal);
                @endphp
                <article
                    data-admin-archive-item
                    data-filter-status="{{ $signal->status }}"
                    data-filter-ticker="{{ $tickerKey($signal->ticker) }}"
                    class="grid grid-cols-1 overflow-hidden rounded-2xl border {{ $statusGroup === 'tp' ? 'border-emerald-500/25 hover:border-emerald-500/45' : 'border-rose-500/25 hover:border-rose-500/45' }} bg-ink-900 transition-colors lg:grid-cols-[320px_minmax(0,1fr)] xl:grid-cols-[360px_minmax(0,1fr)]">
                    <x-live-signal-chart :signal="$signal" :entry="$entryFor($signal)" :current="$closeFor($signal)" :frozen="true" />
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="font-display text-2xl leading-none text-ink-50 sm:text-3xl">{{ $signal->ticker }}</div>
                                <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.16em] text-ink-300 sm:tracking-[0.22em]">
                                    Publish {{ $signal->created_at->format('d M H:i') }} oleh {{ optional($signal->createdBy)->name }}
                                    <span class="text-gold-300">/ Closed {{ ($signal->auto_hit_at ?? $signal->updated_at)->format('d M H:i') }}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex w-fit rounded-full border border-gold-500/25 bg-gold-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-gold-200">{{ $signal->sideLabel() }} {{ $signal->leverageValue() }}x</span>
                                <span class="inline-flex w-fit rounded-full border border-ink-500/40 bg-ink-800/70 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-200">{{ $signal->termLabel() }}</span>
                                <span class="inline-flex w-fit rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] {{ $toneClass($signal) }}">{{ $signal->statusLabel() }}</span>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <x-level label="Take Profit 1" :value="$fmt($signal->take_profit)" tone="emerald" />
                            <x-level label="Take Profit 2" :value="$signal->take_profit_2 ? $fmt($signal->take_profit_2) : 'Tidak Ada'" :tone="$signal->take_profit_2 ? 'emerald' : 'ink'" />
                            <x-level label="Stop Loss" :value="$fmt($signal->stop_loss)" tone="rose" />
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-3">
                            <x-level label="Entry Publish" :value="$usd($signal->entry_price)" tone="gold" />
                            <x-level label="Harga Akhir" :value="$usd($signal->closePrice())" :tone="$statusGroup === 'tp' ? 'emerald' : 'rose'" />
                            <x-level label="Hasil Aktual" :value="$percent($roiActual)" :tone="$statusGroup === 'tp' ? 'emerald' : 'rose'" />
                        </div>

                        <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-ink-700/60 bg-ink-800/45 p-3 sm:flex-row sm:items-center sm:justify-between">
                            <form method="POST" action="{{ route('signals.status', $signal) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    <option value="{{ \App\Models\TradeSignal::STATUS_ACTIVE }}">Aktifkan lagi (kembali ke daftar aktif)</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_TP }}" @selected($signal->status === \App\Models\TradeSignal::STATUS_HIT_TP)>Kena TP1</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_TP2 }}" @selected($signal->status === \App\Models\TradeSignal::STATUS_HIT_TP2)>Kena TP2</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_SL }}" @selected($signal->status === \App\Models\TradeSignal::STATUS_HIT_SL)>Kena SL</option>
                                </select>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-100 transition-all hover:border-gold-400/70 hover:bg-gold-500/15">
                                    <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                    Terapkan
                                </button>
                                <span class="basis-full font-mono text-[9px] uppercase tracking-[0.18em] text-ink-400">Koreksi status kalau auto-detect salah trigger.</span>
                            </form>
                            <div class="flex items-center gap-2">
                                @if (($signal->unlocks_count ?? 0) > 0 || ($signal->user_positions_count ?? 0) > 0)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-gold-500/30 bg-gold-500/10 px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200" title="Sudah dipakai user">
                                        <x-icon name="user" class="h-3 w-3" />
                                        {{ ($signal->unlocks_count ?? 0) }} buka / {{ ($signal->user_positions_count ?? 0) }} pos
                                    </span>
                                @endif
                                <form method="POST" action="{{ route('signals.destroy', $signal) }}" onsubmit="return confirm('Hapus signal {{ $signal->ticker }} permanen?@if(($signal->unlocks_count ?? 0) > 0) {{ $signal->unlocks_count }} user sudah membuka analisa ini dan akan kehilangan akses.@endif Tidak bisa diundo.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-rose-500/30 bg-rose-500/5 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-rose-200 transition-all hover:border-rose-400/60 hover:bg-rose-500/15 hover:text-rose-100">
                                        <x-icon name="trash" class="h-3.5 w-3.5" />
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div data-admin-archive-empty class="hidden rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
            <div class="font-display text-2xl text-ink-50">Filter belum menemukan arsip.</div>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Coba crypto lain atau ubah filter Kena TP1 / TP2 / SL.</p>
        </div>

        <script>
            (() => {
                const root = document.querySelector('[data-admin-archive-root]');
                if (!root) return;
                const grid = root.querySelector('[data-admin-archive-grid]');
                if (!grid) return;
                const counter = root.querySelector('[data-admin-archive-counter]');
                const empty = root.querySelector('[data-admin-archive-empty]');
                const items = Array.from(grid.querySelectorAll('[data-admin-archive-item]'));
                const total = items.length;
                let statusFilter = 'all';
                let tickerFilter = 'all';

                const apply = () => {
                    let visible = 0;
                    items.forEach((item) => {
                        const matchesStatus = statusFilter === 'all' || item.dataset.filterStatus === statusFilter;
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
                    root.querySelectorAll(selector).forEach((btn) => {
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

                root.querySelectorAll('[data-admin-archive-status-filter]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        statusFilter = btn.getAttribute('data-admin-archive-status-filter');
                        setActive('[data-admin-archive-status-filter]', statusFilter, 'data-admin-archive-status-filter');
                        apply();
                    });
                });

                root.querySelectorAll('[data-admin-archive-ticker-filter]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        tickerFilter = btn.getAttribute('data-admin-archive-ticker-filter');
                        setActive('[data-admin-archive-ticker-filter]', tickerFilter, 'data-admin-archive-ticker-filter');
                        apply();
                    });
                });

                const clearBtn = root.querySelector('[data-admin-archive-clear]');
                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        statusFilter = 'all';
                        tickerFilter = 'all';
                        setActive('[data-admin-archive-status-filter]', 'all', 'data-admin-archive-status-filter');
                        setActive('[data-admin-archive-ticker-filter]', 'all', 'data-admin-archive-ticker-filter');
                        apply();
                    });
                }

                apply();
            })();
        </script>
    @endif
</div>
