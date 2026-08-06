@php
    $settled = $settledSelections ?? collect();
    $settledCount = $settled->count();
    $settledFmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 8, '.', ','), '0'), '.');
    $settledPercent = fn ($value) => $value === null
        ? '-'
        : ($value > 0 ? '+' : '').rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.').'%';
@endphp

@if ($settledCount > 0)
    <div class="reveal overflow-hidden rounded-2xl border border-gold-500/25 bg-[linear-gradient(135deg,rgba(23,209,131,0.10),rgba(18,18,16,0.92)_62%)]"
         role="region"
         aria-label="Hasil posisi yang sudah selesai">
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
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Hasil posisi</span>
                        <span class="rounded-full border border-gold-500/30 bg-gold-500/10 px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] text-gold-200">{{ $settledCount }} selesai</span>
                    </div>
                    <h2 class="mt-2 font-display text-xl leading-snug text-ink-50 sm:text-2xl">{{ $settledCount }} posisi kamu sudah menyentuh TP atau SL.</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-200">Posisi ini sudah tidak berjalan lagi, jadi dikeluarkan dari daftar di bawah. Tandai sudah dibaca untuk membersihkannya dari meja kamu - detail lengkapnya tetap bisa dibuka kapan saja di Arsip Analisa.</p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('user.archive') }}" class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 bg-ink-800/60 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                    <x-icon name="database" class="h-3.5 w-3.5" />
                    Lihat arsip
                </a>
                <form method="POST" action="{{ route('positions.settled.clear') }}" data-settled-clear-all>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-gold-500/30 bg-gold-500/10 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200 transition-all hover:border-gold-400/70 hover:text-gold-100">
                        <x-icon name="check-circle" class="h-3.5 w-3.5" />
                        Tandai semua dibaca
                    </button>
                </form>
            </div>
        </div>

        <div class="grid gap-3 p-4 sm:p-5 xl:grid-cols-2">
            @foreach ($settled as $selection)
                @php
                    $settledSignal = $selection->tradeSignal;
                    $settledRoi = $settledSignal->resultRoi();
                    $settledTone = $settledSignal->statusTone() === 'rose' ? 'rose' : 'emerald';
                    $settledMoment = $settledSignal->settledAt();

                    // Angka utama = ROI pribadi (entry yang diinput user) kalau ada;
                    // ROI analisa hanya jadi referensi. Tiga keadaan: profit, rugi,
                    // atau tidak bisa dihitung (jangan pernah menampilkan "rugi"
                    // hanya karena datanya tidak lengkap). Baris Pantauan tidak pernah
                    // memakai label "posisi kamu" walau entry lamanya masih tersimpan.
                    $personalRoi = $selection->personalRoi();
                    $isPositionType = $selection->type === \App\Models\UserSignalPosition::TYPE_POSITION;
                    $mainIsPersonal = $isPositionType && $personalRoi !== null;
                    $mainRoi = $mainIsPersonal ? $personalRoi : $settledRoi;
                    $mainHasRoi = $mainRoi !== null;
                    $mainWin = $mainHasRoi && $mainRoi >= 0;
                    $mainLabelBase = $mainIsPersonal ? 'Hasil posisi kamu' : 'Hasil analisa';
                    $mainLabel = ! $mainHasRoi ? $mainLabelBase : $mainLabelBase.($mainWin ? ' - profit' : ' - rugi');

                    // Warna kartu (border + titik) mengikuti angka utama yang
                    // ditampilkan, bukan status analisa - badge status tetap
                    // memakai tone analisa. Tanpa ini, posisi pribadi yang rugi
                    // di analisa yang kena TP tampil berbingkai hijau.
                    $cardTone = ! $mainHasRoi ? 'ink' : ($mainWin ? 'emerald' : 'rose');
                    $cardBorder = ['ink' => 'border-ink-700/60', 'emerald' => 'border-emerald-500/25', 'rose' => 'border-rose-500/25'][$cardTone];
                    $cardDot = [
                        'ink' => 'bg-ink-400',
                        'emerald' => 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.7)]',
                        'rose' => 'bg-rose-400 shadow-[0_0_10px_rgba(244,63,94,0.7)]',
                    ][$cardTone];
                @endphp

                <article data-settled-item
                         data-signal-id="{{ $settledSignal->id }}"
                         data-signal-side="{{ $settledSignal->position_side }}"
                         data-signal-leverage="{{ $settledSignal->leverageValue() }}"
                         data-signal-close="{{ $settledSignal->closePrice() }}"
                         class="flex flex-col gap-4 rounded-2xl border {{ $cardBorder }} bg-ink-900/70 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $cardDot }}"></span>
                            <div class="min-w-0">
                                <div class="truncate font-display text-xl text-ink-50">{{ $settledSignal->ticker }}</div>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">
                                    {{ $settledSignal->sideLabel() }} {{ $settledSignal->leverageValue() }}x
                                    @if ($settledMoment)
                                        - {{ $settledMoment->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border px-2.5 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] {{ $settledTone === 'rose' ? 'border-rose-500/30 bg-rose-500/10 text-rose-200' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' }}">{{ $settledSignal->statusLabel() }}</span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                        <div class="min-w-0">
                            <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-400">{{ $mainLabel }}</div>
                            <div class="mt-1 font-display text-3xl leading-none {{ ! $mainHasRoi ? 'text-ink-200' : ($mainWin ? 'text-emerald-300' : 'text-rose-300') }}">{{ $settledPercent($mainRoi) }}</div>
                            <div class="mt-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">
                                @if ($mainIsPersonal)
                                    Entry kamu ${{ $settledFmt($selection->entry_price) }} &rarr; tutup ${{ $settledFmt($settledSignal->closePrice()) }} - ROI analisa {{ $settledPercent($settledRoi) }}
                                @elseif ($settledSignal->entry_price && $settledSignal->closePrice())
                                    Entry analisa ${{ $settledFmt($settledSignal->entry_price) }} &rarr; tutup ${{ $settledFmt($settledSignal->closePrice()) }}@if ($isPositionType) - entry pribadi tidak diisi, tidak dihitung di performa @endif
                                @elseif (! $settledSignal->entry_price)
                                    Entry analisa tidak dicatat, ROI tidak bisa dihitung
                                @else
                                    Harga penutupan tidak tersedia, ROI tidak bisa dihitung
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('positions.settled.dismiss', $settledSignal) }}" class="shrink-0" data-settled-dismiss>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gold-500/30 bg-gold-500/10 px-4 py-2.5 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200 transition-all hover:border-gold-400/70 hover:text-gold-100 sm:w-auto">
                                <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                Tandai sudah dibaca
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endif
