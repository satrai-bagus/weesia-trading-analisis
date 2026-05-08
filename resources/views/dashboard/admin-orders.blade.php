<x-layouts.app title="Admin - Pesanan">
    <x-dashboard-shell active="admin-orders">
        @php
            $fmtIdr = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
            $statusOptions = [
                'awaiting_review' => 'Menunggu Verifikasi',
                'pending' => 'Menunggu Bayar',
                'approved' => 'Berhasil',
                'rejected' => 'Ditolak',
                'all' => 'Semua',
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

            <div class="reveal flex flex-col items-start justify-between gap-4 border-b border-ink-700/60 pb-6 sm:flex-row sm:items-end">
                <div>
                    <div class="inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Admin &middot; Orders</span>
                    </div>
                    <h1 class="mt-3 font-display text-3xl text-ink-50 sm:text-4xl">Pesanan Member</h1>
                </div>
                <a href="{{ route('admin.payment-settings.edit') }}" class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 bg-ink-800/60 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                    <x-icon name="settings" class="h-3.5 w-3.5" />
                    Setting Pembayaran
                </a>
            </div>

            <div class="reveal mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/40 lg:grid-cols-4">
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Perlu Verifikasi</div>
                    <div class="mt-2 font-display text-3xl text-gold-200">{{ $stats['awaiting'] }}</div>
                </div>
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Menunggu Bayar</div>
                    <div class="mt-2 font-display text-3xl text-ink-50">{{ $stats['pending'] }}</div>
                </div>
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Approved Hari Ini</div>
                    <div class="mt-2 font-display text-3xl text-emerald-300">{{ $stats['approved_today'] }}</div>
                </div>
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Revenue Bulan Ini</div>
                    <div class="mt-2 font-display text-2xl text-emerald-300">{{ $fmtIdr($stats['revenue_month']) }}</div>
                </div>
            </div>

            <div class="reveal mt-6 flex flex-wrap items-center gap-2">
                @foreach ($statusOptions as $value => $label)
                    @php $isActive = $statusFilter === $value; @endphp
                    <a href="{{ route('admin.orders', ['status' => $value]) }}" class="rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-all {{ $isActive ? 'border-gold-500/50 bg-gold-500/15 text-gold-100' : 'border-ink-700/70 bg-ink-800/50 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="reveal mt-6 space-y-3">
                @forelse ($orders as $order)
                    @php
                        $tone = match ($order->statusTone()) {
                            'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                            'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
                            'gold' => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
                            default => 'border-ink-700/70 bg-ink-800/40 text-ink-200',
                        };
                        $reviewable = in_array($order->status, [\App\Models\PaymentOrder::STATUS_AWAITING_REVIEW, \App\Models\PaymentOrder::STATUS_PENDING], true);
                    @endphp
                    <article class="grid grid-cols-1 gap-4 rounded-2xl border border-ink-700/60 bg-ink-900/70 p-4 backdrop-blur-md sm:grid-cols-[180px_minmax(0,1fr)] sm:p-5">
                        <div class="flex items-center justify-center sm:justify-start">
                            @if ($order->proof_path)
                                <a href="{{ asset('storage/'.$order->proof_path) }}" target="_blank" class="block overflow-hidden rounded-xl border border-ink-700/60 bg-ink-800/40">
                                    <img src="{{ asset('storage/'.$order->proof_path) }}" alt="Bukti" class="h-32 w-full object-cover sm:h-40">
                                </a>
                            @else
                                <div class="flex h-32 w-full items-center justify-center rounded-xl border border-dashed border-ink-700/70 bg-ink-800/30 sm:h-40">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-400">Belum upload</span>
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-display text-lg text-ink-50">{{ $order->package_label }}</span>
                                        <span class="rounded-full border px-2 py-0.5 font-mono text-[10px] uppercase tracking-[0.2em] {{ $tone }}">{{ $order->statusLabel() }}</span>
                                    </div>
                                    <div class="mt-1 font-mono text-[11px] uppercase tracking-[0.22em] text-ink-300">{{ $order->reference }} - {{ $order->created_at->format('d M H:i') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-display text-xl text-ink-50">{{ $fmtIdr($order->total_amount) }}</div>
                                    <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Kode: {{ str_pad($order->unique_code, 3, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-1 gap-3 text-xs sm:grid-cols-3">
                                <div class="rounded-xl border border-ink-700/60 bg-ink-800/40 p-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">User</div>
                                    <div class="mt-1 truncate text-ink-100">{{ $order->user->name }}</div>
                                    <div class="truncate font-mono text-[10px] text-ink-300">{{ $order->user->email }}</div>
                                </div>
                                <div class="rounded-xl border border-ink-700/60 bg-ink-800/40 p-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">WhatsApp</div>
                                    <div class="mt-1 font-mono text-ink-100">{{ $order->whatsapp_number ?: '-' }}</div>
                                </div>
                                <div class="rounded-xl border border-ink-700/60 bg-ink-800/40 p-3">
                                    <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">Saldo Sekarang</div>
                                    <div class="mt-1 font-mono text-ink-100">{{ $order->user->coin_balance }} koin{{ $order->user->subscription_until ? ' / sub '.$order->user->subscription_until->format('d M Y') : '' }}</div>
                                </div>
                            </div>

                            @if ($order->admin_note)
                                <div class="mt-3 rounded-xl border border-ink-700/60 bg-ink-800/40 p-3 text-xs leading-relaxed text-ink-200">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Note admin:</span> {{ $order->admin_note }}
                                </div>
                            @endif

                            @if ($reviewable)
                                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <form method="POST" action="{{ route('admin.orders.approve', $order) }}" class="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-3">
                                        @csrf
                                        <input type="text" name="admin_note" placeholder="Catatan (opsional)" maxlength="255" class="block w-full rounded-xl border border-emerald-500/20 bg-ink-900/60 px-3 py-2 text-xs text-ink-100 outline-none focus:border-emerald-400/60">
                                        <button class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-ink-900 transition-colors hover:bg-emerald-400">
                                            <x-icon name="check-circle" class="h-4 w-4" />
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="rounded-2xl border border-rose-500/30 bg-rose-500/5 p-3">
                                        @csrf
                                        <input type="text" name="admin_note" required placeholder="Alasan penolakan (wajib)" maxlength="255" class="block w-full rounded-xl border border-rose-500/20 bg-ink-900/60 px-3 py-2 text-xs text-ink-100 outline-none focus:border-rose-400/60">
                                        <button class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-400/40 bg-rose-500/10 px-4 py-2 text-sm font-semibold text-rose-200 transition-colors hover:bg-rose-500/20">
                                            <x-icon name="x" class="h-4 w-4" />
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-10 text-center">
                        <div class="font-display text-2xl text-ink-50">Tidak ada pesanan dengan filter ini.</div>
                    </div>
                @endforelse
            </div>

            @if ($orders->hasPages())
                <div class="mt-6">{{ $orders->withQueryString()->links() }}</div>
            @endif
        </section>
    </x-dashboard-shell>
</x-layouts.app>
