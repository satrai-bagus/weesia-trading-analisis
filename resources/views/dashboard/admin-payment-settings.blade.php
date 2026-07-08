<x-layouts.app title="Admin - Setting Pembayaran">
    <x-dashboard-shell active="admin-orders">
        @php
            $banks = old('banks', $setting->bank_accounts ?? []);
            $ewallets = old('ewallets', $setting->ewallet_accounts ?? []);
            // pad with one empty row each so user can always add
            $banks = array_pad((array) $banks, max(count((array) $banks) + 1, 2), ['bank_name' => '', 'account_number' => '', 'account_name' => '']);
            $ewallets = array_pad((array) $ewallets, max(count((array) $ewallets) + 1, 2), ['bank_name' => '', 'account_number' => '', 'account_name' => '']);
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

            <div class="reveal flex items-center justify-between border-b border-ink-700/60 pb-6">
                <div>
                    <a href="{{ route('admin.orders') }}" class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300 transition-colors hover:text-gold-200">
                        <x-icon name="arrow-right" class="h-3 w-3 rotate-180" />
                        Kembali ke Pesanan
                    </a>
                    <h1 class="mt-3 font-display text-3xl text-ink-50 sm:text-4xl">Setting Pembayaran</h1>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-ink-300">Upload QRIS dan daftar rekening yang akan ditampilkan ke user di halaman pesanan.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.payment-settings.update') }}" enctype="multipart/form-data" class="mt-8 space-y-8">
                @csrf

                <section class="reveal rounded-3xl border border-gold-500/25 bg-ink-900/75 p-6 backdrop-blur-xl sm:p-8">
                    <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">QRIS</div>
                    <h2 class="mt-2 font-display text-2xl text-ink-50">QR Code Pembayaran</h2>

                    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-[200px_minmax(0,1fr)]">
                        <div class="overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-50 p-3">
                            @if ($setting->qris_path)
                                <img src="{{ asset('storage/'.$setting->qris_path) }}" alt="QRIS" class="mx-auto h-auto max-h-44 w-auto">
                            @else
                                <div class="flex h-44 items-center justify-center font-mono text-[10px] uppercase tracking-[0.2em] text-ink-700">Belum ada QRIS</div>
                            @endif
                        </div>

                        <div class="space-y-4">
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Upload QRIS Baru (JPG/PNG)</span>
                                <input type="file" name="qris" accept="image/*" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-3 py-2 text-xs text-ink-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gold-500/15 file:px-3 file:py-1.5 file:text-xs file:text-gold-200">
                            </label>
                            <label class="block">
                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Atas Nama (opsional)</span>
                                <input type="text" name="qris_holder" value="{{ old('qris_holder', $setting->qris_holder) }}" placeholder="contoh: Weesia FibPath" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </label>
                            @if ($setting->qris_path)
                                <label class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300">
                                    <input type="checkbox" name="remove_qris" value="1" class="h-4 w-4 rounded border-ink-600 bg-ink-800 text-rose-400 focus:ring-rose-400">
                                    Hapus QRIS yang ada
                                </label>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="reveal rounded-3xl border border-ink-700/60 bg-ink-900/75 p-6 backdrop-blur-xl sm:p-8">
                    <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Rekening Bank</div>
                    <h2 class="mt-2 font-display text-2xl text-ink-50">Daftar Rekening Bank</h2>
                    <p class="mt-1 text-xs text-ink-300">Yang kosong akan diabaikan otomatis. Bisa tambah baris kapan saja.</p>

                    <div class="mt-5 space-y-3" data-account-list="banks">
                        @foreach ($banks as $i => $bank)
                            <div class="grid grid-cols-1 gap-2 rounded-2xl border border-ink-700/60 bg-ink-800/40 p-3 sm:grid-cols-3">
                                <input type="text" name="banks[{{ $i }}][bank_name]" value="{{ $bank['bank_name'] ?? '' }}" placeholder="Nama bank (BCA/Mandiri)" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <input type="text" name="banks[{{ $i }}][account_number]" value="{{ $bank['account_number'] ?? '' }}" placeholder="Nomor rekening" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <input type="text" name="banks[{{ $i }}][account_name]" value="{{ $bank['account_name'] ?? '' }}" placeholder="Atas nama" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </div>
                        @endforeach
                    </div>
                    <button type="button" data-add-row="banks" class="mt-3 inline-flex items-center gap-2 rounded-full border border-ink-600/70 bg-ink-800/50 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200 transition-all hover:border-gold-500/40 hover:text-gold-100">
                        + Tambah Rekening
                    </button>
                </section>

                <section class="reveal rounded-3xl border border-ink-700/60 bg-ink-900/75 p-6 backdrop-blur-xl sm:p-8">
                    <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">E-Wallet</div>
                    <h2 class="mt-2 font-display text-2xl text-ink-50">DANA / OVO / GoPay / ShopeePay</h2>

                    <div class="mt-5 space-y-3" data-account-list="ewallets">
                        @foreach ($ewallets as $i => $ew)
                            <div class="grid grid-cols-1 gap-2 rounded-2xl border border-ink-700/60 bg-ink-800/40 p-3 sm:grid-cols-3">
                                <input type="text" name="ewallets[{{ $i }}][bank_name]" value="{{ $ew['bank_name'] ?? '' }}" placeholder="Provider (DANA/GoPay)" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <input type="text" name="ewallets[{{ $i }}][account_number]" value="{{ $ew['account_number'] ?? '' }}" placeholder="Nomor HP" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <input type="text" name="ewallets[{{ $i }}][account_name]" value="{{ $ew['account_name'] ?? '' }}" placeholder="Atas nama" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            </div>
                        @endforeach
                    </div>
                    <button type="button" data-add-row="ewallets" class="mt-3 inline-flex items-center gap-2 rounded-full border border-ink-600/70 bg-ink-800/50 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-200 transition-all hover:border-gold-500/40 hover:text-gold-100">
                        + Tambah E-Wallet
                    </button>
                </section>

                <section class="reveal rounded-3xl border border-ink-700/60 bg-ink-900/75 p-6 backdrop-blur-xl sm:p-8">
                    <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Lain-lain</div>
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <label class="block">
                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">No WhatsApp Admin</span>
                            <input type="text" name="whatsapp_admin" value="{{ old('whatsapp_admin', $setting->whatsapp_admin) }}" placeholder="62812xxx" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                        </label>
                        <label class="block">
                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Catatan untuk User</span>
                            <textarea name="notes" rows="3" placeholder="contoh: Konfirmasi maks 1x24 jam." class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">{{ old('notes', $setting->notes) }}</textarea>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-7 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_18px_60px_-15px_rgba(23,209,131,0.7)] transition-all hover:shadow-[0_24px_80px_-15px_rgba(23,209,131,0.9)]">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Simpan Settings
                    </button>
                </div>
            </form>
        </section>

        <script>
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-add-row]');
                if (!btn) return;
                const key = btn.dataset.addRow;
                const list = document.querySelector(`[data-account-list="${key}"]`);
                if (!list) return;
                const idx = list.children.length;
                const row = document.createElement('div');
                row.className = 'grid grid-cols-1 gap-2 rounded-2xl border border-ink-700/60 bg-ink-800/40 p-3 sm:grid-cols-3';
                row.innerHTML = `
                    <input type="text" name="${key}[${idx}][bank_name]" placeholder="${key === 'banks' ? 'Nama bank' : 'Provider'}" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                    <input type="text" name="${key}[${idx}][account_number]" placeholder="${key === 'banks' ? 'Nomor rekening' : 'Nomor HP'}" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 font-mono text-sm text-ink-100 outline-none focus:border-gold-500/50">
                    <input type="text" name="${key}[${idx}][account_name]" placeholder="Atas nama" class="rounded-xl border border-ink-700 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                `;
                list.appendChild(row);
            });
        </script>
    </x-dashboard-shell>
</x-layouts.app>
