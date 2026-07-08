<x-layouts.app title="Pesanan {{ $order->reference }} - Weesia">
    <x-dashboard-shell active="checkout">
        @php
            $fmtIdr = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
            $statusToneClass = match ($order->statusTone()) {
                'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
                'gold' => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
                default => 'border-ink-700/70 bg-ink-800/40 text-ink-200',
            };
            $hasProof = (bool) $order->proof_path;
            $banks = $paymentSetting->bank_accounts ?? [];
            $ewallets = $paymentSetting->ewallet_accounts ?? [];
            $finalized = $order->isFinalized();
        @endphp

        <section class="relative mx-auto max-w-5xl px-4 pb-12 pt-24 sm:px-6 sm:pt-28">
            @if (session('status'))
                <div class="reveal mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="reveal mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <div class="reveal flex flex-col items-start gap-3 border-b border-ink-700/60 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('checkout') }}" class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300 transition-colors hover:text-gold-200">
                        <x-icon name="arrow-right" class="h-3 w-3 rotate-180" />
                        Kembali ke checkout
                    </a>
                    <h1 class="mt-3 font-display text-3xl leading-none text-ink-50 sm:text-4xl">{{ $order->package_label }}</h1>
                    <div class="mt-2 font-mono text-[11px] uppercase tracking-[0.22em] text-ink-300">Order {{ $order->reference }} - {{ $order->created_at->format('d M Y H:i') }}</div>
                </div>
                <span class="rounded-full border px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.22em] {{ $statusToneClass }}">{{ $order->statusLabel() }}</span>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
                <div class="reveal rounded-3xl border border-gold-500/30 bg-gradient-to-br from-ink-800/80 via-ink-900 to-ink-900 p-6 sm:p-7">
                    <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Total Bayar</div>
                    <div class="mt-2 font-display text-5xl text-ink-50">{{ $fmtIdr($order->total_amount) }}</div>
                    <p class="mt-2 text-xs leading-relaxed text-ink-300">Sudah termasuk kode unik <span class="font-mono text-gold-200">{{ str_pad($order->unique_code, 3, '0', STR_PAD_LEFT) }}</span> untuk identifikasi mutasi.</p>

                    <div class="mt-5 grid grid-cols-2 gap-3 rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4 text-sm">
                        <div>
                            <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Harga Paket</div>
                            <div class="mt-1 font-mono text-ink-100">{{ $fmtIdr($order->price) }}</div>
                        </div>
                        <div>
                            <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Kode Unik</div>
                            <div class="mt-1 font-mono text-gold-200">+ Rp {{ str_pad($order->unique_code, 3, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>

                    @if ($paymentSetting->qris_path)
                        <div class="mt-5">
                            <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Scan QRIS</div>
                            <div class="mt-3 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-50 p-3">
                                <img src="{{ asset('storage/'.$paymentSetting->qris_path) }}" alt="QRIS" class="mx-auto max-h-72 w-auto">
                            </div>
                            @if ($paymentSetting->qris_holder)
                                <div class="mt-2 text-center font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $paymentSetting->qris_holder }}</div>
                            @endif
                        </div>
                    @endif

                    @if ($paymentSetting->notes)
                        <div class="mt-5 rounded-2xl border border-ink-700/60 bg-ink-800/40 p-4 text-xs leading-relaxed text-ink-200">
                            {!! nl2br(e($paymentSetting->notes)) !!}
                        </div>
                    @endif
                </div>

                <div class="space-y-5">
                    @if (! empty($banks) || ! empty($ewallets))
                        <div class="reveal rounded-3xl border border-ink-700/60 bg-ink-900/75 p-6 backdrop-blur-xl">
                            <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Atau Transfer Manual</div>

                            @foreach ([['Bank', $banks], ['E-Wallet', $ewallets]] as [$label, $accounts])
                                @if (! empty($accounts))
                                    <div class="mt-4">
                                        <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $label }}</div>
                                        <div class="mt-2 space-y-2">
                                            @foreach ($accounts as $acc)
                                                <div class="flex items-center justify-between gap-3 rounded-2xl border border-ink-700/60 bg-ink-800/40 p-3">
                                                    <div class="min-w-0">
                                                        <div class="font-display text-sm text-ink-50">{{ $acc['bank_name'] ?? '-' }}</div>
                                                        <div class="mt-0.5 truncate font-mono text-xs text-ink-300">a.n. {{ $acc['account_name'] ?? '-' }}</div>
                                                    </div>
                                                    <button type="button" data-copy="{{ $acc['account_number'] ?? '' }}" class="inline-flex items-center gap-2 rounded-xl border border-gold-500/30 bg-gold-500/10 px-3 py-1.5 font-mono text-xs text-gold-200 transition-all hover:border-gold-400/70">
                                                        <span>{{ $acc['account_number'] ?? '-' }}</span>
                                                        <x-icon name="clipboard" class="h-3.5 w-3.5" />
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="reveal rounded-3xl border border-ink-700/60 bg-ink-900/75 p-6 backdrop-blur-xl">
                        <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Bukti Transfer</div>

                        @if ($finalized)
                            <div class="mt-4 rounded-2xl border {{ $order->status === \App\Models\PaymentOrder::STATUS_APPROVED ? 'border-emerald-500/30 bg-emerald-500/10' : 'border-rose-500/30 bg-rose-500/10' }} p-4 text-sm">
                                <div class="font-display text-lg {{ $order->status === \App\Models\PaymentOrder::STATUS_APPROVED ? 'text-emerald-200' : 'text-rose-200' }}">{{ $order->statusLabel() }}</div>
                                <div class="mt-1 text-xs text-ink-200">{{ $order->reviewed_at?->format('d M Y H:i') }}</div>
                                @if ($order->admin_note)
                                    <p class="mt-3 text-sm leading-relaxed text-ink-100">{{ $order->admin_note }}</p>
                                @endif
                            </div>
                        @elseif ($hasProof)
                            <p class="mt-3 text-sm leading-relaxed text-ink-200">Bukti transfer sudah terkirim. Admin akan verifikasi maksimal 1x24 jam.</p>
                            <div class="mt-4 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-800/40">
                                <img src="{{ asset('storage/'.$order->proof_path) }}" alt="Bukti" class="max-h-64 w-full object-contain">
                            </div>
                            <form method="POST" action="{{ route('orders.proof', $order) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                                @csrf
                                <label class="block">
                                    <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Upload ulang bukti (opsional)</span>
                                    <input type="file" name="proof" accept="image/*" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-3 py-2 text-xs text-ink-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gold-500/15 file:px-3 file:py-1.5 file:text-xs file:text-gold-200">
                                </label>
                                <button class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 bg-ink-800/60 px-5 py-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                                    <x-icon name="image-plus" class="h-3.5 w-3.5" />
                                    Ganti Bukti
                                </button>
                            </form>
                        @else
                            <p class="mt-3 text-sm leading-relaxed text-ink-200">Setelah transfer, upload screenshot bukti transfer di sini.</p>
                            <form method="POST" action="{{ route('orders.proof', $order) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                                @csrf
                                <label class="block">
                                    <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">File Bukti (JPG/PNG, max 4MB)</span>
                                    <input type="file" name="proof" required accept="image/*" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-3 py-2 text-xs text-ink-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gold-500/15 file:px-3 file:py-1.5 file:text-xs file:text-gold-200">
                                </label>
                                <label class="block">
                                    <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">No WhatsApp (untuk notif)</span>
                                    <input type="tel" name="whatsapp_number" value="{{ $order->whatsapp_number }}" placeholder="08xxxxxxxxxx" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                </label>
                                <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(23,209,131,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(23,209,131,1)]">
                                    Kirim Bukti Transfer
                                    <x-icon name="send" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </button>
                            </form>
                        @endif

                        @if (! $finalized && ! $hasProof)
                            <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-4 border-t border-ink-700/60 pt-4">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300 transition-colors hover:text-rose-200">
                                    <x-icon name="x" class="h-3.5 w-3.5" />
                                    Batalkan Order
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <script>
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-copy]');
                if (!btn) return;
                navigator.clipboard?.writeText(btn.dataset.copy);
                const original = btn.querySelector('span').textContent;
                btn.querySelector('span').textContent = 'Tersalin!';
                setTimeout(() => btn.querySelector('span').textContent = original, 1500);
            });
        </script>
    </x-dashboard-shell>
</x-layouts.app>
