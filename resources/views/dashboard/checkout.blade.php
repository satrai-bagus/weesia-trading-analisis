<x-layouts.app title="Beli Token / Subscribe - Weesia">
    <x-dashboard-shell active="checkout">
        @php
            $fmtIdr = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
            $tierTokens = [1, 2, 3, 5];
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

            <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
                @foreach ($subscriptions as $key => $sub)
                    <article class="reveal relative overflow-hidden rounded-3xl border border-gold-500/40 bg-gradient-to-br from-ink-800/90 via-ink-900 to-ink-900 p-7 shadow-[0_30px_80px_-30px_rgba(23,209,131,0.4)] sm:p-9">
                        <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-gold-500/20 blur-3xl"></div>
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-400 to-transparent"></div>
                        <div class="relative flex items-center justify-between">
                            <div class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-3 py-1">
                                <x-icon name="shield-check" class="h-3.5 w-3.5 text-gold-200" />
                                <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-gold-200">Subscriber</span>
                            </div>
                            <span class="rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-900 shadow-[0_8px_24px_-8px_rgba(23,209,131,0.8)]">Most Value</span>
                        </div>
                        <h3 class="relative mt-7 font-display text-3xl text-ink-50 sm:text-4xl">{{ $sub['label'] }}</h3>
                        <p class="relative mt-3 text-sm leading-relaxed text-ink-200">Akses semua analisa aktif tanpa batas selama {{ $sub['months'] }} bulan.</p>

                        <div class="relative mt-6 flex items-end gap-3">
                            <span class="font-display text-5xl text-ink-50 sm:text-6xl">{{ $fmtIdr($sub['price']) }}</span>
                            <span class="mb-2 font-mono text-xs uppercase tracking-[0.25em] text-ink-300">/ {{ $sub['months'] }} bulan</span>
                        </div>

                        <ul class="relative mt-7 space-y-2.5 text-sm text-ink-100">
                            @foreach (['Buka semua analisa aktif tanpa batas', 'Alert WhatsApp realtime', 'Kalkulator P/L live', 'Arsip TP/SL terbuka selamanya'] as $perk)
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-gold-500/30 bg-gold-500/10">
                                        <x-icon name="check-circle" class="h-3 w-3 text-gold-200" />
                                    </span>
                                    <span class="leading-relaxed">{{ $perk }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('checkout.store') }}" class="relative mt-8 space-y-3">
                            @csrf
                            <input type="hidden" name="package_key" value="{{ $key }}">
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Nomor WhatsApp (untuk notif)</span>
                                <input name="whatsapp_number" type="tel" placeholder="08xxxxxxxxxx" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </label>
                            <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_18px_60px_-15px_rgba(23,209,131,0.7)] transition-all hover:shadow-[0_24px_80px_-15px_rgba(23,209,131,0.9)]">
                                Buat Pesanan
                                <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </button>
                        </form>
                    </article>
                @endforeach

                <article class="reveal relative overflow-hidden rounded-3xl border border-ink-700/60 bg-ink-900/85 p-7 backdrop-blur-xl sm:p-9">
                    <div class="pointer-events-none absolute -left-20 -bottom-20 h-56 w-56 rounded-full bg-emerald-500/15 blur-3xl"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1">
                            <x-icon name="wallet" class="h-3.5 w-3.5 text-emerald-300" />
                            <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-emerald-200">Token Pack</span>
                        </div>
                        <span class="rounded-full border border-ink-600/70 bg-ink-800/60 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200">Pay per analisa</span>
                    </div>
                    <h3 class="relative mt-7 font-display text-3xl text-ink-50 sm:text-4xl">Beli Token</h3>
                    <p class="relative mt-3 text-sm leading-relaxed text-ink-200">1 token = Rp 1.000. Pakai untuk buka analisa sesuai tier ROI-nya.</p>

                    <div class="relative mt-6 grid grid-cols-2 gap-2.5 text-[11px] text-ink-300">
                        @foreach ($tierTokens as $i => $tierToken)
                            <div class="rounded-xl border border-ink-700/60 bg-ink-800/40 px-3 py-2 font-mono uppercase tracking-[0.18em]">Tier {{ $i + 1 }} - {{ $tierToken }} token</div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('checkout.store') }}" class="relative mt-7 space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                            @foreach ($tokens as $key => $pack)
                                <label class="group cursor-pointer">
                                    <input type="radio" name="package_key" value="{{ $key }}" class="peer sr-only" @checked($loop->first)>
                                    <span class="relative flex flex-col gap-1 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4 transition-all peer-checked:border-emerald-400/60 peer-checked:bg-emerald-500/10 peer-checked:shadow-[0_15px_50px_-25px_rgba(52,211,153,0.6)]">
                                        @if ($pack['badge'])
                                            <span class="absolute right-3 top-3 rounded-full {{ $pack['badge'] === 'Best Deal' ? 'bg-gradient-to-b from-gold-300 to-gold-500 text-ink-900' : 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-200' }} px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.2em]">{{ $pack['badge'] }}</span>
                                        @endif
                                        <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">{{ $pack['tokens'] }} Token</span>
                                        <span class="font-display text-2xl text-ink-50">{{ $fmtIdr($pack['price']) }}</span>
                                        <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">@ {{ $fmtIdr($pack['price'] / $pack['tokens']) }} / token</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <label class="block">
                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Nomor WhatsApp (untuk notif)</span>
                            <input name="whatsapp_number" type="tel" placeholder="08xxxxxxxxxx" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none focus:border-emerald-400/50">
                        </label>

                        <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-500/10 px-6 py-3.5 text-sm font-semibold text-emerald-100 transition-all hover:border-emerald-300/70 hover:bg-emerald-500/20">
                            Buat Pesanan Token
                            <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </button>
                    </form>
                </article>
            </div>

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
