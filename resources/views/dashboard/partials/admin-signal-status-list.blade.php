<div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5" data-signal-carousel data-page-size="7">
    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                <x-icon name="shield-check" class="h-5 w-5" />
            </span>
            <div>
                <h2 class="font-display text-2xl leading-none text-ink-50">Status Trading</h2>
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
                Update
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4" data-carousel-list>
        @forelse ($signals as $signal)
            <article data-carousel-item class="grid grid-cols-1 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-900 transition-colors hover:bg-ink-800/45 lg:grid-cols-[320px_minmax(0,1fr)] xl:grid-cols-[360px_minmax(0,1fr)]">
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
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <x-level label="Take Profit 1" :value="$fmt($signal->take_profit)" tone="emerald" />
                        <x-level label="Take Profit 2" :value="$signal->take_profit_2 ? $fmt($signal->take_profit_2) : 'Tidak Ada'" :tone="$signal->take_profit_2 ? 'emerald' : 'ink'" />
                        <x-level label="Stop Loss" :value="$fmt($signal->stop_loss)" tone="rose" />
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
                        <x-level label="Entry (Live)" :value="$usd($liveRefFor($signal))" tone="gold" data-live-price-card data-live-price-ticker="{{ $signal->ticker }}" />
                        <x-level label="ROI TP1" :value="$percent($roiTo($signal, $signal->take_profit))" tone="emerald" />
                        <x-level label="ROI TP2" :value="$signal->take_profit_2 ? $percent($roiTo($signal, $signal->take_profit_2)) : '-'" tone="emerald" />
                        <x-level label="Risk SL" :value="$percent($roiTo($signal, $signal->stop_loss))" tone="rose" />
                    </div>

                    <div class="mt-5 flex flex-col items-start gap-3 rounded-2xl border border-ink-700/60 bg-ink-800/45 p-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-300">
                                <x-icon name="activity" class="h-4 w-4" />
                            </span>
                            <div>
                                <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Auto Status</div>
                                <div class="text-sm text-ink-50">
                                    {{ $signal->statusLabel() }}
                                    @if ($signal->auto_hit_at)
                                        <span class="ml-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">- terdeteksi {{ $signal->auto_hit_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if (($signal->unlocks_count ?? 0) > 0 || ($signal->user_positions_count ?? 0) > 0)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-gold-500/30 bg-gold-500/10 px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200" title="Sudah dipakai user">
                                <x-icon name="user" class="h-3 w-3" />
                                {{ ($signal->unlocks_count ?? 0) }} buka / {{ ($signal->user_positions_count ?? 0) }} pos
                            </span>
                        @endif
                    </div>

                    <details class="group mt-3 rounded-2xl border border-ink-700/60 bg-ink-800/30 [&[open]]:border-gold-500/30">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-2.5 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-colors hover:text-gold-200 [&::-webkit-details-marker]:hidden">
                            <span class="inline-flex items-center gap-2">
                                <x-icon name="edit" class="h-3 w-3" />
                                Edit Signal
                            </span>
                            <span class="font-mono text-[9px] text-ink-500 group-open:hidden">Klik untuk buka</span>
                            <span class="hidden font-mono text-[9px] text-gold-300 group-open:inline">Tutup</span>
                        </summary>
                        <form method="POST" action="{{ route('signals.update', $signal) }}" class="grid grid-cols-1 gap-3 border-t border-ink-700/60 p-4 sm:grid-cols-2 lg:grid-cols-3">
                            @csrf
                            @method('PATCH')

                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Ticker</span>
                                <input type="text" name="ticker" value="{{ $signal->ticker }}" required maxlength="30" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </label>
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Arah</span>
                                <select name="position_side" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    <option value="{{ \App\Models\TradeSignal::SIDE_LONG }}" @selected($signal->position_side === \App\Models\TradeSignal::SIDE_LONG)>Long</option>
                                    <option value="{{ \App\Models\TradeSignal::SIDE_SHORT }}" @selected($signal->position_side === \App\Models\TradeSignal::SIDE_SHORT)>Short</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Jangka Signal</span>
                                <select name="signal_term" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    <option value="{{ \App\Models\TradeSignal::TERM_SHORT }}" @selected($signal->signal_term === \App\Models\TradeSignal::TERM_SHORT)>Short Term</option>
                                    <option value="{{ \App\Models\TradeSignal::TERM_LONG }}" @selected($signal->signal_term === \App\Models\TradeSignal::TERM_LONG)>Long Term</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Leverage</span>
                                <input type="number" name="leverage" value="{{ $signal->leverageValue() }}" required min="1" max="125" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </label>
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Take Profit 1</span>
                                <input type="number" step="any" name="take_profit" value="{{ $signal->take_profit }}" required min="0" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </label>
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Take Profit 2</span>
                                <input type="number" step="any" name="take_profit_2" value="{{ $signal->take_profit_2 }}" min="0" placeholder="Kosongkan jika tidak ada" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </label>
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Stop Loss</span>
                                <input type="number" step="any" name="stop_loss" value="{{ $signal->stop_loss }}" required min="0" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </label>
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Biaya Buka (koin)</span>
                                <input type="number" name="coin_cost" value="{{ $signal->coinCostValue() }}" min="0" max="9999" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </label>
                            <label class="block sm:col-span-2">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Status</span>
                                <select name="status" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    <option value="{{ \App\Models\TradeSignal::STATUS_ACTIVE }}" @selected($signal->status === \App\Models\TradeSignal::STATUS_ACTIVE)>Aktif (signal masih berjalan)</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_TP }}" @selected($signal->status === \App\Models\TradeSignal::STATUS_HIT_TP)>Kena TP1</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_TP2 }}" @selected($signal->status === \App\Models\TradeSignal::STATUS_HIT_TP2)>Kena TP2</option>
                                    <option value="{{ \App\Models\TradeSignal::STATUS_HIT_SL }}" @selected($signal->status === \App\Models\TradeSignal::STATUS_HIT_SL)>Kena SL</option>
                                </select>
                                <span class="mt-1.5 block text-[11px] text-ink-400">Set ke "Aktif" untuk reset auto-detect kalau salah ke-trigger.</span>
                            </label>

                            <div class="flex flex-col gap-2 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between lg:col-span-3">
                                <p class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-400">
                                    @if (($signal->unlocks_count ?? 0) > 0)
                                        Hati-hati: {{ $signal->unlocks_count }} user sudah buka analisa ini.
                                    @endif
                                </p>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-5 py-2.5 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-100 transition-all hover:border-gold-400/70 hover:bg-gold-500/15">
                                    <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </details>

                    <form method="POST" action="{{ route('signals.destroy', $signal) }}" class="mt-3 flex items-center justify-end" onsubmit="return confirm('Hapus signal {{ $signal->ticker }} permanen?@if(($signal->unlocks_count ?? 0) > 0) {{ $signal->unlocks_count }} user sudah membuka analisa ini dan akan kehilangan akses.@endif Tidak bisa diundo.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-rose-500/30 bg-rose-500/5 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-rose-200 transition-all hover:border-rose-400/60 hover:bg-rose-500/15 hover:text-rose-100">
                            <x-icon name="trash" class="h-3.5 w-3.5" />
                            Hapus Signal
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
                <div class="font-display text-2xl text-ink-50">Belum ada signal trading.</div>
                <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Upload foto analisa, ticker, TP, dan SL dari form admin.</p>
            </div>
        @endforelse
    </div>
</div>
