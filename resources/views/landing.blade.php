<x-layouts.app title="Weesia - FibPath Analyzer" :with-csrf="false">
    @php
        $navLinks = [
            ['label' => 'Sistem', 'href' => '#features'],
            ['label' => 'Performa', 'href' => '#stats'],
            ['label' => 'Cara Kerja', 'href' => '#how'],
            ['label' => 'Harga', 'href' => '#pricing'],
            ['label' => 'Kontak', 'href' => '#cta'],
        ];

        $tickers = $landingTickers ?? [
            ['symbol' => 'BTC/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'ETH/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'SOL/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'BNB/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'XRP/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'ADA/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'DOGE/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'AVAX/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'LINK/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
            ['symbol' => 'SEI/USDT', 'price' => '--', 'change' => 'LIVE', 'up' => true],
        ];

        $featured = $featuredAnalysis ?? [
            'symbol' => 'BTC/USDT',
            'title' => 'Bullish Retracement - 0.618',
            'status_meta' => 'Setup Aktif',
            'status_label' => 'Aktif',
            'side' => 'Long',
            'timeframe' => '4H',
            'entry' => '68,420',
            'tp1' => '71,180',
            'sl' => '66,930',
            'profit' => '+4.03%',
            'confidence' => 82,
            'confidence_width' => '82%',
            'is_tp' => false,
        ];

        $features = [
            ['icon' => 'brain', 'title' => 'AI Pattern Recognition', 'desc' => 'FibPath Analyzer mempelajari ribuan pola Fibonacci historis dan mendeteksi formasi yang sedang terbentuk pada multi-timeframe.', 'accent' => 'gold'],
            ['icon' => 'activity', 'title' => 'Real-Time Signal Engine', 'desc' => 'Signal entry, take profit, dan stop loss diperbarui live mengikuti pergerakan harga dengan latensi di bawah 200ms.', 'accent' => 'emerald'],
            ['icon' => 'database', 'title' => 'Trading Journal', 'desc' => 'Setiap analisa, eksekusi, catatan benar atau salah, dan rasionalitas trade tersimpan rapi untuk review.', 'accent' => 'gold'],
            ['icon' => 'trending-up', 'title' => 'Winrate Analytics', 'desc' => 'Metrics dihitung otomatis: winrate, expectancy, average R:R, drawdown, dan kurva equity per setup.', 'accent' => 'emerald'],
            ['icon' => 'layers', 'title' => 'Live Profit Estimator', 'desc' => 'Estimasi keuntungan setiap analisa berjalan dihitung ulang berdasarkan harga market terbaru.', 'accent' => 'gold'],
            ['icon' => 'shield-check', 'title' => 'Risk Framework', 'desc' => 'Kalkulator position sizing, exposure tracker, dan circuit breaker. Disiplin sebelum diskresi.', 'accent' => 'emerald'],
        ];

        $stats = [
            ['label' => 'Total Analisa Tercatat', 'value' => '1832', 'suffix' => '+', 'decimals' => 0],
            ['label' => 'Avg. Winrate Sistem', 'value' => '74.2', 'suffix' => '%', 'decimals' => 1],
            ['label' => 'Avg. R:R Setup', 'value' => '2.8', 'suffix' => 'x', 'decimals' => 1],
            ['label' => 'Profit Factor', 'value' => '3.4', 'suffix' => '', 'decimals' => 1],
        ];

        $steps = [
            ['no' => '01', 'title' => 'Capture', 'desc' => 'FibPath memindai chart, mendeteksi swing high dan swing low, lalu menghitung Fibonacci retracement serta extension otomatis di seluruh timeframe.'],
            ['no' => '02', 'title' => 'Classify', 'desc' => 'Engine AI mencocokkan pola yang terdeteksi dengan basis data ribuan setup historis untuk menentukan probabilitas tiap formasi.'],
            ['no' => '03', 'title' => 'Compose', 'desc' => 'Signal dirangkai menjadi rencana trade lengkap: entry zone, target multi-level, stop loss, position size, dan rasionalitas tertulis.'],
            ['no' => '04', 'title' => 'Capture Result', 'desc' => 'Setiap eksekusi dicatat. Trade selesai akan masuk journal sebagai data benar atau salah untuk analisa lanjutan.'],
        ];

        $tokenTiers = [
            ['label' => 'Tier 1', 'range' => 'ROI &leq; 100%', 'desc' => 'Setup harian, target santai', 'cost' => 1],
            ['label' => 'Tier 2', 'range' => 'ROI 100-300%', 'desc' => 'Swing setup, leverage menengah', 'cost' => 2],
            ['label' => 'Tier 3', 'range' => 'ROI 300-500%', 'desc' => 'High conviction, struktur kuat', 'cost' => 3],
            ['label' => 'Tier 4', 'range' => 'ROI 500%+', 'desc' => 'Sniper setup, multi-target', 'cost' => 5],
        ];

        $tokenPacks = [
            ['amount' => 10, 'price' => '10.000', 'unit' => '1.000', 'discount' => null, 'badge' => null],
            ['amount' => 25, 'price' => '22.500', 'unit' => '900', 'discount' => '-10%', 'badge' => null],
            ['amount' => 50, 'price' => '42.500', 'unit' => '850', 'discount' => '-15%', 'badge' => 'Populer'],
            ['amount' => 100, 'price' => '80.000', 'unit' => '800', 'discount' => '-20%', 'badge' => 'Best Deal'],
        ];

        $footerColumns = [
            ['title' => 'Sistem', 'links' => [['Login Member', route('login')], ['Pattern Engine', '#features'], ['Live Estimator', '#features']]],
            ['title' => 'Perusahaan', 'links' => [['Tentang Weesia', '#'], ['Filosofi', '#'], ['Harga', '#pricing'], ['Kontak', '#cta']]],
            ['title' => 'Legal', 'links' => [['Disclaimer', '#'], ['Privacy', '#'], ['Terms', '#']]],
        ];
    @endphp

    <main class="relative flex-1 overflow-hidden">
        <header data-site-header class="fixed inset-x-0 top-0 z-50 py-6 transition-all duration-500">
            <div class="mx-auto max-w-7xl px-6">
                <div data-site-header-panel class="flex items-center justify-between rounded-full px-2 py-1 transition-all duration-500">
                    <a href="#top" class="group flex items-center gap-3 pl-2">
                        <x-brand-mark />
                        <span class="leading-none">
                            <span class="block font-display text-lg tracking-wide text-ink-50">Weesia</span>
                            <span class="block font-mono text-[9px] uppercase tracking-[0.25em] text-gold-400/80">FibPath Analyzer</span>
                        </span>
                    </a>

                    <nav class="hidden items-center gap-1 md:flex">
                        @foreach ($navLinks as $link)
                            <a href="{{ $link['href'] }}" class="group relative px-4 py-2 text-sm text-ink-200 transition-colors hover:text-ink-50">
                                <span class="relative z-10">{{ $link['label'] }}</span>
                                <span class="absolute inset-x-4 -bottom-px h-px scale-x-0 bg-gradient-to-r from-transparent via-gold-400 to-transparent transition-transform duration-500 group-hover:scale-x-100"></span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="hidden items-center gap-2 md:flex">
                        <a href="{{ route('login') }}" class="group relative overflow-hidden rounded-full border border-gold-500/30 bg-gradient-to-b from-gold-500/10 to-transparent px-5 py-2 text-sm font-medium text-gold-200 transition-all hover:border-gold-400/60 hover:text-gold-100">
                            <span class="relative z-10">Login</span>
                            <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-gold-400/20 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                        </a>
                    </div>

                    <button data-mobile-menu-toggle="#landing-mobile-menu" aria-expanded="false" aria-label="Toggle menu" class="md:hidden rounded-full border border-gold-500/20 p-2 text-ink-100">
                        <x-icon name="menu" data-menu-open-icon class="h-5 w-5" />
                        <x-icon name="x" data-menu-close-icon class="hidden h-5 w-5" />
                    </button>
                </div>

                <div id="landing-mobile-menu" class="glass mt-2 hidden rounded-2xl p-4 md:hidden">
                    <div class="flex flex-col gap-1">
                        @foreach ($navLinks as $link)
                            <a href="{{ $link['href'] }}" class="rounded-lg px-3 py-2 text-sm text-ink-200 hover:bg-ink-800/60 hover:text-ink-50">{{ $link['label'] }}</a>
                        @endforeach
                        <a href="{{ route('login') }}" class="mt-2 rounded-lg border border-gold-500/30 bg-gold-500/10 px-3 py-2 text-center text-sm text-gold-200">Login</a>
                    </div>
                </div>
            </div>
        </header>

        <section id="top" class="relative isolate flex min-h-[100svh] items-center overflow-hidden pb-20 pt-32">
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <canvas data-hero-canvas class="absolute inset-0 h-full w-full"></canvas>
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_30%,rgba(5,5,5,0.85)_85%)]"></div>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-48 bg-gradient-to-b from-transparent to-ink-900"></div>
                <div class="pointer-events-none absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-ink-900/80 to-transparent"></div>
            </div>

            <div class="relative mx-auto grid w-full max-w-7xl grid-cols-1 gap-16 px-6 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-7">
                    <div class="reveal inline-flex items-center gap-2 rounded-full border border-gold-500/20 bg-ink-900/60 px-4 py-1.5 backdrop-blur-md" style="--reveal-delay: 100ms;">
                        <x-icon name="sparkles" class="h-3 w-3 text-gold-300" />
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-200/90">Weesia &middot; Quant Intelligence</span>
                    </div>

                    <h1 class="reveal mt-8 font-display text-5xl leading-[1.05] tracking-tight text-ink-50 sm:text-6xl lg:text-7xl xl:text-[5.5rem]" style="--reveal-delay: 180ms;">
                        Pola tersembunyi pasar.
                        <br>
                        <span class="text-gold-gradient italic">Diterjemahkan.</span>
                    </h1>

                    <p class="reveal mt-8 max-w-xl text-lg leading-relaxed text-ink-200" style="--reveal-delay: 260ms;">
                        <span class="text-gold-200">FibPath Analyzer</span> &mdash; sistem AI milik Weesia yang membaca pola Fibonacci pada market real-time. Setiap analisa, setiap eksekusi, setiap winrate &mdash; terdokumentasi. Estimasi profit dihitung ulang setiap detik mengikuti harga terbaru.
                    </p>

                    <div class="reveal mt-10 flex flex-wrap items-center gap-4" style="--reveal-delay: 340ms;">
                        <a href="{{ route('login') }}" class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-7 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_10px_40px_-10px_rgba(212,167,44,0.6)] transition-all hover:shadow-[0_18px_60px_-10px_rgba(212,167,44,0.8)]">
                            <span class="relative z-10">Mulai Analisa</span>
                            <x-icon name="arrow-right" class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-1" />
                            <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                        </a>
                        <a href="#features" class="inline-flex items-center gap-2 rounded-full border border-ink-500/40 bg-ink-800/40 px-7 py-3.5 text-sm font-medium text-ink-100 backdrop-blur-md transition-all hover:border-gold-500/40 hover:text-gold-100">Lihat Sistem</a>
                    </div>

                    <div class="reveal mt-12 flex flex-wrap items-center gap-8 border-t border-ink-700/60 pt-6" style="--reveal-delay: 420ms;">
                        <div>
                            <div class="font-display text-3xl text-ink-50">74.2<span class="ml-0.5 text-base text-gold-300">%</span></div>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Avg. Winrate</div>
                        </div>
                        <div class="hidden h-10 w-px bg-ink-700 sm:block"></div>
                        <div>
                            <div class="font-display text-3xl text-ink-50">1.8<span class="ml-0.5 text-base text-gold-300">K+</span></div>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Pola Terdeteksi</div>
                        </div>
                        <div class="hidden h-10 w-px bg-ink-700 sm:block"></div>
                        <div>
                            <div class="font-display text-3xl text-ink-50">&lt;200<span class="ml-0.5 text-base text-gold-300">ms</span></div>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Latensi Analisa</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 animate-tilt-in" style="perspective: 1200px; transform-style: preserve-3d;">
                    <div class="relative">
                        <div class="absolute -inset-2 rounded-3xl bg-gradient-to-br from-gold-400/15 via-transparent to-emerald-400/10 blur-2xl"></div>
                        <div
                            data-landing-featured-analysis
                            data-featured-endpoint="{{ route('landing.featured-signal') }}"
                            class="relative overflow-hidden rounded-3xl border border-gold-500/20 bg-ink-900/80 p-6 shadow-[0_30px_80px_-20px_rgba(0,0,0,0.6)] backdrop-blur-xl"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-2.5 w-2.5 animate-pulse-soft rounded-full bg-emerald-400 shadow-[0_0_12px_2px_rgba(47,199,124,0.6)]"></div>
                                    <span class="font-mono text-xs uppercase tracking-widest text-emerald-300">Free Live Analysis</span>
                                </div>
                                <span class="font-mono text-[10px] text-ink-300"><span data-featured-symbol>{{ $featured['symbol'] }}</span> - <span data-featured-timeframe>{{ $featured['timeframe'] }}</span></span>
                            </div>

                            <div class="mt-6 flex items-end justify-between gap-4">
                                <div>
                                    <div data-featured-status-meta class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $featured['status_meta'] }}</div>
                                    <div data-featured-title class="mt-1 font-display text-2xl text-ink-50">{{ $featured['title'] }}</div>
                                </div>
                                <span data-featured-side class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-widest text-emerald-300">{{ $featured['side'] }}</span>
                            </div>

                            <div class="mt-6 h-28 w-full">
                                <svg viewBox="0 0 320 110" class="h-full w-full">
                                    <defs>
                                        <linearGradient id="hero-card-fill" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#d4a72c" stop-opacity="0.4" />
                                            <stop offset="100%" stop-color="#d4a72c" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <path d="M0,80 L40,72 L80,82 L120,60 L160,68 L200,42 L240,50 L280,28 L320,18 L320,110 L0,110 Z" fill="url(#hero-card-fill)" />
                                    <path d="M0,80 L40,72 L80,82 L120,60 L160,68 L200,42 L240,50 L280,28 L320,18" stroke="#fbeeb6" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                    @foreach ([80, 60, 40, 24] as $y)
                                        <line x1="0" x2="320" y1="{{ $y }}" y2="{{ $y }}" stroke="rgba(47,199,124,0.18)" stroke-dasharray="3 5" />
                                    @endforeach
                                    <circle cx="320" cy="18" r="3.5" fill="#fbeeb6" class="animate-pulse-soft" />
                                </svg>
                            </div>

                            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="min-w-0 rounded-2xl border border-ink-700 bg-ink-800/50 p-3 text-ink-100">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">Entry</div>
                                    <div data-featured-entry class="mt-1 min-w-0 break-words font-mono text-sm leading-snug sm:text-[15px]">{{ $featured['entry'] }}</div>
                                </div>
                                <div class="min-w-0 rounded-2xl border border-emerald-500/25 bg-ink-800/50 p-3 text-emerald-300">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">TP1 - 1.272</div>
                                    <div data-featured-tp1 class="mt-1 min-w-0 break-words font-mono text-sm leading-snug sm:text-[15px]">{{ $featured['tp1'] }}</div>
                                </div>
                                <div class="min-w-0 rounded-2xl border border-rose-500/25 bg-ink-800/50 p-3 text-rose-300">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">SL - 0.382</div>
                                    <div data-featured-sl class="mt-1 min-w-0 break-words font-mono text-sm leading-snug sm:text-[15px]">{{ $featured['sl'] }}</div>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-between border-t border-ink-700/60 pt-4">
                                <div>
                                    <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Estimasi Profit</div>
                                    <div data-featured-profit class="font-display text-xl {{ str_starts_with($featured['profit'], '-') ? 'text-rose-300' : 'text-emerald-300' }}">{{ $featured['profit'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Confidence</div>
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="h-1.5 w-20 overflow-hidden rounded-full bg-ink-700">
                                            <div data-featured-confidence-bar class="h-full bg-gradient-to-r from-gold-400 to-emerald-400" style="width: {{ $featured['confidence_width'] }}"></div>
                                        </div>
                                        <span data-featured-confidence class="font-mono text-xs text-ink-100">{{ $featured['confidence'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pointer-events-none absolute bottom-6 left-1/2 hidden -translate-x-1/2 animate-pulse-soft lg:block">
                <div class="flex flex-col items-center gap-2 text-ink-300">
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em]">Scroll</span>
                    <div class="relative h-10 w-px overflow-hidden bg-ink-700">
                        <div class="absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-transparent via-gold-400 to-transparent animate-scroll-line"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative border-y border-ink-700/60 bg-ink-900/80 backdrop-blur-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-32 bg-gradient-to-r from-ink-900 to-transparent"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-32 bg-gradient-to-l from-ink-900 to-transparent"></div>
            <div
                data-landing-market-ticker
                data-market-endpoint="{{ route('landing.market-tickers') }}"
                data-market-tickers="{{ collect($tickers)->pluck('symbol')->implode(',') }}"
                class="overflow-hidden py-4"
            >
                <div class="flex w-max animate-ticker gap-10 whitespace-nowrap">
                    @foreach (array_merge($tickers, $tickers) as $ticker)
                        <div data-market-symbol="{{ $ticker['symbol'] }}" class="flex items-center gap-3">
                            <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $ticker['symbol'] }}</span>
                            <span data-market-price class="font-mono text-sm text-ink-100">{{ $ticker['price'] }}</span>
                            <span data-market-change class="font-mono text-xs {{ $ticker['up'] ? 'text-emerald-300' : 'text-rose-300' }}">{{ $ticker['change'] }}</span>
                            <span class="text-gold-700/60">-</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="features" class="landing-deferred relative scroll-mt-24 py-32 sm:py-40">
            <div class="mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <div class="reveal inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Sistem Inti</span>
                    </div>
                    <h2 class="reveal mt-5 font-display text-4xl leading-[1.1] tracking-tight text-ink-50 sm:text-5xl lg:text-6xl" style="--reveal-delay: 100ms;">
                        Bukan sekadar charting tool.
                        <br>
                        <span class="text-gold-gradient italic">Ini meja kerja seorang quant.</span>
                    </h2>
                    <p class="reveal mt-6 max-w-2xl text-base leading-relaxed text-ink-200" style="--reveal-delay: 200ms;">Enam pilar yang membentuk FibPath Analyzer - dari pola hingga eksekusi, dari journal hingga performa.</p>
                </div>

                <div class="mt-20 grid grid-cols-1 gap-px overflow-hidden rounded-3xl border border-ink-700/60 bg-ink-700/40 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as $index => $feature)
                        <article class="reveal group relative overflow-hidden bg-ink-900/90 p-8 transition-colors hover:bg-ink-800/60" style="--reveal-delay: {{ $index * 60 }}ms;">
                            <div class="pointer-events-none absolute inset-0 -translate-y-full bg-gradient-to-b {{ $feature['accent'] === 'gold' ? 'from-gold-500/20' : 'from-emerald-500/20' }} via-transparent to-transparent transition-transform duration-700 group-hover:translate-y-0"></div>
                            <div class="relative">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-ink-700 bg-ink-800/80 transition-all group-hover:border-gold-500/40">
                                    <x-icon :name="$feature['icon']" class="h-5 w-5 {{ $feature['accent'] === 'gold' ? 'text-gold-300 group-hover:text-gold-200' : 'text-emerald-300 group-hover:text-emerald-200' }}" />
                                </div>
                                <h3 class="mt-6 font-display text-2xl text-ink-50">{{ $feature['title'] }}</h3>
                                <p class="mt-3 text-sm leading-relaxed text-ink-200">{{ $feature['desc'] }}</p>
                                <div class="mt-6 flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.25em] text-ink-300">
                                    <span>Module {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="h-px flex-1 bg-gradient-to-r from-ink-700 to-transparent"></span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="stats" class="landing-deferred relative scroll-mt-24 border-y border-ink-700/60 bg-gradient-to-b from-ink-900 via-ink-800/40 to-ink-900 py-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.04]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="stripe" width="6" height="6" patternUnits="userSpaceOnUse">
                            <path d="M 0 6 L 6 0" stroke="#d4a72c" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#stripe)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <div class="reveal inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Performa</span>
                    </div>
                    <h2 class="reveal mt-5 font-display text-4xl leading-[1.1] tracking-tight text-ink-50 sm:text-5xl lg:text-6xl" style="--reveal-delay: 100ms;">
                        Angka tidak berbohong.
                        <br>
                        <span class="text-emerald-gradient italic">Edge yang terukur.</span>
                    </h2>
                    <p class="reveal mt-6 max-w-2xl text-base leading-relaxed text-ink-200" style="--reveal-delay: 200ms;">Setiap metrik dihitung dari trade aktual yang tercatat di journal Weesia - bukan backtest dan bukan estimasi kosong.</p>
                </div>

                <div class="mt-16 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/40 lg:grid-cols-4">
                    @foreach ($stats as $index => $stat)
                        <article class="reveal group relative overflow-hidden bg-ink-900 p-8 transition-colors hover:bg-ink-800/40" style="--reveal-delay: {{ $index * 80 }}ms;">
                            <div class="font-mono text-[10px] uppercase tracking-[0.25em] text-gold-300">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="mt-6 font-display text-5xl text-ink-50 lg:text-6xl">
                                <span data-counter="{{ $stat['value'] }}" data-decimals="{{ $stat['decimals'] }}" data-suffix="{{ $stat['suffix'] }}" class="tabular-nums">0{{ $stat['suffix'] }}</span>
                            </div>
                            <div class="mt-4 text-xs uppercase tracking-[0.18em] text-ink-300">{{ $stat['label'] }}</div>
                            <div class="mt-6 h-px bg-gradient-to-r from-gold-500/40 via-ink-700 to-transparent"></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="how" class="landing-deferred relative scroll-mt-24 py-32 sm:py-40">
            <div class="mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <div class="reveal inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Cara Kerja</span>
                    </div>
                    <h2 class="reveal mt-5 font-display text-4xl leading-[1.1] tracking-tight text-ink-50 sm:text-5xl lg:text-6xl" style="--reveal-delay: 100ms;">
                        Empat langkah dari pola
                        <br>
                        <span class="text-gold-gradient italic">menjadi posisi.</span>
                    </h2>
                    <p class="reveal mt-6 max-w-2xl text-base leading-relaxed text-ink-200" style="--reveal-delay: 200ms;">Sebuah pipeline analisa yang dirancang sederhana di permukaan, dalam pada eksekusinya.</p>
                </div>

                <div class="mt-20 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    @foreach ($steps as $index => $step)
                        <article class="reveal group relative overflow-hidden rounded-2xl border border-ink-700/60 bg-gradient-to-br from-ink-800/40 to-ink-900 p-8 transition-all hover:border-gold-500/30" style="--reveal-delay: {{ $index * 100 }}ms;">
                            <div class="pointer-events-none absolute -right-4 -top-8 select-none font-display text-[10rem] leading-none text-ink-700/60 transition-colors group-hover:text-gold-700/30">{{ $step['no'] }}</div>
                            <div class="relative">
                                <div class="inline-flex items-center gap-3">
                                    <span class="h-px w-8 bg-gold-500/60"></span>
                                    <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Step {{ $step['no'] }}</span>
                                </div>
                                <h3 class="mt-5 font-display text-3xl text-ink-50">{{ $step['title'] }}</h3>
                                <p class="mt-3 max-w-md text-sm leading-relaxed text-ink-200">{{ $step['desc'] }}</p>
                                <div class="mt-8 flex items-center gap-2">
                                    @foreach ($steps as $railIndex => $rail)
                                        <span class="h-1 flex-1 rounded-full {{ $railIndex <= $index ? 'bg-gold-400/70' : 'bg-ink-700' }}"></span>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="pricing" class="landing-deferred relative scroll-mt-24 overflow-hidden border-y border-ink-700/60 bg-gradient-to-b from-ink-900 via-ink-800/30 to-ink-900 py-28 sm:py-36">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="pricing-grid" width="48" height="48" patternUnits="userSpaceOnUse">
                            <path d="M 48 0 L 0 0 0 48" fill="none" stroke="#d4a72c" stroke-width="0.6" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#pricing-grid)" />
                </svg>
            </div>
            <div class="pointer-events-none absolute -left-32 top-1/3 h-72 w-72 rounded-full bg-gold-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-32 bottom-1/4 h-80 w-80 rounded-full bg-emerald-500/10 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <div class="reveal inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Harga &middot; Pricing</span>
                    </div>
                    <h2 class="reveal mt-5 font-display text-4xl leading-[1.1] tracking-tight text-ink-50 sm:text-5xl lg:text-6xl" style="--reveal-delay: 100ms;">
                        Harga seharga
                        <br>
                        <span class="text-gold-gradient italic">satu kopi.</span>
                    </h2>
                    <p class="reveal mt-6 max-w-2xl text-base leading-relaxed text-ink-200" style="--reveal-delay: 200ms;">Promo awal peluncuran. Akses analisa profesional dengan harga yang masuk akal &mdash; fokus ke disiplin dulu, scale-up nanti.</p>

                    <div class="reveal mt-6 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 backdrop-blur-md" style="--reveal-delay: 280ms;">
                        <span class="h-2 w-2 animate-pulse-soft rounded-full bg-emerald-400 shadow-[0_0_10px_2px_rgba(47,199,124,0.6)]"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-emerald-200">Limited &middot; Promo Awal Peluncuran</span>
                    </div>
                </div>

                <div class="mt-16 grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
                    <article class="reveal group relative overflow-hidden rounded-3xl border border-gold-500/40 bg-gradient-to-br from-ink-800/90 via-ink-900 to-ink-900 p-8 shadow-[0_30px_80px_-30px_rgba(212,167,44,0.4)] transition-all hover:border-gold-400/60 hover:shadow-[0_40px_100px_-30px_rgba(212,167,44,0.55)] sm:p-10" style="--reveal-delay: 100ms;">
                        <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-gold-500/20 blur-3xl"></div>
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-400 to-transparent"></div>

                        <div class="relative flex items-center justify-between">
                            <div class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-3 py-1">
                                <x-icon name="shield-check" class="h-3.5 w-3.5 text-gold-200" />
                                <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-gold-200">Subscriber</span>
                            </div>
                            <span class="rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-900 shadow-[0_8px_24px_-8px_rgba(212,167,44,0.8)]">Most Value</span>
                        </div>

                        <div class="relative mt-7">
                            <h3 class="font-display text-3xl text-ink-50 sm:text-4xl">Akses Penuh Bulanan</h3>
                            <p class="mt-3 max-w-md text-sm leading-relaxed text-ink-200">Buka semua analisa aktif tanpa batas, alert WhatsApp prioritas, dan live estimator selalu nyala.</p>
                        </div>

                        <div class="relative mt-7 flex items-end gap-3">
                            <span class="font-display text-6xl text-ink-50 sm:text-7xl">Rp&nbsp;25rb</span>
                            <span class="mb-2 font-mono text-xs uppercase tracking-[0.25em] text-ink-300">/ bulan / user</span>
                        </div>
                        <div class="relative mt-2 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1">
                            <x-icon name="trending-up" class="h-3.5 w-3.5 text-emerald-300" />
                            <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-emerald-200">5 analisa = balik modal</span>
                        </div>

                        <ul class="relative mt-8 space-y-3">
                            @foreach ([
                                'Semua analisa aktif terbuka tanpa batas',
                                'Alert WhatsApp realtime saat TP / SL kena',
                                'Live profit estimator real-time',
                                'Arsip TP / SL terbuka selamanya',
                                'Akses ke semua signal baru selama subscription aktif',
                            ] as $perk)
                                <li class="flex items-start gap-3 text-sm text-ink-100">
                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-gold-500/30 bg-gold-500/10">
                                        <x-icon name="check-circle" class="h-3 w-3 text-gold-200" />
                                    </span>
                                    <span class="leading-relaxed">{{ $perk }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('checkout') }}" class="relative mt-9 inline-flex w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-7 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_18px_60px_-15px_rgba(212,167,44,0.7)] transition-all hover:shadow-[0_24px_80px_-15px_rgba(212,167,44,0.9)]">
                            <span class="relative z-10">Subscribe Sekarang</span>
                            <x-icon name="arrow-up-right" class="relative z-10 h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                            <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/35 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                        </a>

                        <p class="relative mt-4 text-center font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Bisa di-cancel kapan saja &middot; Tanpa auto-renew</p>
                    </article>

                    <article class="reveal group relative overflow-hidden rounded-3xl border border-ink-700/60 bg-ink-900/85 p-8 backdrop-blur-xl transition-all hover:border-emerald-400/40 sm:p-10" style="--reveal-delay: 200ms;">
                        <div class="pointer-events-none absolute -left-20 -bottom-20 h-56 w-56 rounded-full bg-emerald-500/15 blur-3xl"></div>

                        <div class="relative flex items-center justify-between">
                            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1">
                                <x-icon name="wallet" class="h-3.5 w-3.5 text-emerald-300" />
                                <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-emerald-200">Pay-per-Analisa</span>
                            </div>
                            <span class="rounded-full border border-ink-600/70 bg-ink-800/60 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200">Flexible</span>
                        </div>

                        <div class="relative mt-7">
                            <h3 class="font-display text-3xl text-ink-50 sm:text-4xl">Token Bayar per Buka</h3>
                            <p class="mt-3 max-w-md text-sm leading-relaxed text-ink-200">Pilih cuma analisa yang kamu suka. Bayar sekali, kebuka selamanya. Cocok buat yang mau coba dulu sebelum commit.</p>
                        </div>

                        <div class="relative mt-7 flex items-end gap-3">
                            <span class="font-display text-6xl text-ink-50 sm:text-7xl">Mulai 1rb</span>
                            <span class="mb-2 font-mono text-xs uppercase tracking-[0.25em] text-ink-300">/ analisa</span>
                        </div>
                        <p class="relative mt-2 font-mono text-[11px] uppercase tracking-[0.18em] text-ink-300">Harga sesuai potensi ROI signal</p>

                        <div class="relative mt-7 space-y-2.5">
                            @foreach ($tokenTiers as $tier)
                                <div class="flex items-center gap-4 rounded-2xl border border-ink-700/60 bg-ink-800/40 p-3 transition-colors group-hover:border-ink-600/80">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-500/25 bg-emerald-500/10 font-display text-sm text-emerald-200">
                                        {{ $tier['cost'] }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-display text-sm text-ink-50">{{ $tier['label'] }}</span>
                                            <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-gold-300">{!! $tier['range'] !!}</span>
                                        </div>
                                        <div class="mt-0.5 truncate text-xs text-ink-300">{{ $tier['desc'] }}</div>
                                    </div>
                                    <span class="shrink-0 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $tier['cost'] }} token</span>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ route('checkout') }}" class="relative mt-8 inline-flex w-full items-center justify-center gap-2 overflow-hidden rounded-full border border-emerald-400/40 bg-emerald-500/10 px-7 py-3.5 text-sm font-semibold text-emerald-100 transition-all hover:border-emerald-300/70 hover:bg-emerald-500/20">
                            <span class="relative z-10">Beli Token &amp; Mulai</span>
                            <x-icon name="arrow-up-right" class="relative z-10 h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                        </a>

                        <p class="relative mt-4 text-center font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Bayar sekali &middot; Analisa kebuka selamanya</p>
                    </article>
                </div>

                <div class="reveal mt-16 rounded-3xl border border-ink-700/60 bg-ink-900/70 p-6 backdrop-blur-xl sm:p-8" style="--reveal-delay: 240ms;">
                    <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <div class="inline-flex items-center gap-3">
                                <span class="h-px w-8 bg-gold-500/60"></span>
                                <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Token Pack</span>
                            </div>
                            <h3 class="mt-3 font-display text-2xl text-ink-50 sm:text-3xl">Beli Banyak, Hemat Lebih Banyak</h3>
                            <p class="mt-2 max-w-xl text-sm leading-relaxed text-ink-300">Top-up sekali pakai berkali-kali. Diskon makin gede tiap kelipatan paket.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-gold-500/25 bg-gold-500/10 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200">
                            <x-icon name="sparkles" class="h-3 w-3" />
                            1 token = 1.000
                        </span>
                    </div>

                    <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($tokenPacks as $pack)
                            @php $featured = $pack['badge'] === 'Best Deal'; @endphp
                            <div class="group relative overflow-hidden rounded-2xl border {{ $featured ? 'border-gold-500/40 bg-gradient-to-b from-gold-500/10 to-transparent' : 'border-ink-700/70 bg-ink-800/40' }} p-5 transition-all hover:border-gold-500/40 hover:bg-ink-800/60">
                                @if ($pack['badge'])
                                    <span class="absolute right-3 top-3 rounded-full {{ $featured ? 'bg-gradient-to-b from-gold-300 to-gold-500 text-ink-900' : 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-200' }} px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.2em]">{{ $pack['badge'] }}</span>
                                @endif

                                <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">{{ $pack['amount'] }} Token</div>
                                <div class="mt-3 flex items-baseline gap-2">
                                    <span class="font-display text-3xl text-ink-50">Rp {{ $pack['price'] }}</span>
                                    @if ($pack['discount'])
                                        <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-emerald-300">{{ $pack['discount'] }}</span>
                                    @endif
                                </div>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">@ Rp {{ $pack['unit'] }} / token</div>

                                <div class="mt-5 h-px bg-gradient-to-r from-gold-500/40 via-ink-700 to-transparent"></div>
                                <div class="mt-4 flex items-center justify-between font-mono text-[10px] uppercase tracking-[0.2em]">
                                    <span class="text-ink-300">~{{ floor($pack['amount']) }} analisa Tier 1</span>
                                    <span class="text-gold-200 transition-transform group-hover:translate-x-0.5">Pilih &rarr;</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-7 grid grid-cols-1 gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5 sm:grid-cols-3">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                                <x-icon name="check-circle" class="h-4 w-4" />
                            </span>
                            <div>
                                <div class="font-display text-sm text-ink-50">Tanpa Expired</div>
                                <p class="mt-1 text-xs leading-relaxed text-ink-300">Token kamu tetap valid sampai dipakai semua.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                                <x-icon name="shield-check" class="h-4 w-4" />
                            </span>
                            <div>
                                <div class="font-display text-sm text-ink-50">Akses Selamanya</div>
                                <p class="mt-1 text-xs leading-relaxed text-ink-300">Analisa yang dibuka tetap kebuka, walau signal sudah selesai.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                                <x-icon name="activity" class="h-4 w-4" />
                            </span>
                            <div>
                                <div class="font-display text-sm text-ink-50">Live Estimator</div>
                                <p class="mt-1 text-xs leading-relaxed text-ink-300">Setiap analisa kebuka langsung dapat estimasi P/L real-time.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="reveal mt-12 flex flex-col items-center justify-center gap-3 text-center sm:flex-row sm:gap-6" style="--reveal-delay: 320ms;">
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-ink-300">Bingung pilih?</span>
                    <span class="hidden h-px w-12 bg-ink-700 sm:block"></span>
                    <span class="text-sm text-ink-200">Aktif tiap hari? Subscribe. Casual? Token. Sama-sama hemat.</span>
                </div>
            </div>
        </section>

        <section id="cta" class="landing-deferred relative scroll-mt-24 overflow-hidden py-32 sm:py-40">
            <div class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                @foreach ([300, 500, 720, 980] as $size)
                    <div class="absolute -translate-x-1/2 -translate-y-1/2 rounded-full border border-gold-500/8" style="width: {{ $size }}px; height: {{ $size }}px; left: 0; top: 0;"></div>
                @endforeach
            </div>

            <div class="relative mx-auto max-w-4xl px-6 text-center">
                <div class="reveal inline-flex items-center gap-3">
                    <span class="h-px w-10 bg-gold-500/60"></span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Get Started</span>
                    <span class="h-px w-10 bg-gold-500/60"></span>
                </div>
                <h2 class="reveal mt-6 font-display text-5xl leading-[1.05] tracking-tight text-ink-50 sm:text-6xl lg:text-7xl" style="--reveal-delay: 120ms;">
                    Trade dengan <span class="text-gold-gradient italic">disiplin</span>.
                    <br>
                    Bukan dengan harapan.
                </h2>
                <p class="reveal mx-auto mt-6 max-w-2xl text-base leading-relaxed text-ink-200" style="--reveal-delay: 220ms;">Akses dashboard internal Weesia. Catat setiap analisa, ukur winrate sebenarnya, dan lihat estimasi profit dalam satu meja kerja.</p>
                <div class="reveal mt-12 flex flex-wrap items-center justify-center gap-4" style="--reveal-delay: 320ms;">
                    <a href="{{ route('login') }}" class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-8 py-4 text-sm font-semibold text-ink-900 shadow-[0_18px_60px_-10px_rgba(212,167,44,0.6)] transition-all hover:shadow-[0_24px_80px_-10px_rgba(212,167,44,0.8)]">
                        <span class="relative z-10">Login Member</span>
                        <x-icon name="arrow-up-right" class="relative z-10 h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                        <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                    </a>
                    <a href="#features" class="inline-flex items-center gap-2 rounded-full border border-ink-500/40 bg-ink-800/40 px-8 py-4 text-sm font-medium text-ink-100 backdrop-blur-md transition-all hover:border-gold-500/40">Lihat Sistem</a>
                </div>
                <div class="reveal mt-14 flex flex-col items-center justify-center gap-4 text-ink-300 sm:flex-row sm:gap-6">
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em]">Built by Weesia</span>
                    <span class="h-px w-16 bg-ink-700"></span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em]">Powered by FibPath AI</span>
                </div>
            </div>
        </section>

        <footer class="relative border-t border-ink-700/60 bg-ink-900">
            <div class="mx-auto max-w-7xl px-6 py-14">
                <div class="flex flex-col items-start justify-between gap-10 md:flex-row md:items-center">
                    <div>
                        <div class="font-display text-2xl text-ink-50">Weesia</div>
                        <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300/80">FibPath Analyzer</div>
                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-ink-300">Quantitative trading intelligence. Disiplin sebelum diskresi, data sebelum opini.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-12 sm:grid-cols-3">
                        @foreach ($footerColumns as $column)
                            <div>
                                <div class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300/80">{{ $column['title'] }}</div>
                                <ul class="mt-4 space-y-2">
                                    @foreach ($column['links'] as [$label, $href])
                                        <li><a href="{{ $href }}" class="text-sm text-ink-200 transition-colors hover:text-gold-200">{{ $label }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-14 flex flex-col items-start justify-between gap-4 border-t border-ink-700/60 pt-6 text-xs text-ink-300 sm:flex-row sm:items-center">
                    <span class="font-mono uppercase tracking-[0.2em]">&copy; {{ now()->year }} Weesia - All rights reserved</span>
                    <span class="font-mono uppercase tracking-[0.2em]">Trading involves risk - Past performance is not future results</span>
                </div>
            </div>
        </footer>
    </main>
</x-layouts.app>
