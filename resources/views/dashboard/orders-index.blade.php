<x-layouts.app title="Pesanan Saya - Weesia">
    <x-dashboard-shell active="checkout">
        @php
            $fmtIdr = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
        @endphp

        <section class="relative mx-auto max-w-5xl px-4 pb-12 pt-24 sm:px-6 sm:pt-28">
            <div class="reveal flex items-end justify-between border-b border-ink-700/60 pb-6">
                <div>
                    <div class="inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">My Orders</span>
                    </div>
                    <h1 class="mt-3 font-display text-3xl text-ink-50 sm:text-4xl">Pesanan Saya</h1>
                </div>
                <a href="{{ route('checkout') }}" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-5 py-2.5 text-sm font-semibold text-ink-900 transition-all hover:shadow-[0_14px_44px_-16px_rgba(23,209,131,1)]">
                    <x-icon name="wallet" class="h-4 w-4" />
                    Beli Lagi
                </a>
            </div>

            <div class="mt-8 space-y-3">
                @forelse ($orders as $order)
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
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gold-500/25 bg-gold-500/10 text-gold-200">
                                <x-icon name="{{ $order->type === \App\Models\PaymentOrder::TYPE_TOKEN ? 'wallet' : 'shield-check' }}" class="h-4 w-4" />
                            </span>
                            <div>
                                <div class="font-display text-base text-ink-50">{{ $order->package_label }}</div>
                                <div class="mt-0.5 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">{{ $order->reference }} - {{ $order->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 sm:gap-5">
                            <span class="font-mono text-sm text-ink-100">{{ $fmtIdr($order->total_amount) }}</span>
                            <span class="rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] {{ $tone }}">{{ $order->statusLabel() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-8 text-center">
                        <div class="font-display text-2xl text-ink-50">Belum ada pesanan</div>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-300">Beli token atau subscribe untuk mulai akses analisa FibPath.</p>
                        <a href="{{ route('checkout') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-5 py-2.5 text-sm font-semibold text-ink-900">Beli Sekarang</a>
                    </div>
                @endforelse
            </div>

            @if ($orders->hasPages())
                <div class="mt-6">{{ $orders->links() }}</div>
            @endif
        </section>
    </x-dashboard-shell>
</x-layouts.app>
