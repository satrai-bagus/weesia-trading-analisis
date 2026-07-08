<x-layouts.app title="Admin Dashboard - Weesia">
    <x-dashboard-shell active="admin">
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
            $latestActive = $signals->firstWhere('status', \App\Models\TradeSignal::STATUS_ACTIVE);
            $rows = $signals->map(fn ($signal) => [
                $signal->ticker,
                $fmt($signal->take_profit),
                $signal->take_profit_2 ? $fmt($signal->take_profit_2) : '-',
                $fmt($signal->stop_loss),
                $signal->statusLabel(),
            ]);
            $timeline = $signals->take(3)->map(fn ($signal) => $signal->ticker.' '.strtolower($signal->statusLabel()).' - TP1 '.$fmt($signal->take_profit).', SL '.$fmt($signal->stop_loss));
        @endphp

        <section class="relative overflow-hidden border-b border-ink-700/60 bg-[linear-gradient(180deg,rgba(18,18,16,0.96),rgba(5,5,5,1))] pt-32 sm:pt-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="admin-dashboard-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                            <path d="M 42 0 L 0 0 0 42" fill="none" stroke="#17d183" stroke-width="0.7" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#admin-dashboard-grid)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 pb-8 sm:px-6 sm:pb-10">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
                    <div class="reveal flex min-h-[260px] flex-col justify-end sm:min-h-[320px]">
                        <div class="inline-flex w-fit items-center gap-3">
                            <span class="h-px w-10 bg-gold-500/60"></span>
                            <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Admin Console</span>
                        </div>
                        <h1 class="mt-5 max-w-3xl font-display text-4xl leading-[1.05] text-ink-50 sm:text-6xl">Kontrol penuh sistem Weesia.</h1>
                        <p class="mt-6 max-w-2xl text-base leading-relaxed text-ink-200">Pantau user, validasi signal, audit performa engine, dan jaga risk framework tetap disiplin dari satu panel operasional.</p>
                        <div class="mt-8 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                            <a href="{{ route('admin.signals') }}" class="group inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-5 py-3 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(23,209,131,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(23,209,131,1)] sm:px-6">
                                <x-icon name="target" class="h-4 w-4" />
                                Publish Signal
                                <x-icon name="arrow-up-right" class="h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                            </a>
                            <a href="#database-summary" class="inline-flex items-center justify-center gap-2 rounded-full border border-ink-500/50 bg-ink-800/60 px-5 py-3 text-sm font-medium text-ink-100 backdrop-blur-md transition-all hover:border-gold-500/50 hover:text-gold-100 sm:px-6">
                                <x-icon name="clipboard" class="h-4 w-4" />
                                Audit Log
                            </a>
                        </div>
                    </div>

                    <div class="reveal relative overflow-hidden rounded-2xl border border-gold-500/20 bg-ink-900/80 p-4 backdrop-blur-xl sm:rounded-3xl sm:p-5">
                        <div class="flex items-center justify-between border-b border-ink-700/70 pb-4">
                            <div>
                                <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Live Market Pulse</div>
                                <div class="mt-1 font-display text-2xl text-ink-50">FibPath Stream</div>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-300">
                                <x-icon name="activity" class="h-5 w-5" />
                            </span>
                        </div>
                        <div class="mt-5 h-40 overflow-hidden rounded-2xl border border-ink-700/70 bg-ink-900/50">
                            <svg viewBox="0 0 360 160" class="h-full w-full">
                                <defs>
                                    <linearGradient id="admin-chart-fill" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#17d183" stop-opacity="0.32" />
                                        <stop offset="100%" stop-color="#17d183" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                                @foreach ([30, 60, 90, 120] as $y)
                                    <line x1="0" x2="360" y1="{{ $y }}" y2="{{ $y }}" stroke="rgba(23,209,131,0.08)" stroke-dasharray="4 8" />
                                @endforeach
                                <path d="M0,112 L42,96 L84,103 L126,72 L168,84 L210,48 L252,58 L294,31 L360,42 L360,160 L0,160 Z" fill="url(#admin-chart-fill)" />
                                <path d="M0,112 L42,96 L84,103 L126,72 L168,84 L210,48 L252,58 L294,31 L360,42" fill="none" stroke="#c9f7e0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                <circle cx="294" cy="31" r="4" fill="#2fc77c" />
                                <circle cx="360" cy="42" r="4" fill="#c9f7e0" />
                            </svg>
                        </div>
                        <div class="mt-5 grid grid-cols-1 gap-3 min-[420px]:grid-cols-3">
                            <x-level label="Ticker" :value="$latestActive?->ticker ?? 'No Signal'" />
                            <x-level label="TP1" :value="$latestActive ? $fmt($latestActive->take_profit) : '-'" tone="emerald" />
                            <x-level label="SL" :value="$latestActive ? $fmt($latestActive->stop_loss) : '-'" tone="gold" />
                        </div>
                    </div>
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
            <div class="space-y-6">
                <div id="publish-signal" class="hidden">
                    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="bot" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-2xl leading-none text-ink-50">Publish Signal Baru</h2>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Real-time</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 px-3 py-2 text-xs text-ink-200">
                            <x-icon name="target" class="h-4 w-4" />
                            Admin
                        </span>
                    </div>

                    @if (session('status'))
                        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                            <x-icon name="target" class="mt-0.5 h-4 w-4 shrink-0" />
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (isset($errors) && $errors->any())
                        <div class="mb-5 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('signals.store') }}" class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-2">
                        @csrf
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Ticker Crypto</span>
                            <input name="ticker" value="{{ old('ticker') }}" list="crypto-ticker-options" placeholder="BTC/USDT" required class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
                            <datalist id="crypto-ticker-options">
                                @foreach ($marketTickers as $ticker)
                                    <option value="{{ $ticker['ticker'] }}">{{ $ticker['price'] ? '$'.$fmt($ticker['price']) : 'Harga live belum tersedia' }}</option>
                                @endforeach
                            </datalist>
                            <span class="mt-2 block text-xs leading-relaxed text-ink-400">Harga live dipakai otomatis sebagai entry estimasi saat signal dipublish.</span>
                        </label>
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Arah Posisi</span>
                            <select name="position_side" required class="min-h-11 w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <option value="{{ \App\Models\TradeSignal::SIDE_LONG }}" @selected(old('position_side', \App\Models\TradeSignal::SIDE_LONG) === \App\Models\TradeSignal::SIDE_LONG)>Long</option>
                                <option value="{{ \App\Models\TradeSignal::SIDE_SHORT }}" @selected(old('position_side') === \App\Models\TradeSignal::SIDE_SHORT)>Short</option>
                            </select>
                        </label>
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Jangka Signal</span>
                            <select name="signal_term" required class="min-h-11 w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <option value="{{ \App\Models\TradeSignal::TERM_SHORT }}" @selected(old('signal_term', \App\Models\TradeSignal::TERM_SHORT) === \App\Models\TradeSignal::TERM_SHORT)>Short Term</option>
                                <option value="{{ \App\Models\TradeSignal::TERM_LONG }}" @selected(old('signal_term') === \App\Models\TradeSignal::TERM_LONG)>Long Term</option>
                            </select>
                        </label>
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Bar Waktu</span>
                            <input type="datetime-local" name="info_at" value="{{ old('info_at') }}" class="min-h-11 w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            <span class="mt-2 block text-xs leading-relaxed text-ink-400">Opsional, hanya tampil di halaman admin.</span>
                        </label>
                        <x-form-field label="Take Profit 1" name="take_profit" placeholder="71420" />
                        <x-form-field label="Stop Loss" name="stop_loss" placeholder="66930" />
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Leverage</span>
                            <input name="leverage" value="{{ old('leverage', 75) }}" inputmode="numeric" required class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
                        </label>
                        <label class="flex items-center justify-between gap-4 rounded-2xl border border-ink-700/70 bg-ink-800/50 px-4 py-3">
                            <span>
                                <span class="block text-sm text-ink-100">Ada Take Profit 2?</span>
                                <span class="mt-1 block font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">Aktifkan kalau setup punya target lanjutan</span>
                            </span>
                            <input data-tp2-toggle="#tp2-field" type="checkbox" name="has_take_profit_2" value="1" class="h-5 w-5 accent-gold-400">
                        </label>
                        <div id="tp2-field" class="hidden">
                            <x-form-field label="Take Profit 2" name="take_profit_2" placeholder="73200" :required="false" />
                        </div>
                        <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(23,209,131,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(23,209,131,1)] lg:col-span-2">
                            <x-icon name="send" class="h-4 w-4" />
                            Publish ke User Dashboard
                        </button>
                    </form>
                </div>

                @if (session('status'))
                    <div class="flex items-start gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                        <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                        {{ session('status') }}
                    </div>
                @endif

                @if (isset($errors) && $errors->any() && ! $errors->has('whatsapp'))
                    <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ $errors->first() }}</div>
                @endif

                @include('dashboard.partials.admin-signal-status-list', ['signals' => $activeSignals])

                <div id="wa-test" class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-200">
                                <x-icon name="send" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-2xl leading-none text-ink-50">Test WhatsApp Fonnte</h2>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">POST /admin/wa/test</div>
                            </div>
                        </div>
                    </div>

                    @error('whatsapp')
                        <div class="mb-5 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{ route('wa.test') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-2">
                        @csrf
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Nomor Target</span>
                            <input name="target" value="{{ old('target') }}" placeholder="08123456789 atau 0812...|Nama|Role" required class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
                            <span class="mt-2 block text-xs leading-relaxed text-ink-400">Bisa banyak target, pisahkan dengan koma seperti format Fonnte.</span>
                        </label>
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Country Code</span>
                            <input name="countryCode" value="{{ old('countryCode', config('services.fonnte.country_code', '62')) }}" class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
                        </label>
                        <label class="lg:col-span-2">
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Pesan</span>
                            <textarea name="message" rows="4" required placeholder="Halo {name}, ini test pesan dari Weesia." class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">{{ old('message') }}</textarea>
                        </label>
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">URL Media Opsional</span>
                            <input name="url" value="{{ old('url') }}" placeholder="https://..." class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
                        </label>
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Filename Opsional</span>
                            <input name="filename" value="{{ old('filename') }}" placeholder="chart-weesia.png" class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
                        </label>
                        <label>
                            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">File Lokal Opsional</span>
                            <input name="file" type="file" class="w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 file:mr-4 file:rounded-full file:border-0 file:bg-gold-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-ink-900">
                        </label>
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-ink-700/70 bg-ink-800/50 px-4 py-3">
                            <span>
                                <span class="block text-sm text-ink-100">Typing Indicator</span>
                                <span class="mt-1 block font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">Tampilkan sedang mengetik</span>
                            </span>
                            <input type="checkbox" name="typing" value="1" class="h-5 w-5 accent-gold-400" @checked(old('typing'))>
                        </div>
                        <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-emerald-200 to-emerald-400 px-6 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(47,199,124,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(47,199,124,1)] lg:col-span-2">
                            <x-icon name="send" class="h-4 w-4" />
                            Kirim Test WhatsApp
                        </button>
                    </form>
                </div>

                <div id="database-summary" class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="database" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-2xl leading-none text-ink-50">Ringkasan Database</h2>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Real-time</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 px-3 py-2 text-xs text-ink-200">
                            <x-icon name="sliders" class="h-4 w-4" />
                            Filter
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-ink-700/60">
                        <div class="min-w-[560px] sm:min-w-[620px]">
                            <div class="grid grid-cols-5 bg-ink-800/70 px-4 py-3">
                                @foreach (['Ticker', 'TP1', 'TP2', 'SL', 'Status'] as $header)
                                    <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-gold-300">{{ $header }}</div>
                                @endforeach
                            </div>
                            @forelse ($rows as $row)
                                <div class="grid grid-cols-5 border-t border-ink-700/60 bg-ink-900 px-4 py-4 text-sm text-ink-100 transition-colors hover:bg-ink-800/50">
                                    @foreach ($row as $cellIndex => $cell)
                                        <div class="min-w-0 truncate pr-3 {{ $cellIndex === 0 ? 'text-gold-200' : '' }}">{{ $cell }}</div>
                                    @endforeach
                                </div>
                            @empty
                                <div class="border-t border-ink-700/60 bg-ink-900 px-4 py-5 text-sm text-ink-300">Belum ada data signal.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <aside class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5">
                    <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                                <x-icon name="shield-check" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-2xl leading-none text-ink-50">Risk Guard</h2>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Rules</div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <x-progress label="Max Exposure" value="62" />
                        <x-progress label="Signal Approval" value="91" />
                        <x-progress label="Error Budget" value="18" />
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
                            <div class="mt-1 text-sm text-ink-50">Synced</div>
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
                                <span class="mt-1 h-2 w-2 rounded-full bg-gold-400 shadow-[0_0_14px_rgba(23,209,131,0.8)]"></span>
                                <div>
                                    <p class="text-sm leading-relaxed text-ink-100">{{ $item }}</p>
                                    <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">baru saja</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm leading-relaxed text-ink-300">Belum ada signal. Publish setup pertama dari form admin.</div>
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
    </x-dashboard-shell>
</x-layouts.app>
