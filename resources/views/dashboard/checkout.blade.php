<x-layouts.app title="Beli Token / Subscribe - Weesia">
    <x-dashboard-shell active="checkout">
        @php
            $fmtIdr = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
            $tokenUnitBase = 1000;
            $tokenUnit = fn ($pack) => (float) $pack['price'] / max((int) $pack['tokens'], 1);
            $tokenSaving = function ($pack) use ($tokenUnit, $tokenUnitBase) {
                $saving = (int) round((1 - $tokenUnit($pack) / $tokenUnitBase) * 100);

                return $saving > 0 ? $saving : null;
            };
            $firstToken = reset($tokens) ?: ['price' => 0, 'tokens' => 0];
            $tokenTiers = [
                ['label' => 'Analisa Harian', 'desc' => 'Outlook cepat dengan level utama', 'cost' => 1],
                ['label' => 'Swing Setup', 'desc' => 'Skenario menengah dengan konteks lebih dalam', 'cost' => 2],
                ['label' => 'Riset Mendalam', 'desc' => 'Confluence berlapis di timeframe tinggi', 'cost' => 3],
                ['label' => 'Full Riset', 'desc' => 'Skenario lengkap dengan target bertahap', 'cost' => 5],
            ];
        @endphp

        <section class="relative mx-auto max-w-7xl px-4 pb-12 pt-24 sm:px-6 sm:pt-28">
            @if (session('status'))
                <div class="reveal mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="reveal mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <div class="reveal flex flex-col items-start gap-3 border-b border-ink-700/60 pb-8 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Checkout</span>
                    </div>
                    <h1 class="mt-4 font-display text-4xl leading-[1.05] text-ink-50 sm:text-5xl">Pilih Paket Kamu</h1>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-ink-300">Bayar transfer bank atau scan QRIS, upload bukti, admin verifikasi maksimal 1x24 jam.</p>
                </div>
                <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                    <x-icon name="clipboard" class="h-3.5 w-3.5" />
                    Pesanan Saya
                </a>
            </div>

            {{-- ===== Paket langganan ===== --}}
            @foreach ($subscriptions as $key => $sub)
                <article class="reveal relative mt-10 overflow-hidden rounded-3xl border border-gold-500/40 bg-gradient-to-br from-ink-800/90 via-ink-900 to-ink-900 p-6 shadow-[0_30px_80px_-30px_rgba(23,209,131,0.4)] sm:p-9">
                    <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-gold-500/20 blur-3xl"></div>
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-400 to-transparent"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-3 py-1">
                            <x-icon name="shield-check" class="h-3.5 w-3.5 text-gold-200" />
                            <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-gold-200">Subscriber</span>
                        </div>
                        <span class="rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-900 shadow-[0_8px_24px_-8px_rgba(23,209,131,0.8)]">Most Value</span>
                    </div>

                    <div class="relative mt-7 grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,340px)] lg:gap-12">
                        <div class="min-w-0">
                            <h2 class="font-display text-3xl leading-tight text-ink-50 sm:text-4xl">{{ $sub['label'] }}</h2>
                            <p class="mt-3 max-w-xl text-sm leading-relaxed text-ink-200">Akses semua analisa aktif tanpa batas selama {{ $sub['months'] }} bulan - tanpa perlu keluar token per analisa.</p>

                            <div class="mt-6 flex flex-wrap items-end gap-x-3 gap-y-1">
                                <span class="font-display text-5xl leading-none text-ink-50 sm:text-6xl">{{ $fmtIdr($sub['price']) }}</span>
                                <span class="font-mono text-xs uppercase tracking-[0.25em] text-ink-300">/ {{ $sub['months'] }} bulan</span>
                            </div>

                            <ul class="mt-7 grid gap-2.5 text-sm text-ink-100 sm:grid-cols-2">
                                @foreach (['Buka semua analisa aktif tanpa batas', 'Alert WhatsApp realtime', 'Kalkulator P/L live', 'Arsip TP/SL terbuka selamanya'] as $perk)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-gold-500/30 bg-gold-500/10">
                                            <x-icon name="check-circle" class="h-3 w-3 text-gold-200" />
                                        </span>
                                        <span class="leading-relaxed">{{ $perk }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-2xl border border-gold-500/20 bg-ink-900/70 p-5 backdrop-blur-xl">
                            <div class="font-mono text-[10px] uppercase tracking-[0.28em] text-gold-300">Buat pesanan</div>
                            <form method="POST" action="{{ route('checkout.store') }}" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="package_key" value="{{ $key }}">
                                <label class="block">
                                    <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Nomor WhatsApp (untuk notif)</span>
                                    <input name="whatsapp_number" type="tel" placeholder="08xxxxxxxxxx" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none transition-colors focus:border-gold-500/50">
                                </label>
                                <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_18px_60px_-15px_rgba(23,209,131,0.7)] transition-all hover:shadow-[0_24px_80px_-15px_rgba(23,209,131,0.9)]">
                                    Buat Pesanan
                                    <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </button>
                            </form>
                            <p class="mt-4 text-[11px] leading-relaxed text-ink-400">Setelah pesanan dibuat kamu diarahkan ke instruksi pembayaran - transfer bank atau QRIS - lalu tinggal upload bukti.</p>
                        </div>
                    </div>
                </article>
            @endforeach

            {{-- ===== Pemisah ===== --}}
            <div class="reveal mt-10 flex items-center gap-4">
                <span class="h-px flex-1 bg-ink-700/60"></span>
                <span class="shrink-0 font-mono text-[10px] uppercase tracking-[0.3em] text-ink-400">Atau bayar per analisa</span>
                <span class="h-px flex-1 bg-ink-700/60"></span>
            </div>

            {{-- ===== Paket token ===== --}}
            <article data-token-picker class="reveal relative mt-10 overflow-hidden rounded-3xl border border-ink-700/60 bg-ink-900/85 p-6 backdrop-blur-xl sm:p-9">
                <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-emerald-500/15 blur-3xl"></div>

                <div class="relative flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1">
                        <x-icon name="wallet" class="h-3.5 w-3.5 text-emerald-300" />
                        <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-emerald-200">Token Pack</span>
                    </div>
                    <span class="rounded-full border border-ink-600/70 bg-ink-800/60 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200">Pay per analisa</span>
                </div>

                <div class="relative mt-7">
                    <h2 class="font-display text-3xl leading-tight text-ink-50 sm:text-4xl">Beli Token</h2>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-ink-200">1 token = {{ $fmtIdr($tokenUnitBase) }}. Bayar hanya untuk analisa yang kamu buka - makin besar paketnya, makin murah harga per tokennya.</p>
                </div>

                <form method="POST" action="{{ route('checkout.store') }}" class="relative mt-7">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4" role="radiogroup" aria-label="Pilih paket token">
                        @foreach ($tokens as $key => $pack)
                            @php $saving = $tokenSaving($pack); @endphp
                            <label class="group relative block cursor-pointer">
                                <input type="radio" name="package_key" value="{{ $key }}" class="peer sr-only"
                                       data-token-price="{{ $fmtIdr($pack['price']) }}"
                                       data-token-amount="{{ $pack['tokens'] }}"
                                       data-token-unit="{{ $fmtIdr($tokenUnit($pack)) }}"
                                       @checked($loop->first)>
                                <span aria-hidden="true" class="pointer-events-none absolute left-4 top-4 z-10 inline-flex h-4 w-4 items-center justify-center rounded-full border border-ink-600 bg-ink-900/80 text-transparent transition-colors group-hover:border-ink-500 peer-checked:border-emerald-400 peer-checked:text-emerald-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                </span>
                                <span class="relative flex h-full flex-col gap-1 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4 pt-12 transition-all hover:border-ink-600 peer-checked:border-emerald-400/60 peer-checked:bg-emerald-500/10 peer-checked:shadow-[0_15px_50px_-25px_rgba(52,211,153,0.6)] peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400/60">
                                    @if ($pack['badge'])
                                        <span class="absolute right-4 top-3.5 rounded-full {{ $pack['badge'] === 'Best Deal' ? 'bg-gradient-to-b from-gold-300 to-gold-500 text-ink-900' : 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-200' }} px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.2em]">{{ $pack['badge'] }}</span>
                                    @endif
                                    <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">{{ $pack['tokens'] }} Token</span>
                                    <span class="font-display text-2xl text-ink-50">{{ $fmtIdr($pack['price']) }}</span>
                                    <span class="mt-auto flex flex-wrap items-center gap-2 pt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">
                                        <span>@ {{ $fmtIdr($tokenUnit($pack)) }} / token</span>
                                        @if ($saving)
                                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-emerald-200">Hemat {{ $saving }}%</span>
                                        @endif
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-5 grid gap-5 rounded-2xl border border-emerald-500/20 bg-ink-900/70 p-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,340px)] lg:items-end lg:gap-8">
                        <div class="min-w-0">
                            <div class="font-mono text-[10px] uppercase tracking-[0.28em] text-emerald-300">Total pesanan</div>
                            <div class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-2">
                                <span data-token-summary-price class="font-display text-4xl leading-none text-ink-50 sm:text-5xl">{{ $fmtIdr($firstToken['price']) }}</span>
                                <span data-token-summary-amount class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-0.5 font-mono text-[10px] uppercase tracking-[0.2em] text-emerald-200">{{ $firstToken['tokens'] }} token</span>
                                <span data-token-summary-unit class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400">@ {{ $fmtIdr($tokenUnit($firstToken)) }} / token</span>
                            </div>
                            <p class="mt-3 text-[11px] leading-relaxed text-ink-400">Token tidak punya masa kedaluwarsa. Sisa token tetap ada di saldo kamu.</p>
                        </div>

                        <div class="space-y-3">
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Nomor WhatsApp (untuk notif)</span>
                                <input name="whatsapp_number" type="tel" placeholder="08xxxxxxxxxx" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none transition-colors focus:border-emerald-400/50">
                            </label>
                            <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-500/10 px-6 py-3.5 text-sm font-semibold text-emerald-100 transition-all hover:border-emerald-300/70 hover:bg-emerald-500/20">
                                Buat Pesanan Token
                                <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </button>
                        </div>
                    </div>
                </form>

                <div class="relative mt-8 border-t border-ink-700/60 pt-6">
                    <div class="flex items-center gap-3">
                        <span class="shrink-0 font-mono text-[10px] uppercase tracking-[0.3em] text-ink-400">Token dipakai untuk</span>
                        <span class="h-px flex-1 bg-ink-700/60"></span>
                    </div>
                    <div class="mt-4 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($tokenTiers as $i => $tier)
                            <div class="rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[9px] uppercase tracking-[0.22em] text-ink-400">Tier {{ $i + 1 }}</span>
                                    <span class="rounded-full border border-emerald-500/25 bg-emerald-500/10 px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] text-emerald-200">{{ $tier['cost'] }} token</span>
                                </div>
                                <div class="mt-2 font-display text-base text-ink-50">{{ $tier['label'] }}</div>
                                <div class="mt-1 text-xs leading-relaxed text-ink-300">{{ $tier['desc'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <p class="reveal mt-6 text-xs leading-relaxed text-ink-400">Pembelian token dan langganan adalah akses ke konten riset &amp; edukasi - bukan produk investasi dan bukan jaminan hasil. Baca <a href="{{ route('legal.disclaimer') }}" class="underline text-gold-300 transition-colors hover:text-gold-100">disclaimer</a>.</p>

            @if ($orders->isNotEmpty())
                <div class="reveal mt-12">
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-xl text-ink-50">Pesanan Terakhir</h2>
                        <a href="{{ route('orders.index') }}" class="font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300 transition-colors hover:text-gold-100">Lihat semua &rarr;</a>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-3">
                        @foreach ($orders as $order)
                            @php
                                $tone = match ($order->statusTone()) {
                                    'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                                    'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
                                    'gold' => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
                                    default => 'border-ink-700/70 bg-ink-800/40 text-ink-200',
                                };
                            @endphp
                            <a href="{{ route('orders.show', $order) }}" class="group flex flex-col gap-3 rounded-2xl border border-ink-700/60 bg-ink-900/70 p-4 backdrop-blur-md transition-colors hover:border-gold-500/40 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/25 bg-gold-500/10 text-gold-200">
                                        <x-icon name="{{ $order->type === \App\Models\PaymentOrder::TYPE_TOKEN ? 'wallet' : 'shield-check' }}" class="h-4 w-4" />
                                    </span>
                                    <div>
                                        <div class="font-display text-sm text-ink-50">{{ $order->package_label }}</div>
                                        <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">{{ $order->reference }} - {{ $order->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3 sm:gap-4">
                                    <span class="font-mono text-sm text-ink-100">{{ $fmtIdr($order->total_amount) }}</span>
                                    <span class="rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] {{ $tone }}">{{ $order->statusLabel() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </x-dashboard-shell>
</x-layouts.app>
