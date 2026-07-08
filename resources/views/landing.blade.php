<x-layouts.app title="Weesia - Riset Market Crypto" :with-csrf="false">
    @php
        $navLinks = [
            ['label' => 'Metodologi', 'href' => '#metodologi'],
            ['label' => 'Sistem', 'href' => '#sistem'],
            ['label' => 'Transparansi', 'href' => '#transparansi'],
            ['label' => 'Harga', 'href' => '#harga'],
            ['label' => 'Disclaimer', 'href' => '#disclaimer'],
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
            'status_meta' => 'Contoh Analisa',
            'side' => 'Long',
            'timeframe' => '4H',
            'entry' => '68,420',
            'tp1' => '71,180',
            'sl' => '66,930',
            'rr' => '1 : 1.9',
            'is_live' => false,
            'live_label' => 'Contoh Analisa',
        ];
        $featuredIsLive = (bool) ($featured['is_live'] ?? false);

        $record = $recordStats ?? ['total' => 0, 'active' => 0, 'hitTp' => 0, 'hitSl' => 0, 'closed' => 0];

        $features = [
            [
                'icon' => 'brain',
                'title' => 'Pemetaan Fibonacci Multi-Timeframe',
                'desc' =>
                    'FibPath Analyzer membaca struktur swing market lalu memetakan retracement dan extension pada beberapa timeframe sekaligus - fondasi setiap outlook.',
                'accent' => 'gold',
            ],
            [
                'icon' => 'activity',
                'title' => 'Chart Live per Analisa',
                'desc' =>
                    'Level entry, target, dan invalidasi tergambar langsung di chart yang bergerak mengikuti harga market real-time. Kamu melihat analisanya bekerja, bukan sekadar membaca angka.',
                'accent' => 'emerald',
            ],
            [
                'icon' => 'clipboard',
                'title' => 'Skenario Tertulis',
                'desc' =>
                    'Setiap outlook dirilis sebagai rencana yang bisa diuji: arah, jangka waktu, level penting, dan kondisi yang membatalkan analisa. Bukan sekadar panah naik-turun.',
                'accent' => 'gold',
            ],
            [
                'icon' => 'database',
                'title' => 'Arsip Riset Terbuka',
                'desc' =>
                    'Analisa yang selesai membeku di titik akhirnya - kena target maupun kena stop - dan tersimpan sebagai catatan terbuka yang bisa dinilai siapa pun.',
                'accent' => 'emerald',
            ],
            [
                'icon' => 'bell',
                'title' => 'Alert Level Real-Time',
                'desc' =>
                    'Notifikasi saat harga menyentuh level target atau level invalidasi. Kamu tidak perlu menatap chart seharian untuk mengikuti perkembangan skenario.',
                'accent' => 'gold',
            ],
            [
                'icon' => 'shield-check',
                'title' => 'Framework Risiko & Edukasi',
                'desc' =>
                    'Materi cara membaca analisa, position sizing, dan disiplin manajemen risiko. Prinsip kami: risiko dihitung sebelum target dibicarakan.',
                'accent' => 'emerald',
            ],
        ];

        $steps = [
            [
                'no' => '01',
                'title' => 'Konteks & Struktur',
                'desc' =>
                    'Semua dimulai dari data: struktur swing, tren besar, dan konteks multi-timeframe dipetakan lebih dulu sebelum bias arah ditentukan - cara kerja meja riset institusional.',
            ],
            [
                'no' => '02',
                'title' => 'Confluence Fibonacci',
                'desc' =>
                    'Retracement dan extension dihitung dari struktur tersebut. Sebuah setup hanya lolos ke publikasi jika beberapa lapis confluence bertemu di zona yang sama.',
            ],
            [
                'no' => '03',
                'title' => 'Skenario & Invalidasi',
                'desc' =>
                    'Outlook ditulis sebagai skenario yang bisa diuji: zona entry, target bertahap, dan level pasti di mana analisa dinyatakan salah. Tanpa level invalidasi, itu bukan riset.',
            ],
            [
                'no' => '04',
                'title' => 'Review & Arsip',
                'desc' =>
                    'Hasil dicatat apa adanya - benar maupun salah - lalu masuk arsip terbuka. Setiap kesalahan menjadi bahan evaluasi metode untuk analisa berikutnya.',
            ],
        ];

        $principles = ['Risiko dihitung sebelum target', 'Data sebelum opini', 'Salah pun dicatat'];

        $tokenTiers = [
            ['label' => 'Analisa Harian', 'range' => 'Intraday', 'desc' => 'Outlook cepat dengan level utama', 'cost' => 1],
            ['label' => 'Swing Setup', 'range' => 'Multi-Day', 'desc' => 'Skenario menengah dengan konteks lebih dalam', 'cost' => 2],
            ['label' => 'Riset Mendalam', 'range' => 'Struktur Besar', 'desc' => 'Confluence berlapis di timeframe tinggi', 'cost' => 3],
            ['label' => 'Full Riset', 'range' => 'Multi-Target', 'desc' => 'Skenario lengkap dengan target bertahap', 'cost' => 5],
        ];

        $tokenPacks = [
            ['amount' => 10, 'price' => '10.000', 'unit' => '1.000', 'discount' => null, 'badge' => null],
            ['amount' => 25, 'price' => '22.500', 'unit' => '900', 'discount' => '-10%', 'badge' => null],
            ['amount' => 50, 'price' => '42.500', 'unit' => '850', 'discount' => '-15%', 'badge' => 'Populer'],
            ['amount' => 100, 'price' => '80.000', 'unit' => '800', 'discount' => '-20%', 'badge' => 'Termurah'],
        ];

        $disclaimers = [
            [
                'title' => 'Bukan nasihat finansial',
                'desc' => 'Seluruh konten Weesia adalah riset dan edukasi. Kami tidak memerintahkan kamu membeli atau menjual aset apa pun.',
            ],
            [
                'title' => 'Crypto berisiko tinggi',
                'desc' => 'Harga aset kripto sangat fluktuatif dan kamu bisa kehilangan seluruh modal. Penggunaan leverage memperbesar risiko itu berkali-kali lipat.',
            ],
            [
                'title' => 'Masa lalu bukan jaminan',
                'desc' => 'Arsip kami menunjukkan hasil riset sebelumnya - benar maupun salah. Hasil di masa lalu tidak menjamin hasil di masa depan.',
            ],
            [
                'title' => 'Keputusan di tangan kamu',
                'desc' => 'Eksekusi, ukuran posisi, dan risiko sepenuhnya tanggung jawab kamu. Gunakan hanya dana yang siap kamu pertaruhkan.',
            ],
            [
                'title' => 'Kami tidak mengelola dana',
                'desc' => 'Weesia tidak menerima titipan dana, tidak menjanjikan imbal hasil, dan tidak menjalankan program investasi apa pun.',
            ],
        ];

        $footerColumns = [
            [
                'title' => 'Riset',
                'links' => [
                    ['Login Member', route('login')],
                    ['Metodologi', '#metodologi'],
                    ['Transparansi', '#transparansi'],
                ],
            ],
            [
                'title' => 'Weesia',
                'links' => [['Harga', '#harga'], ['Daftar Gratis', route('register')], ['Kontak', 'mailto:'.config('app.contact_email')]],
            ],
            [
                'title' => 'Legal',
                'links' => [
                    ['Disclaimer', route('legal.disclaimer')],
                    ['Privasi', route('legal.privacy')],
                    ['Ketentuan', route('legal.terms')],
                ],
            ],
        ];

        // Pricing CTAs: members go straight to checkout; guests are sent to login
        // carrying a return-to-checkout intent so they land on /buy after auth
        // instead of a contextless login wall.
        $checkoutHref = auth()->check()
            ? route('checkout')
            : route('login', ['redirect' => route('checkout', [], false)]);
    @endphp

    <main class="relative flex-1 overflow-hidden">
        <header class="fixed inset-x-0 top-0 z-50 py-6 transition-all duration-500" data-site-header>
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex items-center justify-between rounded-full px-2 py-1 transition-all duration-500"
                    data-site-header-panel>
                    <a class="group flex items-center gap-3 pl-2" href="#top">
                        <x-brand-mark />
                        <span class="leading-none">
                            <span class="text-ink-50 block font-mono text-[15px] font-semibold uppercase tracking-[0.34em]">Weesia</span>
                            <span class="text-gold-400/80 mt-1 block font-mono text-[9px] uppercase tracking-[0.25em]">Riset Market Crypto</span>
                        </span>
                    </a>

                    <nav class="hidden items-center gap-1 md:flex">
                        @foreach ($navLinks as $link)
                            <a class="text-ink-200 hover:text-ink-50 group relative px-4 py-2 text-sm transition-colors"
                                href="{{ $link['href'] }}">
                                <span class="relative z-10">{{ $link['label'] }}</span>
                                <span
                                    class="via-gold-400 absolute inset-x-4 -bottom-px h-px scale-x-0 bg-gradient-to-r from-transparent to-transparent transition-transform duration-500 group-hover:scale-x-100"></span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="hidden items-center gap-2 md:flex">
                        @if (auth()->user()?->role == 'admin')
                            <a class="border-gold-500/30 from-gold-500/10 text-gold-200 hover:border-gold-400/60 hover:text-gold-100 group relative overflow-hidden rounded-full border bg-gradient-to-b to-transparent px-5 py-2 text-sm font-medium transition-all"
                                href="{{ route('admin.dashboard') }}">
                                <span class="relative z-10">Dashboard</span>
                                <span
                                    class="via-gold-400/20 absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                            </a>
                        @elseif (auth()->user()?->role == 'user')
                            <a class="border-gold-500/30 from-gold-500/10 text-gold-200 hover:border-gold-400/60 hover:text-gold-100 group relative overflow-hidden rounded-full border bg-gradient-to-b to-transparent px-5 py-2 text-sm font-medium transition-all"
                                href="{{ route('user.dashboard') }}">
                                <span class="relative z-10">Buka Analisa</span>
                                <span
                                    class="via-gold-400/20 absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                            </a>
                        @else
                            <a class="border-gold-500/30 from-gold-500/10 text-gold-200 hover:border-gold-400/60 hover:text-gold-100 group relative overflow-hidden rounded-full border bg-gradient-to-b to-transparent px-5 py-2 text-sm font-medium transition-all"
                                href="{{ route('login') }}">
                                <span class="relative z-10">Login</span>
                                <span
                                    class="via-gold-400/20 absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                            </a>
                        @endif
                    </div>

                    <button class="border-gold-500/20 text-ink-100 rounded-full border p-2 md:hidden"
                        data-mobile-menu-toggle="#landing-mobile-menu" aria-expanded="false" aria-label="Toggle menu">
                        <x-icon class="h-5 w-5" name="menu" data-menu-open-icon />
                        <x-icon class="hidden h-5 w-5" name="x" data-menu-close-icon />
                    </button>
                </div>

                <div class="glass mt-2 hidden rounded-2xl p-4 md:hidden" id="landing-mobile-menu">
                    <div class="flex flex-col gap-1">
                        @foreach ($navLinks as $link)
                            <a class="text-ink-200 hover:bg-ink-800/60 hover:text-ink-50 rounded-lg px-3 py-2 text-sm"
                                href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                        @endforeach
                        @if (auth()->user()?->role == 'admin')
                            <a class="border-gold-500/30 bg-gold-500/10 text-gold-200 mt-2 rounded-lg border px-3 py-2 text-center text-sm"
                                href="{{ route('admin.dashboard') }}">Dashboard</a>
                        @elseif (auth()->user()?->role == 'user')
                            <a class="border-gold-500/30 bg-gold-500/10 text-gold-200 mt-2 rounded-lg border px-3 py-2 text-center text-sm"
                                href="{{ route('user.dashboard') }}">Buka Analisa</a>
                        @else
                            <a class="border-gold-500/30 bg-gold-500/10 text-gold-200 mt-2 rounded-lg border px-3 py-2 text-center text-sm"
                                href="{{ route('login') }}">Login</a>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <section class="relative isolate flex min-h-[100svh] items-center overflow-hidden pb-20 pt-32" id="top">
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <canvas class="absolute inset-0 h-full w-full" data-hero-canvas></canvas>
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_30%,rgba(5,5,5,0.85)_85%)]">
                </div>
                <div
                    class="to-ink-900 pointer-events-none absolute inset-x-0 bottom-0 h-48 bg-gradient-to-b from-transparent">
                </div>
                <div
                    class="from-ink-900/80 pointer-events-none absolute inset-x-0 top-0 h-32 bg-gradient-to-b to-transparent">
                </div>
            </div>

            <div class="relative mx-auto grid w-full max-w-7xl grid-cols-1 gap-16 px-6 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-7">
                    <div class="border-gold-500/20 bg-ink-900/60 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 backdrop-blur-md"
                        data-hero-stage>
                        <x-icon class="text-gold-300 h-3 w-3" name="sparkles" />
                        <span class="text-gold-200/90 font-mono text-[10px] uppercase tracking-[0.3em]">Weesia &middot;
                            Riset Market Crypto</span>
                    </div>

                    <h1 class="font-display text-ink-50 mt-8 text-5xl leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl xl:text-[5.5rem]"
                        data-hero-stage>
                        Outlook market crypto.
                        <br>
                        <span class="text-gold-gradient italic">Diriset, bukan ditebak.</span>
                    </h1>

                    <p class="text-ink-200 mt-8 max-w-xl text-lg leading-relaxed" data-hero-stage>
                        Weesia membedah struktur market dengan kedalaman ala meja riset hedge fund &mdash;
                        <span class="text-gold-200">Fibonacci multi-timeframe</span>, skenario tertulis, dan level
                        invalidasi yang jelas. Semua hasil diarsipkan terbuka: yang benar maupun yang salah.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4" data-hero-stage>
                        <a class="from-gold-300 to-gold-500 text-ink-900 group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-gradient-to-b px-7 py-3.5 text-sm font-semibold shadow-[0_10px_40px_-10px_rgba(23,209,131,0.6)] transition-all hover:shadow-[0_18px_60px_-10px_rgba(23,209,131,0.8)]"
                            href="{{ route('login') }}">
                            <span class="relative z-10">Mulai Baca Analisa</span>
                            <x-icon class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-1"
                                name="arrow-right" />
                            <span
                                class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                        </a>
                        <a class="border-ink-500/40 bg-ink-800/40 text-ink-100 hover:border-gold-500/40 hover:text-gold-100 inline-flex items-center gap-2 rounded-full border px-7 py-3.5 text-sm font-medium backdrop-blur-md transition-all"
                            href="#metodologi">Pelajari Metodologi</a>
                    </div>

                    <div class="border-ink-700/60 mt-12 flex flex-wrap items-center gap-8 border-t pt-6"
                        data-hero-stage>
                        <div>
                            <div class="font-display text-ink-50 text-3xl"><span class="tabular-nums"
                                    data-counter="{{ $record['total'] }}" data-decimals="0"
                                    data-suffix="">0</span></div>
                            <div class="text-ink-300 mt-1 font-mono text-[10px] uppercase tracking-[0.2em]">Analisa
                                Terdokumentasi</div>
                        </div>
                        <div class="bg-ink-700 hidden h-10 w-px sm:block"></div>
                        <div>
                            <div class="font-display text-ink-50 text-3xl"><span class="tabular-nums"
                                    data-counter="{{ $record['closed'] }}" data-decimals="0"
                                    data-suffix="">0</span></div>
                            <div class="text-ink-300 mt-1 font-mono text-[10px] uppercase tracking-[0.2em]">Selesai &amp;
                                Diarsipkan</div>
                        </div>
                        <div class="bg-ink-700 hidden h-10 w-px sm:block"></div>
                        <div>
                            <div class="font-display text-ink-50 text-3xl">0</div>
                            <div class="text-ink-300 mt-1 font-mono text-[10px] uppercase tracking-[0.2em]">Hasil
                                Disembunyikan</div>
                        </div>
                    </div>
                </div>

                <div class="animate-tilt-in lg:col-span-5" style="perspective: 1200px; transform-style: preserve-3d;">
                    <div class="relative">
                        <div
                            class="from-gold-400/15 absolute -inset-2 rounded-3xl bg-gradient-to-br via-transparent to-emerald-400/10 blur-2xl">
                        </div>
                        <div class="border-gold-500/20 bg-ink-900/80 relative overflow-hidden rounded-3xl border p-6 shadow-[0_30px_80px_-20px_rgba(0,0,0,0.6)] backdrop-blur-xl"
                            data-landing-featured-analysis
                            data-featured-endpoint="{{ route('landing.featured-signal') }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div data-featured-live-dot
                                        class="{{ $featuredIsLive ? 'animate-pulse-soft h-2.5 w-2.5 rounded-full bg-emerald-400 shadow-[0_0_12px_2px_rgba(47,199,124,0.6)]' : 'h-2.5 w-2.5 rounded-full bg-gold-400 shadow-[0_0_12px_2px_rgba(23,209,131,0.5)]' }}">
                                    </div>
                                    <span data-featured-live-label
                                        class="font-mono text-xs uppercase tracking-widest {{ $featuredIsLive ? 'text-emerald-300' : 'text-gold-200' }}">{{ $featured['live_label'] ?? 'Analisa' }}</span>
                                </div>
                                <span class="text-ink-300 font-mono text-[10px]"><span
                                        data-featured-symbol>{{ $featured['symbol'] }}</span> - <span
                                        data-featured-timeframe>{{ $featured['timeframe'] }}</span></span>
                            </div>

                            <div class="mt-6 flex items-end justify-between gap-4">
                                <div>
                                    <div class="text-ink-300 font-mono text-[10px] uppercase tracking-[0.2em]"
                                        data-featured-status-meta>{{ $featured['status_meta'] }}</div>
                                    <div class="font-display text-ink-50 mt-1 text-2xl" data-featured-title>
                                        {{ $featured['title'] }}</div>
                                </div>
                                <span
                                    class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-widest text-emerald-300"
                                    data-featured-side>{{ $featured['side'] }}</span>
                            </div>

                            <div class="mt-6 h-28 w-full">
                                <svg class="h-full w-full" viewBox="0 0 320 110">
                                    <defs>
                                        <linearGradient id="hero-card-fill" x1="0" y1="0"
                                            x2="0" y2="1">
                                            <stop offset="0%" stop-color="#17d183" stop-opacity="0.4" />
                                            <stop offset="100%" stop-color="#17d183" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <path
                                        d="M0,80 L40,72 L80,82 L120,60 L160,68 L200,42 L240,50 L280,28 L320,18 L320,110 L0,110 Z"
                                        fill="url(#hero-card-fill)" />
                                    <path d="M0,80 L40,72 L80,82 L120,60 L160,68 L200,42 L240,50 L280,28 L320,18"
                                        stroke="#c9f7e0" stroke-width="1.5" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    @foreach ([80, 60, 40, 24] as $y)
                                        <line x1="0" x2="320" y1="{{ $y }}"
                                            y2="{{ $y }}" stroke="rgba(47,199,124,0.18)"
                                            stroke-dasharray="3 5" />
                                    @endforeach
                                    <circle class="animate-pulse-soft" cx="320" cy="18" r="3.5"
                                        fill="#c9f7e0" />
                                </svg>
                            </div>

                            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="border-ink-700 bg-ink-800/50 text-ink-100 min-w-0 rounded-2xl border p-3">
                                    <div class="text-ink-300 font-mono text-[9px] uppercase tracking-[0.2em]">Entry
                                    </div>
                                    <div class="mt-1 min-w-0 break-words font-mono text-sm leading-snug sm:text-[15px]"
                                        data-featured-entry>{{ $featured['entry'] }}</div>
                                </div>
                                <div
                                    class="bg-ink-800/50 min-w-0 rounded-2xl border border-emerald-500/25 p-3 text-emerald-300">
                                    <div class="text-ink-300 font-mono text-[9px] uppercase tracking-[0.2em]">Target 1
                                    </div>
                                    <div class="mt-1 min-w-0 break-words font-mono text-sm leading-snug sm:text-[15px]"
                                        data-featured-tp1>{{ $featured['tp1'] }}</div>
                                </div>
                                <div
                                    class="bg-ink-800/50 min-w-0 rounded-2xl border border-rose-500/25 p-3 text-rose-300">
                                    <div class="text-ink-300 font-mono text-[9px] uppercase tracking-[0.2em]">
                                        Invalidasi</div>
                                    <div class="mt-1 min-w-0 break-words font-mono text-sm leading-snug sm:text-[15px]"
                                        data-featured-sl>{{ $featured['sl'] }}</div>
                                </div>
                            </div>

                            <div class="border-ink-700/60 mt-6 flex items-center justify-between gap-4 border-t pt-4">
                                <div>
                                    <div class="text-ink-300 font-mono text-[10px] uppercase tracking-[0.2em]">Risk :
                                        Reward</div>
                                    <div class="font-display text-gold-200 text-xl" data-featured-rr>
                                        {{ $featured['rr'] ?? '-' }}</div>
                                </div>
                                <p class="text-ink-300 max-w-[11rem] text-right text-[10px] leading-relaxed">
                                    Riset &amp; edukasi &mdash; bukan ajakan membeli atau menjual.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="animate-pulse-soft pointer-events-none absolute bottom-6 left-1/2 hidden -translate-x-1/2 lg:block">
                <div class="text-ink-300 flex flex-col items-center gap-2">
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em]">Scroll</span>
                    <div class="bg-ink-700 relative h-10 w-px overflow-hidden">
                        <div
                            class="via-gold-400 animate-scroll-line absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-transparent to-transparent">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-ink-700/60 bg-ink-900/80 relative border-y backdrop-blur-md">
            <div
                class="from-ink-900 pointer-events-none absolute inset-y-0 left-0 z-10 w-32 bg-gradient-to-r to-transparent">
            </div>
            <div
                class="from-ink-900 pointer-events-none absolute inset-y-0 right-0 z-10 w-32 bg-gradient-to-l to-transparent">
            </div>
            <div class="overflow-hidden py-4" data-landing-market-ticker
                data-market-endpoint="{{ route('landing.market-tickers') }}"
                data-market-tickers="{{ collect($tickers)->pluck('symbol')->implode(',') }}">
                <div class="animate-ticker flex w-max gap-10 whitespace-nowrap">
                    @foreach (array_merge($tickers, $tickers) as $ticker)
                        <div class="flex items-center gap-3" data-market-symbol="{{ $ticker['symbol'] }}">
                            <span
                                class="text-ink-300 font-mono text-[10px] uppercase tracking-[0.2em]">{{ $ticker['symbol'] }}</span>
                            <span class="text-ink-100 font-mono text-sm"
                                data-market-price>{{ $ticker['price'] }}</span>
                            <span class="{{ $ticker['up'] ? 'text-emerald-300' : 'text-rose-300' }} font-mono text-xs"
                                data-market-change>{{ $ticker['change'] }}</span>
                            <span class="text-gold-700/60">-</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-deferred relative scroll-mt-24 py-32 sm:py-40" id="metodologi">
            <div class="mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <div class="reveal inline-flex items-center gap-3">
                        <span class="bg-gold-500/60 h-px w-10"></span>
                        <span class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.3em]">Metodologi</span>
                    </div>
                    <h2 class="reveal font-display text-ink-50 mt-5 text-4xl leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl"
                        style="--reveal-delay: 100ms;">
                        Cara hedge fund bekerja,
                        <br>
                        <span class="text-gold-gradient italic">diterapkan ke crypto.</span>
                    </h2>
                    <p class="reveal text-ink-200 mt-6 max-w-2xl text-base leading-relaxed"
                        style="--reveal-delay: 200ms;">Empat tahap yang dilewati setiap outlook sebelum sampai ke kamu.
                        Prosesnya membosankan, disiplin, dan bisa diaudit &mdash; memang begitu seharusnya riset.</p>
                </div>

                <div class="mt-20 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    @foreach ($steps as $index => $step)
                        <article
                            class="reveal border-ink-700/60 from-ink-800/40 to-ink-900 hover:border-gold-500/30 group relative overflow-hidden rounded-2xl border bg-gradient-to-br p-8 transition-all"
                            style="--reveal-delay: {{ $index * 100 }}ms;">
                            <div
                                class="font-display text-ink-700/60 group-hover:text-gold-700/30 pointer-events-none absolute -right-4 -top-8 select-none text-[10rem] leading-none transition-colors">
                                {{ $step['no'] }}</div>
                            <div class="relative">
                                <div class="inline-flex items-center gap-3">
                                    <span class="bg-gold-500/60 h-px w-8"></span>
                                    <span class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.3em]">Tahap
                                        {{ $step['no'] }}</span>
                                </div>
                                <h3 class="font-display text-ink-50 mt-5 text-3xl">{{ $step['title'] }}</h3>
                                <p class="text-ink-200 mt-3 max-w-md text-sm leading-relaxed">{{ $step['desc'] }}</p>
                                <div class="mt-8 flex items-center gap-2">
                                    @foreach ($steps as $railIndex => $rail)
                                        <span
                                            class="{{ $railIndex <= $index ? 'bg-gold-400/70' : 'bg-ink-700' }} h-1 flex-1 rounded-full"></span>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="reveal border-ink-700/60 bg-ink-900/70 mt-12 flex flex-col items-center justify-between gap-4 rounded-2xl border p-6 backdrop-blur-md sm:flex-row"
                    style="--reveal-delay: 240ms;">
                    @foreach ($principles as $index => $principle)
                        <div class="flex items-center gap-3">
                            <span
                                class="border-gold-500/30 bg-gold-500/10 text-gold-200 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border font-mono text-[10px]">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-ink-100 font-mono text-[11px] uppercase tracking-[0.18em]">{{ $principle }}</span>
                        </div>
                        @if (! $loop->last)
                            <span class="bg-ink-700 hidden h-8 w-px sm:block"></span>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-deferred relative scroll-mt-24 py-32 sm:py-40" id="sistem">
            <div class="mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <div class="reveal inline-flex items-center gap-3">
                        <span class="bg-gold-500/60 h-px w-10"></span>
                        <span class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.3em]">Sistem</span>
                    </div>
                    <h2 class="reveal font-display text-ink-50 mt-5 text-4xl leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl"
                        style="--reveal-delay: 100ms;">
                        Bukan sekadar charting tool.
                        <br>
                        <span class="text-gold-gradient italic">Ini meja riset yang hidup.</span>
                    </h2>
                    <p class="reveal text-ink-200 mt-6 max-w-2xl text-base leading-relaxed"
                        style="--reveal-delay: 200ms;">Enam pilar FibPath Analyzer &mdash; dari pemetaan pola sampai
                        arsip hasil. Semuanya kamu lihat sendiri di dashboard, bukan hanya di brosur.</p>
                </div>

                <div
                    class="border-ink-700/60 bg-ink-700/40 mt-20 grid grid-cols-1 gap-px overflow-hidden rounded-3xl border sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as $index => $feature)
                        <article
                            class="reveal bg-ink-900/90 hover:bg-ink-800/60 group relative overflow-hidden p-8 transition-colors"
                            style="--reveal-delay: {{ $index * 60 }}ms;">
                            <div
                                class="{{ $feature['accent'] === 'gold' ? 'from-gold-500/20' : 'from-emerald-500/20' }} pointer-events-none absolute inset-0 -translate-y-full bg-gradient-to-b via-transparent to-transparent transition-transform duration-700 group-hover:translate-y-0">
                            </div>
                            <div class="relative">
                                <div
                                    class="border-ink-700 bg-ink-800/80 group-hover:border-gold-500/40 flex h-12 w-12 items-center justify-center rounded-xl border transition-all">
                                    <x-icon
                                        class="{{ $feature['accent'] === 'gold' ? 'text-gold-300 group-hover:text-gold-200' : 'text-emerald-300 group-hover:text-emerald-200' }} h-5 w-5"
                                        :name="$feature['icon']" />
                                </div>
                                <h3 class="font-display text-ink-50 mt-6 text-2xl">{{ $feature['title'] }}</h3>
                                <p class="text-ink-200 mt-3 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                                <div
                                    class="text-ink-300 mt-6 flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.25em]">
                                    <span>Modul {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="from-ink-700 h-px flex-1 bg-gradient-to-r to-transparent"></span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section
            class="landing-deferred border-ink-700/60 from-ink-900 via-ink-800/40 to-ink-900 relative scroll-mt-24 border-y bg-gradient-to-b py-28"
            id="transparansi">
            <div class="pointer-events-none absolute inset-0 opacity-[0.04]" data-parallax="0.08">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="stripe" width="6" height="6" patternUnits="userSpaceOnUse">
                            <path d="M 0 6 L 6 0" stroke="#17d183" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#stripe)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <div class="reveal inline-flex items-center gap-3">
                        <span class="bg-gold-500/60 h-px w-10"></span>
                        <span class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.3em]">Transparansi</span>
                    </div>
                    <h2 class="reveal font-display text-ink-50 mt-5 text-4xl leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl"
                        style="--reveal-delay: 100ms;">
                        Kami arsipkan semuanya.
                        <br>
                        <span class="text-emerald-gradient italic">Termasuk yang salah.</span>
                    </h2>
                    <p class="reveal text-ink-200 mt-6 max-w-2xl text-base leading-relaxed"
                        style="--reveal-delay: 200ms;">Tidak ada track record yang dipilih-pilih dan tidak ada janji
                        keuntungan. Analisa yang kena stop tampil di arsip yang sama dengan yang kena target &mdash;
                        supaya kamu bisa menilai metodenya sendiri, dari data.</p>
                </div>

                <div
                    class="border-ink-700/60 bg-ink-700/40 mt-16 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border lg:grid-cols-4">
                    @foreach ([
        ['label' => 'Total Analisa Tercatat', 'value' => $record['total'], 'tone' => 'text-ink-50'],
        ['label' => 'Sedang Berjalan', 'value' => $record['active'], 'tone' => 'text-ink-50'],
        ['label' => 'Selesai Kena Target', 'value' => $record['hitTp'], 'tone' => 'text-emerald-200'],
        ['label' => 'Selesai Kena Stop', 'value' => $record['hitSl'], 'tone' => 'text-rose-200'],
    ] as $index => $stat)
                        <article
                            class="reveal bg-ink-900 hover:bg-ink-800/40 group relative overflow-hidden p-8 transition-colors"
                            style="--reveal-delay: {{ $index * 80 }}ms;">
                            <div class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.25em]">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="font-display {{ $stat['tone'] }} mt-6 text-5xl lg:text-6xl">
                                <span class="tabular-nums" data-counter="{{ $stat['value'] }}" data-decimals="0"
                                    data-suffix="">0</span>
                            </div>
                            <div class="text-ink-300 mt-4 text-xs uppercase tracking-[0.18em]">{{ $stat['label'] }}
                            </div>
                            <div class="from-gold-500/40 via-ink-700 mt-6 h-px bg-gradient-to-r to-transparent"></div>
                        </article>
                    @endforeach
                </div>

                <div class="reveal border-ink-700/60 bg-ink-900/70 mt-8 flex flex-col items-start justify-between gap-5 rounded-2xl border p-6 backdrop-blur-md sm:flex-row sm:items-center"
                    style="--reveal-delay: 240ms;">
                    <div class="flex items-start gap-3">
                        <span
                            class="border-gold-500/30 bg-gold-500/10 text-gold-200 mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border">
                            <x-icon class="h-4 w-4" name="database" />
                        </span>
                        <p class="text-ink-200 max-w-2xl text-sm leading-relaxed">Angka di atas dihitung langsung dari
                            database yang sama dengan dashboard member &mdash; bukan angka marketing. Arsip lengkapnya,
                            termasuk chart yang membeku di titik akhir, terbuka gratis untuk semua member.</p>
                    </div>
                    <a class="border-gold-500/40 bg-gold-500/10 text-gold-100 hover:border-gold-400/70 hover:bg-gold-500/15 inline-flex shrink-0 items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-medium transition-all"
                        href="{{ route('register') }}">
                        Daftar Gratis &amp; Buka Arsip
                        <x-icon class="h-4 w-4" name="arrow-right" />
                    </a>
                </div>
            </div>
        </section>

        <section
            class="landing-deferred border-ink-700/60 from-ink-900 via-ink-800/30 to-ink-900 relative scroll-mt-24 overflow-hidden border-y bg-gradient-to-b py-28 sm:py-36"
            id="harga">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]" data-parallax="0.06">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="pricing-grid" width="48" height="48" patternUnits="userSpaceOnUse">
                            <path d="M 48 0 L 0 0 0 48" fill="none" stroke="#17d183" stroke-width="0.6" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#pricing-grid)" />
                </svg>
            </div>
            <div class="bg-gold-500/10 pointer-events-none absolute -left-32 top-1/3 h-72 w-72 rounded-full blur-3xl"
                data-parallax="-0.15">
            </div>
            <div class="pointer-events-none absolute -right-32 bottom-1/4 h-80 w-80 rounded-full bg-emerald-500/10 blur-3xl"
                data-parallax="0.12">
            </div>

            <div class="relative mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <div class="reveal inline-flex items-center gap-3">
                        <span class="bg-gold-500/60 h-px w-10"></span>
                        <span class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.3em]">Harga</span>
                    </div>
                    <h2 class="reveal font-display text-ink-50 mt-5 text-4xl leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl"
                        style="--reveal-delay: 100ms;">
                        Harga yang jujur.
                        <br>
                        <span class="text-gold-gradient italic">Tanpa gimmick.</span>
                    </h2>
                    <p class="reveal text-ink-200 mt-6 max-w-2xl text-base leading-relaxed"
                        style="--reveal-delay: 200ms;">Kamu membayar akses ke riset dan edukasi &mdash; bukan janji
                        keuntungan. Pilih cara akses yang paling cocok dengan ritme kamu.</p>
                </div>

                <div class="mt-16 grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
                    <article
                        class="reveal border-gold-500/40 from-ink-800/90 via-ink-900 to-ink-900 hover:border-gold-400/60 group relative overflow-hidden rounded-3xl border bg-gradient-to-br p-8 shadow-[0_30px_80px_-30px_rgba(23,209,131,0.4)] transition-all hover:shadow-[0_40px_100px_-30px_rgba(23,209,131,0.55)] sm:p-10"
                        style="--reveal-delay: 100ms;">
                        <div
                            class="bg-gold-500/20 pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full blur-3xl">
                        </div>
                        <div
                            class="via-gold-400 pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent to-transparent">
                        </div>

                        <div class="relative flex items-center justify-between">
                            <div
                                class="border-gold-500/40 bg-gold-500/10 inline-flex items-center gap-2 rounded-full border px-3 py-1">
                                <x-icon class="text-gold-200 h-3.5 w-3.5" name="shield-check" />
                                <span
                                    class="text-gold-200 font-mono text-[10px] uppercase tracking-[0.25em]">Langganan</span>
                            </div>
                            <span
                                class="from-gold-300 to-gold-500 text-ink-900 rounded-full bg-gradient-to-b px-3 py-1 font-mono text-[10px] uppercase tracking-[0.22em] shadow-[0_8px_24px_-8px_rgba(23,209,131,0.8)]">Paling
                                Hemat</span>
                        </div>

                        <div class="relative mt-7">
                            <h3 class="font-display text-ink-50 text-3xl sm:text-4xl">Akses Riset Bulanan</h3>
                            <p class="text-ink-200 mt-3 max-w-md text-sm leading-relaxed">Semua analisa aktif terbuka
                                selama masa langganan, lengkap dengan chart live dan alert level.</p>
                        </div>

                        <div class="relative mt-7 flex items-end gap-3">
                            <span class="font-display text-ink-50 text-6xl sm:text-7xl">Rp&nbsp;25rb</span>
                            <span class="text-ink-300 mb-2 font-mono text-xs uppercase tracking-[0.25em]">/ bulan /
                                user</span>
                        </div>
                        <div
                            class="relative mt-2 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1">
                            <x-icon class="h-3.5 w-3.5 text-emerald-300" name="check-circle" />
                            <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-emerald-200">Tanpa
                                auto-renew &middot; Berhenti kapan saja</span>
                        </div>

                        <ul class="relative mt-8 space-y-3">
                            @foreach (['Semua analisa aktif terbuka selama masa langganan', 'Alert WhatsApp saat level target / invalidasi tersentuh', 'Chart live untuk setiap analisa', 'Arsip lengkap - kena target maupun kena stop', 'Materi edukasi & framework risiko'] as $perk)
                                <li class="text-ink-100 flex items-start gap-3 text-sm">
                                    <span
                                        class="border-gold-500/30 bg-gold-500/10 mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full border">
                                        <x-icon class="text-gold-200 h-3 w-3" name="check-circle" />
                                    </span>
                                    <span class="leading-relaxed">{{ $perk }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a class="from-gold-300 to-gold-500 text-ink-900 relative mt-9 inline-flex w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-gradient-to-b px-7 py-3.5 text-sm font-semibold shadow-[0_18px_60px_-15px_rgba(23,209,131,0.7)] transition-all hover:shadow-[0_24px_80px_-15px_rgba(23,209,131,0.9)]"
                            href="{{ $checkoutHref }}">
                            <span class="relative z-10">Mulai Langganan</span>
                            <x-icon
                                class="relative z-10 h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5"
                                name="arrow-up-right" />
                            <span
                                class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/35 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                        </a>

                        <p
                            class="text-ink-300 relative mt-4 text-center font-mono text-[10px] uppercase tracking-[0.22em]">
                            Akses konten riset &amp; edukasi &middot; Bukan produk investasi</p>
                    </article>

                    <article
                        class="reveal border-ink-700/60 bg-ink-900/85 group relative overflow-hidden rounded-3xl border p-8 backdrop-blur-xl transition-all hover:border-emerald-400/40 sm:p-10"
                        style="--reveal-delay: 200ms;">
                        <div
                            class="pointer-events-none absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-emerald-500/15 blur-3xl">
                        </div>

                        <div class="relative flex items-center justify-between">
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1">
                                <x-icon class="h-3.5 w-3.5 text-emerald-300" name="wallet" />
                                <span
                                    class="font-mono text-[10px] uppercase tracking-[0.25em] text-emerald-200">Pay-per-Analisa</span>
                            </div>
                            <span
                                class="border-ink-600/70 bg-ink-800/60 text-ink-200 rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.22em]">Fleksibel</span>
                        </div>

                        <div class="relative mt-7">
                            <h3 class="font-display text-ink-50 text-3xl sm:text-4xl">Token per Analisa</h3>
                            <p class="text-ink-200 mt-3 max-w-md text-sm leading-relaxed">Pilih hanya analisa yang mau
                                kamu baca. Bayar sekali, terbuka selamanya &mdash; cocok untuk menilai metodenya dulu
                                sebelum berlangganan.</p>
                        </div>

                        <div class="relative mt-7 flex items-end gap-3">
                            <span class="font-display text-ink-50 text-6xl sm:text-7xl">Mulai 1rb</span>
                            <span class="text-ink-300 mb-2 font-mono text-xs uppercase tracking-[0.25em]">/
                                analisa</span>
                        </div>
                        <p class="text-ink-300 relative mt-2 font-mono text-[11px] uppercase tracking-[0.18em]">Biaya
                            mengikuti kedalaman riset &mdash; bukan janji hasil</p>

                        <div class="relative mt-7 space-y-2.5">
                            @foreach ($tokenTiers as $tier)
                                <div
                                    class="border-ink-700/60 bg-ink-800/40 group-hover:border-ink-600/80 flex items-center gap-4 rounded-2xl border p-3 transition-colors">
                                    <div
                                        class="font-display flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-500/25 bg-emerald-500/10 text-sm text-emerald-200">
                                        {{ $tier['cost'] }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-display text-ink-50 text-sm">{{ $tier['label'] }}</span>
                                            <span
                                                class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.18em]">{{ $tier['range'] }}</span>
                                        </div>
                                        <div class="text-ink-300 mt-0.5 truncate text-xs">{{ $tier['desc'] }}</div>
                                    </div>
                                    <span
                                        class="text-ink-300 shrink-0 font-mono text-[10px] uppercase tracking-[0.2em]">{{ $tier['cost'] }}
                                        token</span>
                                </div>
                            @endforeach
                        </div>

                        <a class="relative mt-8 inline-flex w-full items-center justify-center gap-2 overflow-hidden rounded-full border border-emerald-400/40 bg-emerald-500/10 px-7 py-3.5 text-sm font-semibold text-emerald-100 transition-all hover:border-emerald-300/70 hover:bg-emerald-500/20"
                            href="{{ $checkoutHref }}">
                            <span class="relative z-10">Beli Token</span>
                            <x-icon
                                class="relative z-10 h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5"
                                name="arrow-up-right" />
                        </a>

                        <p
                            class="text-ink-300 relative mt-4 text-center font-mono text-[10px] uppercase tracking-[0.22em]">
                            Bayar sekali &middot; Analisa terbuka selamanya</p>
                    </article>
                </div>

                <div class="reveal border-ink-700/60 bg-ink-900/70 mt-16 rounded-3xl border p-6 backdrop-blur-xl sm:p-8"
                    style="--reveal-delay: 240ms;">
                    <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <div class="inline-flex items-center gap-3">
                                <span class="bg-gold-500/60 h-px w-8"></span>
                                <span class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.3em]">Paket
                                    Token</span>
                            </div>
                            <h3 class="font-display text-ink-50 mt-3 text-2xl sm:text-3xl">Beli sekali, pakai kapan
                                saja</h3>
                            <p class="text-ink-300 mt-2 max-w-xl text-sm leading-relaxed">Harga per token lebih rendah
                                di paket besar. Token tidak punya masa berlaku.</p>
                        </div>
                        <span
                            class="border-gold-500/25 bg-gold-500/10 text-gold-200 inline-flex items-center gap-2 rounded-full border px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em]">
                            <x-icon class="h-3 w-3" name="sparkles" />
                            1 token = 1.000
                        </span>
                    </div>

                    <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($tokenPacks as $pack)
                            @php $featuredPack = $pack['badge'] === 'Termurah'; @endphp
                            <div
                                class="{{ $featuredPack ? 'border-gold-500/40 bg-gradient-to-b from-gold-500/10 to-transparent' : 'border-ink-700/70 bg-ink-800/40' }} hover:border-gold-500/40 hover:bg-ink-800/60 group relative overflow-hidden rounded-2xl border p-5 transition-all">
                                @if ($pack['badge'])
                                    <span
                                        class="{{ $featuredPack ? 'bg-gradient-to-b from-gold-300 to-gold-500 text-ink-900' : 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-200' }} absolute right-3 top-3 rounded-full px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.2em]">{{ $pack['badge'] }}</span>
                                @endif

                                <div class="text-ink-300 font-mono text-[10px] uppercase tracking-[0.22em]">
                                    {{ $pack['amount'] }} Token</div>
                                <div class="mt-3 flex items-baseline gap-2">
                                    <span class="font-display text-ink-50 text-3xl">Rp {{ $pack['price'] }}</span>
                                    @if ($pack['discount'])
                                        <span
                                            class="font-mono text-[10px] uppercase tracking-[0.2em] text-emerald-300">{{ $pack['discount'] }}</span>
                                    @endif
                                </div>
                                <div class="text-ink-300 mt-1 font-mono text-[10px] uppercase tracking-[0.2em]">@ Rp
                                    {{ $pack['unit'] }} / token</div>

                                <div class="from-gold-500/40 via-ink-700 mt-5 h-px bg-gradient-to-r to-transparent">
                                </div>
                                <div
                                    class="mt-4 flex items-center justify-between font-mono text-[10px] uppercase tracking-[0.2em]">
                                    <span class="text-ink-300">~{{ floor($pack['amount']) }} analisa harian</span>
                                    <span class="text-gold-200 transition-transform group-hover:translate-x-0.5">Pilih
                                        &rarr;</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="mt-7 grid grid-cols-1 gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5 sm:grid-cols-3">
                        <div class="flex items-start gap-3">
                            <span
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                                <x-icon class="h-4 w-4" name="check-circle" />
                            </span>
                            <div>
                                <div class="font-display text-ink-50 text-sm">Tanpa Expired</div>
                                <p class="text-ink-300 mt-1 text-xs leading-relaxed">Token kamu tetap valid sampai
                                    dipakai semua.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                                <x-icon class="h-4 w-4" name="shield-check" />
                            </span>
                            <div>
                                <div class="font-display text-ink-50 text-sm">Akses Selamanya</div>
                                <p class="text-ink-300 mt-1 text-xs leading-relaxed">Analisa yang sudah dibuka tetap
                                    bisa dibaca, walau risetnya sudah selesai.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                                <x-icon class="h-4 w-4" name="shield-check" />
                            </span>
                            <div>
                                <div class="font-display text-ink-50 text-sm">Keputusan di Tangan Kamu</div>
                                <p class="text-ink-300 mt-1 text-xs leading-relaxed">Analisa adalah opini riset dengan
                                    level yang jelas &mdash; bukan perintah beli atau jual.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="reveal mt-12 flex flex-col items-center justify-center gap-3 text-center sm:flex-row sm:gap-6"
                    style="--reveal-delay: 320ms;">
                    <span class="text-ink-300 font-mono text-[10px] uppercase tracking-[0.3em]">Bingung pilih?</span>
                    <span class="bg-ink-700 hidden h-px w-12 sm:block"></span>
                    <span class="text-ink-200 text-sm">Baca riset tiap hari? Langganan. Sesekali saja? Token.</span>
                </div>
            </div>
        </section>

        <section class="landing-deferred relative scroll-mt-24 py-28 sm:py-32" id="disclaimer">
            <div class="mx-auto max-w-7xl px-6">
                <div class="border-ink-700/60 from-ink-800/60 to-ink-900 relative overflow-hidden rounded-3xl border bg-gradient-to-br p-8 sm:p-12">
                    <div
                        class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-rose-500/10 blur-3xl">
                    </div>
                    <div class="relative grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
                        <div>
                            <div class="reveal inline-flex items-center gap-3">
                                <span class="h-px w-10 bg-rose-500/60"></span>
                                <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-rose-300">Disclaimer</span>
                            </div>
                            <h2 class="reveal font-display text-ink-50 mt-5 text-4xl leading-[1.1] tracking-tight sm:text-5xl"
                                style="--reveal-delay: 100ms;">
                                Jujur soal risiko,
                                <br>
                                <span class="text-gold-gradient italic">sejak halaman pertama.</span>
                            </h2>
                            <p class="reveal text-ink-200 mt-6 text-base leading-relaxed"
                                style="--reveal-delay: 200ms;">Kami menjual riset, bukan mimpi. Sebelum kamu membaca
                                satu analisa pun, pahami dulu lima hal ini.</p>
                            <a class="reveal border-ink-500/40 bg-ink-800/40 text-ink-100 hover:border-gold-500/40 hover:text-gold-100 mt-8 inline-flex items-center gap-2 rounded-full border px-6 py-3 text-sm font-medium backdrop-blur-md transition-all"
                                style="--reveal-delay: 260ms;" href="{{ route('legal.disclaimer') }}">
                                Baca Disclaimer Lengkap
                                <x-icon class="h-4 w-4" name="arrow-right" />
                            </a>
                        </div>
                        <ul class="space-y-4">
                            @foreach ($disclaimers as $index => $item)
                                <li class="reveal border-ink-700/60 bg-ink-900/70 flex items-start gap-4 rounded-2xl border p-5 backdrop-blur-md"
                                    style="--reveal-delay: {{ 100 + $index * 70 }}ms;">
                                    <span
                                        class="border-gold-500/30 bg-gold-500/10 text-gold-200 mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border font-mono text-[10px]">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div>
                                        <div class="font-display text-ink-50 text-lg">{{ $item['title'] }}</div>
                                        <p class="text-ink-300 mt-1 text-sm leading-relaxed">{{ $item['desc'] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-deferred relative scroll-mt-24 overflow-hidden py-32 sm:py-40" id="cta">
            <div class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"
                data-parallax="-0.08">
                @foreach ([300, 500, 720, 980] as $size)
                    <div class="border-gold-500/8 absolute -translate-x-1/2 -translate-y-1/2 rounded-full border"
                        style="width: {{ $size }}px; height: {{ $size }}px; left: 0; top: 0;"></div>
                @endforeach
            </div>

            <div class="relative mx-auto max-w-4xl px-6 text-center">
                <div class="reveal inline-flex items-center gap-3">
                    <span class="bg-gold-500/60 h-px w-10"></span>
                    <span class="text-gold-300 font-mono text-[10px] uppercase tracking-[0.3em]">Mulai</span>
                    <span class="bg-gold-500/60 h-px w-10"></span>
                </div>
                <h2 class="reveal font-display text-ink-50 mt-6 text-5xl leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl"
                    style="--reveal-delay: 120ms;">
                    Trade dengan <span class="text-gold-gradient italic">disiplin</span>.
                    <br>
                    Bukan dengan harapan.
                </h2>
                <p class="reveal text-ink-200 mx-auto mt-6 max-w-2xl text-base leading-relaxed"
                    style="--reveal-delay: 220ms;">Masuk ke meja riset Weesia: baca skenarionya, pahami level
                    invalidasinya, lalu ambil keputusan dengan kepala dingin dan risiko yang terukur.</p>
                <div class="reveal mt-12 flex flex-wrap items-center justify-center gap-4"
                    style="--reveal-delay: 320ms;">
                    <a class="from-gold-300 to-gold-500 text-ink-900 group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-gradient-to-b px-8 py-4 text-sm font-semibold shadow-[0_18px_60px_-10px_rgba(23,209,131,0.6)] transition-all hover:shadow-[0_24px_80px_-10px_rgba(23,209,131,0.8)]"
                        href="{{ route('login') }}">
                        <span class="relative z-10">Login Member</span>
                        <x-icon
                            class="relative z-10 h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5"
                            name="arrow-up-right" />
                        <span
                            class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                    </a>
                    <a class="border-ink-500/40 bg-ink-800/40 text-ink-100 hover:border-gold-500/40 inline-flex items-center gap-2 rounded-full border px-8 py-4 text-sm font-medium backdrop-blur-md transition-all"
                        href="#metodologi">Pelajari Metodologi</a>
                </div>
                <div
                    class="reveal text-ink-300 mt-14 flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-6">
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em]">Built by Weesia</span>
                    <span class="bg-ink-700 h-px w-16"></span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em]">Powered by FibPath Analyzer</span>
                </div>
            </div>
        </section>

        <footer class="border-ink-700/60 bg-ink-900 relative border-t">
            <div class="mx-auto max-w-7xl px-6 py-14">
                <div class="flex flex-col items-start justify-between gap-10 md:flex-row md:items-center">
                    <div>
                        <div class="text-ink-50 font-mono text-xl font-semibold uppercase tracking-[0.34em]">Weesia</div>
                        <div class="text-gold-300/80 mt-1 font-mono text-[10px] uppercase tracking-[0.3em]">Riset
                            Market Crypto</div>
                        <p class="text-ink-300 mt-4 max-w-sm text-sm leading-relaxed">Riset market crypto yang
                            terdokumentasi. Disiplin sebelum diskresi, data sebelum opini.</p>
                        <a class="text-ink-200 hover:text-gold-200 mt-4 inline-flex items-center gap-2 text-sm transition-colors"
                            href="mailto:{{ config('app.contact_email') }}">
                            <x-icon class="text-gold-300 h-4 w-4" name="send" />
                            {{ config('app.contact_email') }}
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-12 sm:grid-cols-3">
                        @foreach ($footerColumns as $column)
                            <div>
                                <div class="text-gold-300/80 font-mono text-[10px] uppercase tracking-[0.3em]">
                                    {{ $column['title'] }}</div>
                                <ul class="mt-4 space-y-2">
                                    @foreach ($column['links'] as [$label, $href])
                                        <li><a class="text-ink-200 hover:text-gold-200 text-sm transition-colors"
                                                href="{{ $href }}">{{ $label }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="border-ink-700/60 text-ink-300 mt-14 border-t pt-6 text-xs leading-relaxed">
                    Aset kripto berisiko tinggi dan sangat fluktuatif; kamu bisa kehilangan seluruh modal, dan
                    penggunaan leverage memperbesar risiko tersebut. Seluruh konten Weesia &mdash; termasuk analisa,
                    chart, dan arsip &mdash; adalah riset dan edukasi, bukan nasihat finansial dan bukan ajakan membeli
                    atau menjual aset apa pun. Kinerja masa lalu tidak menjamin hasil di masa depan. Weesia tidak
                    mengelola dana pengguna dalam bentuk apa pun.
                </p>
                <div
                    class="text-ink-300 mt-6 flex flex-col items-start justify-between gap-4 text-xs sm:flex-row sm:items-center">
                    <span class="font-mono uppercase tracking-[0.2em]">&copy; {{ now()->year }} Weesia - All rights
                        reserved</span>
                    <span class="font-mono uppercase tracking-[0.2em]">Riset &amp; Edukasi &mdash; Bukan Nasihat
                        Finansial</span>
                </div>
            </div>
        </footer>
    </main>
</x-layouts.app>
