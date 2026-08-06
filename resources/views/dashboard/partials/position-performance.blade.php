@php
    $perf = $performance ?? null;
    $perfPercent = fn ($value) => $value === null
        ? '-'
        : ($value > 0 ? '+' : '').rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.').'%';
    $perfProfit = ($perf['total_roi'] ?? 0) >= 0;
@endphp

@if ($perf && $perf['counted'] > 0)
    <div data-position-performance
         class="reveal overflow-hidden rounded-3xl border border-ink-700/60 bg-gradient-to-b from-ink-900/95 to-ink-900/70 backdrop-blur-xl"
         role="region"
         aria-label="Performa posisi kamu">
        {{-- Header + verdict --}}
        <div class="flex flex-col gap-4 border-b border-ink-700/50 p-5 sm:p-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <div class="inline-flex items-center gap-3">
                    <span class="h-px w-10 bg-gold-500/60"></span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Performa Posisi</span>
                </div>
                <h2 data-perf-headline class="mt-3 font-display text-2xl leading-snug sm:text-3xl {{ $perfProfit ? 'text-emerald-200' : 'text-rose-200' }}">
                    {{ $perfProfit ? 'Sejauh ini posisi kamu untung' : 'Sejauh ini posisi kamu rugi' }} {{ $perfPercent($perf['total_roi']) }} dari {{ $perf['counted'] }} posisi selesai.
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-300">Dihitung dari <span class="text-emerald-200">harga entry yang kamu input</span> saat memasang Posisi, ke harga penutupan analisa - sudah termasuk leverage, asumsi ukuran tiap posisi sama. Pantauan dan posisi tanpa harga entry tidak ikut dihitung.</p>
            </div>

            {{-- Filter rentang waktu: satu baris, men-scope tiles + grafik + tabel di bawahnya --}}
            <div class="flex shrink-0 flex-wrap items-center gap-2" role="group" aria-label="Rentang waktu performa">
                @foreach ([['30', '30 Hari'], ['90', '90 Hari'], ['365', '1 Tahun'], ['all', 'Semua']] as [$rangeKey, $rangeLabel])
                    <button type="button"
                            data-perf-range="{{ $rangeKey }}"
                            aria-pressed="{{ $rangeKey === 'all' ? 'true' : 'false' }}"
                            class="rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-colors {{ $rangeKey === 'all' ? 'border-gold-500/50 bg-gold-500/15 text-gold-100' : 'border-ink-700/70 bg-ink-800/50 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">{{ $rangeLabel }}</button>
                @endforeach
            </div>
        </div>

        <div class="space-y-5 p-5 sm:p-6">
            {{-- KPI tiles --}}
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4">
                    <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-400">Winrate</div>
                    <div data-perf-winrate class="mt-2 font-display text-3xl leading-none text-ink-50">{{ $perf['winrate'] === null ? '-' : rtrim(rtrim(number_format($perf['winrate'], 1, '.', ','), '0'), '.').'%' }}</div>
                    <div data-perf-winrate-sub class="mt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">{{ $perf['wins'] }} menang &middot; {{ $perf['losses'] }} rugi</div>
                </div>
                <div class="rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4">
                    <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-400">Akumulasi ROI</div>
                    <div data-perf-total class="mt-2 font-display text-3xl leading-none {{ $perfProfit ? 'text-emerald-300' : 'text-rose-300' }}">{{ $perfPercent($perf['total_roi']) }}</div>
                    <div data-perf-total-sub class="mt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">{{ $perf['counted'] }} posisi selesai</div>
                </div>
                <div class="rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4">
                    <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-400">Rata-rata / Posisi</div>
                    <div data-perf-avg class="mt-2 font-display text-3xl leading-none {{ ($perf['avg_roi'] ?? 0) >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">{{ $perfPercent($perf['avg_roi']) }}</div>
                    <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">ROI leverage per entri</div>
                </div>
                <div class="rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4">
                    <div class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-400">Profit Factor</div>
                    <div data-perf-pf class="mt-2 font-display text-3xl leading-none text-ink-50">{{ $perf['profit_factor'] !== null ? number_format($perf['profit_factor'], 2, '.', ',') : ($perf['wins'] > 0 ? '∞' : '-') }}</div>
                    <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">Profit kotor &divide; rugi kotor</div>
                </div>
            </div>

            {{-- Best / worst --}}
            <div data-perf-extremes class="flex flex-wrap gap-2 {{ $perf['best'] ? '' : 'hidden' }}">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/25 bg-emerald-500/10 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-emerald-200">
                    <x-icon name="trending-up" class="h-3 w-3" />
                    Terbaik <span data-perf-best>{{ $perf['best'] ? $perf['best']['ticker'].' '.$perfPercent($perf['best']['roi']) : '-' }}</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-rose-500/25 bg-rose-500/10 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-rose-200">
                    <x-icon name="activity" class="h-3 w-3" />
                    Terburuk <span data-perf-worst>{{ $perf['worst'] ? $perf['worst']['ticker'].' '.$perfPercent($perf['worst']['roi']) : '-' }}</span>
                </span>
            </div>

            {{-- Grafik akumulasi ROI --}}
            <div class="rounded-2xl border border-ink-700/60 bg-ink-800/30 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="font-display text-sm text-ink-50">Kurva Akumulasi ROI</div>
                        <div class="mt-1 font-mono text-[9px] uppercase tracking-[0.22em] text-ink-400">ROI kumulatif tiap posisi selesai, urut tanggal</div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 font-mono text-[9px] uppercase tracking-[0.18em] text-ink-400">
                        <span class="h-0.5 w-4 rounded-full bg-gold-400"></span>
                        Akumulasi
                    </span>
                </div>
                <div data-perf-chart class="relative mt-3 h-64 w-full sm:h-72" aria-label="Grafik akumulasi ROI posisi" role="img">
                    <canvas class="h-full w-full" tabindex="0"></canvas>
                    <div data-perf-tooltip hidden class="pointer-events-none absolute z-10 min-w-36 rounded-xl border border-ink-600/80 bg-ink-900/95 p-3 shadow-[0_20px_50px_-20px_rgba(0,0,0,0.9)] backdrop-blur-md">
                        <div data-perf-tooltip-cum class="font-display text-xl leading-none text-ink-50"></div>
                        <div data-perf-tooltip-roi class="mt-1.5 font-mono text-[10px] uppercase tracking-[0.16em]"></div>
                        <div data-perf-tooltip-meta class="mt-1 font-mono text-[10px] uppercase tracking-[0.16em] text-ink-400"></div>
                    </div>
                    <div data-perf-chart-empty hidden class="absolute inset-0 flex items-center justify-center text-center">
                        <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-400">Tidak ada posisi selesai di rentang ini</div>
                    </div>
                </div>
            </div>

            {{-- Table view twin --}}
            <details class="group rounded-2xl border border-ink-700/60 bg-ink-800/30">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-2 p-4 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300 transition-colors hover:text-gold-200 [&::-webkit-details-marker]:hidden">
                    Lihat tabel data
                    <x-icon name="arrow-right" class="h-3.5 w-3.5 transition-transform group-open:rotate-90" />
                </summary>
                <div class="overflow-x-auto border-t border-ink-700/50">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-700/50 font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">
                                <th class="px-4 py-3 font-normal">Tanggal</th>
                                <th class="px-4 py-3 font-normal">Ticker</th>
                                <th class="px-4 py-3 font-normal">Posisi</th>
                                <th class="px-4 py-3 text-right font-normal">Entry Kamu</th>
                                <th class="px-4 py-3 font-normal">Hasil</th>
                                <th class="px-4 py-3 text-right font-normal">ROI</th>
                            </tr>
                        </thead>
                        <tbody data-perf-table>
                            @foreach (array_reverse($perf['series']) as $row)
                                <tr data-perf-row data-ts="{{ $row['ts'] }}" class="border-b border-ink-700/40 last:border-0">
                                    <td class="px-4 py-2.5 font-mono text-xs text-ink-300">{{ $row['label'] }}</td>
                                    <td class="px-4 py-2.5 font-display text-ink-50">{{ $row['ticker'] }}</td>
                                    <td class="px-4 py-2.5 font-mono text-xs uppercase tracking-[0.14em] text-ink-300">{{ $row['side'] }} {{ $row['leverage'] }}x</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-xs tabular-nums text-ink-300">{{ $row['entry'] !== null ? '$'.rtrim(rtrim(number_format((float) $row['entry'], 8, '.', ','), '0'), '.') : '-' }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="rounded-full border px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.16em] {{ $row['tone'] === 'rose' ? 'border-rose-500/30 bg-rose-500/10 text-rose-200' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' }}">{{ $row['status'] }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono text-xs tabular-nums {{ $row['roi'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">{{ $perfPercent($row['roi']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            @if ($perf['uncounted'] > 0)
                <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">{{ $perf['uncounted'] }} posisi selesai tidak ikut dihitung karena harga entry-nya tidak diisi atau data harga analisanya tidak lengkap.</p>
            @endif
        </div>

        <script type="application/json" data-perf-data>@json($perf['series'])</script>
    </div>
@elseif ($perf && $perf['uncounted'] > 0)
    {{-- Ada riwayat posisi selesai tapi tidak satu pun punya harga entry. --}}
    <div class="reveal flex flex-col gap-3 rounded-2xl border border-ink-700/60 bg-ink-900/70 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-start gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                <x-icon name="bar-chart" class="h-4 w-4" />
            </span>
            <div class="min-w-0">
                <div class="font-display text-lg text-ink-50">Performa belum bisa dihitung.</div>
                <p class="mt-1 max-w-xl text-sm leading-relaxed text-ink-300">{{ $perf['uncounted'] }} posisi kamu selesai tanpa harga entry. Mulai sekarang, isi harga entry saat menekan tombol <span class="text-emerald-200">Posisi</span> - winrate dan kurva performa dihitung dari harga itu.</p>
            </div>
        </div>
    </div>
@endif
