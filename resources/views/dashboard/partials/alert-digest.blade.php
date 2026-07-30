@php
    $digest = $alerts ?? ['unread_count' => 0, 'items' => []];
    $digestUnread = collect($digest['items'] ?? [])
        ->filter(fn ($item) => $item['is_unread'] ?? false)
        ->values();
    $digestItems = $digestUnread->take(6);
    $digestTotal = max((int) ($digest['unread_count'] ?? 0), $digestUnread->count());
    $digestRest = $digestTotal - $digestItems->count();
    $digestTone = fn (string $tone) => match ($tone) {
        'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200',
        'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-200',
        default => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
    };
    $digestDot = fn (string $tone) => match ($tone) {
        'emerald' => 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.7)]',
        'rose' => 'bg-rose-400 shadow-[0_0_10px_rgba(244,63,94,0.7)]',
        default => 'bg-gold-400',
    };
@endphp

<div data-alert-digest
     class="reveal mt-6 overflow-hidden rounded-2xl border border-gold-500/25 bg-[linear-gradient(135deg,rgba(23,209,131,0.10),rgba(18,18,16,0.92)_62%)] {{ $digestTotal ? '' : 'hidden' }}"
     role="region"
     aria-label="Notifikasi analisa yang belum dibaca">
    <div class="flex flex-col gap-4 border-b border-ink-700/50 p-4 sm:p-5 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex min-w-0 items-start gap-3">
            <span class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                <x-icon name="bell" class="h-4 w-4" />
                <span class="absolute -right-1 -top-1 inline-flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-gold-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-gold-400"></span>
                </span>
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Belum dibaca</span>
                    <span data-alert-digest-count class="rounded-full border border-gold-500/30 bg-gold-500/10 px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] text-gold-200">{{ $digestTotal ? $digestTotal.' baru' : '' }}</span>
                </div>
                <h2 data-alert-digest-headline class="mt-2 font-display text-xl leading-snug text-ink-50 sm:text-2xl">{{ $digestTotal ? $digestTotal.' analisa menyentuh levelnya dan belum kamu baca.' : '' }}</h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-200">Harga tiap analisa dipantau otomatis setiap 30 detik. Begitu menyentuh target profit (TP) atau batas invalidasi (SL), analisanya ditutup di level itu dan hasilnya dicatat apa adanya - kena target maupun kena stop - lalu pindah ke arsip terbuka.</p>
            </div>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <a href="{{ route('user.archive') }}" class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 bg-ink-800/60 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                <x-icon name="database" class="h-3.5 w-3.5" />
                Lihat arsip
            </a>
            <button type="button" data-alert-digest-clear class="inline-flex items-center gap-2 rounded-full border border-gold-500/30 bg-gold-500/10 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200 transition-all hover:border-gold-400/70 hover:text-gold-100">
                <x-icon name="check-circle" class="h-3.5 w-3.5" />
                Tandai dibaca
            </button>
        </div>
    </div>

    <ul data-alert-digest-list class="grid gap-2 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3">
        @foreach ($digestItems as $item)
            <li data-alert-digest-item="{{ $item['id'] }}" class="flex items-center justify-between gap-3 rounded-xl border border-ink-700/60 bg-ink-900/60 px-3 py-2.5">
                <span class="flex min-w-0 items-center gap-2.5">
                    <span class="h-2 w-2 shrink-0 rounded-full {{ $digestDot($item['tone']) }}"></span>
                    <span class="min-w-0">
                        <span class="block truncate font-display text-sm text-ink-50">{{ $item['ticker'] }}</span>
                        <span class="mt-0.5 block truncate font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">{{ $item['side'] }} {{ $item['leverage'] }}x - {{ $item['auto_hit_human'] }}</span>
                    </span>
                </span>
                <span class="shrink-0 rounded-full border px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] {{ $digestTone($item['tone']) }}">{{ $item['status_label'] }}</span>
            </li>
        @endforeach
        @if ($digestRest > 0)
            <li data-alert-digest-rest class="flex items-center justify-center rounded-xl border border-dashed border-ink-700/60 px-3 py-2.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">+{{ $digestRest }} lainnya di lonceng</li>
        @endif
    </ul>

    <div class="flex flex-wrap gap-x-5 gap-y-1.5 border-t border-ink-700/50 px-4 py-3 font-mono text-[9px] uppercase tracking-[0.18em] text-ink-400 sm:px-5">
        <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Kena TP - harga menyentuh target</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span> Kena SL - harga menyentuh invalidasi</span>
    </div>
</div>
