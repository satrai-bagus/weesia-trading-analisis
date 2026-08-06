{{-- Modal input harga entry saat memasang analisa sebagai Posisi.
     Dipicu tombol [data-position-entry-trigger]; di-wire di setupPositionEntryModal(). --}}
<div data-modal="position-entry" hidden class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-ink-900/85 p-4 backdrop-blur-xl sm:items-center sm:p-6">
    <div class="relative w-full max-w-md rounded-2xl border border-emerald-500/20 bg-ink-900/95 p-4 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.9)] sm:rounded-3xl sm:p-7">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                    <x-icon name="target" class="h-5 w-5" />
                </span>
                <div>
                    <div class="font-display text-xl leading-none text-ink-50">Pasang Posisi</div>
                    <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Catat harga entry kamu</div>
                </div>
            </div>
            <button type="button" data-modal-close aria-label="Tutup" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                <x-icon name="x" class="h-4 w-4" />
            </button>
        </div>

        <div class="mt-5 rounded-2xl border border-ink-700/60 bg-ink-800/50 p-4">
            <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Analisa</div>
            <div class="mt-1 flex items-center gap-2">
                <div data-position-entry-ticker class="font-display text-2xl text-ink-50">-</div>
                <span data-position-entry-side class="rounded-full border border-emerald-500/25 bg-emerald-500/10 px-2 py-0.5 font-mono text-[10px] uppercase tracking-[0.18em] text-emerald-200">-</span>
            </div>
        </div>

        <form method="POST" data-position-entry-form class="mt-4">
            @csrf
            <input type="hidden" name="type" value="{{ \App\Models\UserSignalPosition::TYPE_POSITION }}">
            <label class="block">
                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-emerald-300">Harga Entry Kamu (USD)</span>
                <input name="entry_price" type="number" step="any" min="0.00000001" max="999999999999" inputmode="decimal" required
                       data-position-entry-input
                       class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none transition-colors focus:border-emerald-400/50">
            </label>
            <p class="mt-2 text-[11px] leading-relaxed text-ink-400">Harga saat kamu benar-benar masuk pasar. Winrate dan kurva performa dihitung dari harga ini ke harga penutupan analisa - tanpa entry, posisinya tidak dihitung. Masih bisa diubah selama analisa berjalan.</p>
            <button type="button" data-position-entry-live class="mt-3 hidden items-center gap-2 rounded-full border border-ink-600/70 bg-ink-800/60 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-200 transition-all hover:border-emerald-500/50 hover:text-emerald-100">
                <x-icon name="refresh" class="h-3 w-3" />
                Pakai harga live: $<span data-position-entry-live-value>-</span>
            </button>

            <div class="mt-5 flex flex-col-reverse items-stretch gap-3 border-t border-ink-700/60 pt-5 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" data-modal-close class="rounded-xl border border-ink-700 px-5 py-2.5 text-sm text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">Batal</button>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-400/40 bg-emerald-500/15 px-5 py-2.5 text-sm font-semibold text-emerald-100 transition-colors hover:border-emerald-300/70 hover:bg-emerald-500/25">
                    <x-icon name="target" class="h-4 w-4" />
                    Pasang Posisi
                </button>
            </div>
        </form>
    </div>
</div>
