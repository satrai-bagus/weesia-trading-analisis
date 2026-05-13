<x-layouts.app title="Manajemen Signal - Weesia">
    <x-dashboard-shell active="admin-signals">
        @php
            $fmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 8, '.', ','), '0'), '.');
            $toneClass = fn ($signal) => match ($signal->statusTone()) {
                'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
                default => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
            };
            $priceMap = $priceMap ?? [];
            $marketTickers = $marketTickers ?? [];
            $entryFor = fn ($signal) => $signal->entry_price ?: ($priceMap[$signal->ticker] ?? null);
            $liveFor = fn ($signal) => $priceMap[$signal->ticker] ?? null;
            $liveRefFor = fn ($signal) => $liveFor($signal) ?: $entryFor($signal);
            $usd = fn ($value) => $value ? '$'.$fmt($value) : '-';
            $percent = fn ($value) => $value === null ? '-' : ($value > 0 ? '+' : '').rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.').'%';
            $roiTo = fn ($signal, $target) => $target ? $signal->leveragedMoveTo((float) $target, $liveRefFor($signal)) : null;
        @endphp

        <section class="relative overflow-hidden border-b border-ink-700/60 bg-[linear-gradient(180deg,rgba(18,18,16,0.96),rgba(5,5,5,1))] pt-32 sm:pt-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="admin-signals-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                            <path d="M 42 0 L 0 0 0 42" fill="none" stroke="#d4a72c" stroke-width="0.7" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#admin-signals-grid)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 pb-8 sm:px-6 sm:pb-10">
                <div class="reveal">
                    <div class="inline-flex w-fit items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Signal Trading</span>
                    </div>
                    <h1 class="mt-5 max-w-3xl font-display text-4xl leading-[1.05] text-ink-50 sm:text-6xl">Publish dan pantau signal.</h1>
                    <p class="mt-6 max-w-2xl text-base leading-relaxed text-ink-200">Buat signal baru, koreksi parameter, dan awasi status TP/SL dari satu halaman khusus.</p>
                </div>

                <div class="reveal mt-10 grid grid-cols-1 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/40 sm:grid-cols-2 lg:grid-cols-4">
                    <x-stat-card label="Total Signal" :value="$stats['total']" change="tersimpan di database" />
                    <x-stat-card label="Signal Aktif" :value="$stats['active']" change="tampil di dashboard user" tone="emerald" />
                    <x-stat-card label="Kena TP" :value="$stats['hitTp']" change="TP1 atau TP2" tone="emerald" />
                    <x-stat-card label="Kena SL" :value="$stats['hitSl']" change="perlu evaluasi setup" tone="rose" />
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 sm:py-8">
            @include('dashboard.partials.admin-signal-form')
            @include('dashboard.partials.admin-signal-status-list')
        </section>
    </x-dashboard-shell>
</x-layouts.app>
