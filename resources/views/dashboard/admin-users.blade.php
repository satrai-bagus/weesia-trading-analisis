<x-layouts.app title="Manajemen User - Weesia">
    <x-dashboard-shell active="admin-users">
        @php
            $coinFmt = fn ($value) => number_format((int) $value, 0, ',', '.');
            $statusBadge = function ($user) {
                if ($user->hasActiveSubscription()) {
                    return ['class' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300', 'label' => 'Subscriber', 'key' => 'subscriber'];
                }
                if ($user->subscription_until) {
                    return ['class' => 'border-rose-500/30 bg-rose-500/10 text-rose-300', 'label' => 'Kadaluarsa', 'key' => 'expired'];
                }
                return ['class' => 'border-ink-600/70 bg-ink-800/60 text-ink-200', 'label' => 'Belum berlangganan', 'key' => 'free'];
            };
            $typeTone = fn ($type) => match ($type) {
                \App\Models\CoinTransaction::TYPE_TOPUP => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                \App\Models\CoinTransaction::TYPE_SPEND => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
                \App\Models\CoinTransaction::TYPE_SUBSCRIPTION => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
                \App\Models\CoinTransaction::TYPE_REFUND => 'border-sky-500/30 bg-sky-500/10 text-sky-300',
                default => 'border-ink-600 bg-ink-800/60 text-ink-200',
            };
            $autoOpenAddUser = $errors->any() && (old('name') !== null || old('email') !== null);
            $topUsers = $topUsers ?? collect();
            $rankAccent = function ($index) {
                return match ($index) {
                    0 => ['border-gold-400/50 bg-gold-500/10 text-gold-200', 'border-gold-400/60'],
                    1 => ['border-ink-500/60 bg-ink-700/30 text-ink-100', 'border-ink-500/60'],
                    2 => ['border-amber-700/40 bg-amber-700/10 text-amber-200', 'border-amber-700/50'],
                    default => ['border-ink-700/60 bg-ink-800/40 text-ink-200', 'border-ink-700/60'],
                };
            };
        @endphp

        <section class="relative overflow-hidden border-b border-ink-700/60 bg-[linear-gradient(180deg,rgba(18,18,16,0.96),rgba(5,5,5,1))] pt-32 sm:pt-28">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]">
                <svg class="h-full w-full" preserveAspectRatio="none">
                    <defs>
                        <pattern id="admin-users-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                            <path d="M 42 0 L 0 0 0 42" fill="none" stroke="#d4a72c" stroke-width="0.7" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#admin-users-grid)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 pb-8 sm:px-6 sm:pb-10">
                <div class="reveal">
                    <div class="inline-flex w-fit items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Manajemen User</span>
                    </div>
                    <h1 class="mt-5 max-w-3xl font-display text-4xl leading-[1.05] text-ink-50 sm:text-6xl">Kontrol akses & saldo trader.</h1>
                    <p class="mt-6 max-w-2xl text-base leading-relaxed text-ink-200">Top-up koin, perpanjang subscription, edit profil, dan audit semua transaksi user dari satu meja kerja.</p>
                </div>

                @if (session('status'))
                    <div class="reveal mt-8 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="reveal mt-8 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="reveal mt-10 grid grid-cols-1 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/50 sm:grid-cols-2 sm:rounded-3xl lg:grid-cols-4">
                    <x-stat-card label="Total User" :value="$stats['total_users']" change="terdaftar di sistem" />
                    <x-stat-card label="Subscriber Aktif" :value="$stats['subscribers']" change="bayar bulanan" tone="emerald" />
                    <x-stat-card label="Saldo Koin Beredar" :value="number_format($stats['total_coins'], 0, ',', '.')" change="koin di tangan user" />
                    <x-stat-card label="Total Top-up" :value="number_format($stats['total_topup'], 0, ',', '.')" change="koin pernah diisi" tone="emerald" />
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 sm:py-8">

            @if ($topUsers->isNotEmpty())
                <div class="reveal rounded-2xl border border-gold-500/25 bg-[linear-gradient(120deg,rgba(212,167,44,0.07),rgba(18,18,16,0.85)_60%)] p-4 backdrop-blur-xl sm:p-5">
                    <div class="mb-5 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                <x-icon name="trending-up" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="font-display text-2xl leading-none text-ink-50">Top Trader</h2>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Lifetime value tertinggi</div>
                            </div>
                        </div>
                        <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-400">Top {{ $topUsers->count() }} - berdasarkan total top-up + subscription</span>
                    </div>

                    @php
                        $topCols = match (min($topUsers->count(), 5)) {
                            1 => 'lg:grid-cols-1',
                            2 => 'lg:grid-cols-2',
                            3 => 'lg:grid-cols-3',
                            4 => 'lg:grid-cols-4',
                            default => 'lg:grid-cols-5',
                        };
                    @endphp
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 {{ $topCols }}">
                        @foreach ($topUsers as $i => $u)
                            @php
                                [$accent, $borderAccent] = $rankAccent($i);
                                $lifetime = (int) ($u->lifetime_value ?? 0);
                                $topupTotal = (int) ($u->total_topup ?? 0);
                                $subTotal = abs((int) ($u->total_subscription ?? 0));
                                $unlocks = (int) ($u->signal_unlocks_count ?? 0);
                            @endphp
                            <div class="relative rounded-2xl border {{ $borderAccent }} bg-ink-900/70 p-4 backdrop-blur-md">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border px-2 font-mono text-[10px] font-semibold uppercase tracking-[0.22em] {{ $accent }}">#{{ $i + 1 }}</span>
                                    @if ($i === 0)
                                        <span class="font-mono text-[9px] uppercase tracking-[0.22em] text-gold-300">Crown</span>
                                    @endif
                                </div>
                                <div class="mt-3 truncate font-display text-base text-ink-50" title="{{ $u->name }}">{{ $u->name }}</div>
                                <div class="mt-0.5 truncate font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400" title="{{ $u->email }}">{{ $u->email }}</div>

                                <div class="mt-4 space-y-1.5 border-t border-ink-700/60 pt-3">
                                    <div class="flex items-center justify-between gap-2 font-mono text-[10px] uppercase tracking-[0.2em]">
                                        <span class="text-ink-400">LTV</span>
                                        <span class="font-display text-base normal-case text-gold-200 tracking-normal">{{ $coinFmt($lifetime) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">
                                        <span>Top-up</span>
                                        <span class="text-emerald-300">{{ $coinFmt($topupTotal) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">
                                        <span>Subs</span>
                                        <span class="text-gold-300">{{ $coinFmt($subTotal) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">
                                        <span>Unlock</span>
                                        <span class="text-ink-100">{{ $unlocks }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5"
                 data-signal-carousel data-page-size="8" data-user-filter-root>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                            <x-icon name="wallet" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="font-display text-2xl leading-none text-ink-50">Daftar User</h2>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Klik baris untuk top-up, edit, atau hapus</div>
                        </div>
                    </div>
                    <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
                        <span data-carousel-status class="hidden font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 sm:inline"></span>
                        <div class="flex items-center gap-2">
                            <button type="button" data-carousel-prev aria-label="User sebelumnya" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100 disabled:cursor-not-allowed disabled:opacity-35">
                                <x-icon name="arrow-right" class="h-4 w-4 rotate-180" />
                            </button>
                            <button type="button" data-carousel-next aria-label="User berikutnya" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/25 bg-gold-500/10 text-gold-100 transition-all hover:border-gold-400/70 disabled:cursor-not-allowed disabled:opacity-35">
                                <x-icon name="arrow-right" class="h-4 w-4" />
                            </button>
                            <button type="button" data-modal-open="add-user" class="inline-flex items-center gap-2 rounded-full bg-gold-500 px-4 py-2 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(212,167,44,0.8)] transition-all hover:bg-gold-300">
                                <x-icon name="user" class="h-4 w-4" />
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-ink-700/60 bg-ink-800/35 p-3 sm:p-4">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.7fr)_180px_180px_auto]">
                        <label class="relative">
                            <span class="sr-only">Cari user</span>
                            <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-300" />
                            <input type="search" data-user-search placeholder="Cari nama atau email user"
                                   class="min-h-12 w-full rounded-2xl border border-ink-700/70 bg-ink-900/80 py-3 pl-11 pr-4 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
                        </label>
                        <label>
                            <span class="sr-only">Filter role</span>
                            <select data-user-role class="min-h-12 w-full rounded-2xl border border-ink-700/70 bg-ink-900/80 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <option value="all">Semua Role</option>
                                <option value="user">User (Trader)</option>
                                <option value="admin">Admin</option>
                            </select>
                        </label>
                        <label>
                            <span class="sr-only">Filter status subscription</span>
                            <select data-user-status class="min-h-12 w-full rounded-2xl border border-ink-700/70 bg-ink-900/80 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <option value="all">Semua Status</option>
                                <option value="subscriber">Subscriber Aktif</option>
                                <option value="expired">Subscription Kadaluarsa</option>
                                <option value="free">Belum Berlangganan</option>
                            </select>
                        </label>
                        <button type="button" data-user-filter-clear class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-ink-700/70 bg-ink-900/80 px-4 text-sm font-medium text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-2xl border border-ink-700/60" data-carousel-list>
                    <div class="hidden grid-cols-[minmax(0,2fr)_minmax(0,1fr)_120px_minmax(0,1.4fr)_72px] gap-3 bg-ink-800/70 px-4 py-3 lg:grid">
                        @foreach (['User', 'Role', 'Koin', 'Subscription', ''] as $header)
                            <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-gold-300">{{ $header }}</div>
                        @endforeach
                    </div>

                    @forelse ($users as $user)
                        @php
                            $badge = $statusBadge($user);
                            $hayParts = [strtolower($user->name), strtolower($user->email), strtolower($user->role)];
                            $hay = implode(' ', $hayParts);
                            $userTopup = (int) ($user->total_topup ?? 0);
                            $userSub = abs((int) ($user->total_subscription ?? 0));
                            $userUnlocks = (int) ($user->signal_unlocks_count ?? 0);
                            $userPositions = (int) ($user->signal_positions_count ?? 0);
                            $userSignals = (int) ($user->trade_signals_count ?? 0);
                        @endphp
                        <details data-carousel-item
                                 data-search-hay="{{ $hay }}"
                                 data-role="{{ $user->role }}"
                                 data-status="{{ $badge['key'] }}"
                                 class="group border-t border-ink-700/60 bg-ink-900 first:border-t-0">
                            <summary class="grid cursor-pointer list-none grid-cols-1 gap-3 px-4 py-4 transition-colors hover:bg-ink-800/50 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_120px_minmax(0,1.4fr)_72px] [&::-webkit-details-marker]:hidden">
                                <div class="min-w-0">
                                    <div class="truncate text-sm text-gold-200">{{ $user->name }}</div>
                                    <div class="mt-1 truncate font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">{{ $user->email }}</div>
                                </div>
                                <div class="flex items-center text-sm text-ink-100">
                                    <span class="rounded-full border {{ $user->role === 'admin' ? 'border-gold-500/40 bg-gold-500/10 text-gold-200' : 'border-ink-600/70 text-ink-200' }} px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em]">{{ $user->role }}</span>
                                </div>
                                <div class="flex items-center font-mono text-sm text-ink-50">{{ $coinFmt($user->coin_balance) }}</div>
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit items-center gap-2 rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.18em] {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                    @if ($user->subscription_until)
                                        <span class="font-mono text-[10px] text-ink-300">{{ $user->subscription_until->format('d M Y H:i') }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-end">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all group-open:border-gold-500/50 group-open:bg-gold-500/10 group-open:text-gold-100">
                                        <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-open:rotate-90" />
                                    </span>
                                </div>
                            </summary>

                            <div class="space-y-4 border-t border-ink-700/60 bg-ink-800/40 px-4 py-5">
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <div class="rounded-xl border border-ink-700/60 bg-ink-900 p-3">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">Total Top-up</div>
                                        <div class="mt-1 font-display text-lg text-emerald-300">{{ $coinFmt($userTopup) }}</div>
                                    </div>
                                    <div class="rounded-xl border border-ink-700/60 bg-ink-900 p-3">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">Total Subscription</div>
                                        <div class="mt-1 font-display text-lg text-gold-200">{{ $coinFmt($userSub) }}</div>
                                    </div>
                                    <div class="rounded-xl border border-ink-700/60 bg-ink-900 p-3">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">Buka Analisa</div>
                                        <div class="mt-1 font-display text-lg text-ink-50">{{ $userUnlocks }}</div>
                                    </div>
                                    <div class="rounded-xl border border-ink-700/60 bg-ink-900 p-3">
                                        <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-400">Posisi/Pantauan</div>
                                        <div class="mt-1 font-display text-lg text-ink-50">{{ $userPositions }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <form method="POST" action="{{ route('admin.users.topup', $user) }}" class="space-y-3 rounded-2xl border border-ink-700/60 bg-ink-900 p-4">
                                        @csrf
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                                <x-icon name="bar-chart" class="h-4 w-4" />
                                            </span>
                                            <div class="font-display text-sm text-ink-50">Top-up Koin</div>
                                        </div>
                                        <label class="block">
                                            <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Jumlah Koin</span>
                                            <input type="number" name="amount" min="1" max="1000000" required class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-900 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        </label>
                                        <label class="block">
                                            <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Catatan (opsional)</span>
                                            <input type="text" name="note" maxlength="191" placeholder="Transfer BCA 02 May" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-900 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        </label>
                                        <button class="w-full rounded-xl bg-gold-500 px-4 py-2.5 text-sm font-semibold text-ink-900 transition-colors hover:bg-gold-300">Tambah Koin</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.subscription', $user) }}" class="space-y-3 rounded-2xl border border-ink-700/60 bg-ink-900 p-4">
                                        @csrf
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                                                <x-icon name="shield-check" class="h-4 w-4" />
                                            </span>
                                            <div class="font-display text-sm text-ink-50">Extend Subscription</div>
                                        </div>
                                        <label class="block">
                                            <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Durasi (bulan)</span>
                                            <input type="number" name="months" min="1" max="24" value="1" required class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-900 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        </label>
                                        <label class="block">
                                            <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Catatan (opsional)</span>
                                            <input type="text" name="note" maxlength="191" placeholder="Pembayaran subscription Mei" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-900 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        </label>
                                        <button class="w-full rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-200 transition-colors hover:bg-emerald-500/20">Perpanjang</button>
                                    </form>
                                </div>

                                <details class="group/edit rounded-2xl border border-ink-700/60 bg-ink-900 [&[open]]:border-gold-500/30">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-2.5 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-colors hover:text-gold-200 [&::-webkit-details-marker]:hidden">
                                        <span class="inline-flex items-center gap-2">
                                            <x-icon name="edit" class="h-3 w-3" />
                                            Edit Profil & Akses
                                        </span>
                                        <span class="font-mono text-[9px] text-ink-500 group-open/edit:hidden">Klik untuk buka</span>
                                        <span class="hidden font-mono text-[9px] text-gold-300 group-open/edit:inline">Tutup</span>
                                    </summary>
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid grid-cols-1 gap-3 border-t border-ink-700/60 p-4 sm:grid-cols-2">
                                        @csrf
                                        @method('PATCH')

                                        <label class="block">
                                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Nama</span>
                                            <input type="text" name="name" value="{{ $user->name }}" required maxlength="120"
                                                   class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        </label>
                                        <label class="block">
                                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Email</span>
                                            <input type="email" name="email" value="{{ $user->email }}" required maxlength="191"
                                                   class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        </label>
                                        <label class="block">
                                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Role</span>
                                            <select name="role" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                                <option value="user" @selected($user->role === 'user')>User (Trader)</option>
                                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                            </select>
                                        </label>
                                        <label class="block">
                                            <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Password Baru (opsional)</span>
                                            <input type="text" name="password" minlength="6" maxlength="191" placeholder="Kosongkan kalau tidak diganti"
                                                   class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        </label>
                                        <div class="flex flex-col gap-2 sm:col-span-2 sm:flex-row sm:items-center sm:justify-end">
                                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-5 py-2.5 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-100 transition-all hover:border-gold-400/70 hover:bg-gold-500/15">
                                                <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                                Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </details>

                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      class="flex flex-col gap-3 rounded-2xl border border-rose-500/20 bg-rose-500/[0.04] p-4 sm:flex-row sm:items-center sm:justify-between"
                                      onsubmit="return confirm('Hapus user {{ addslashes($user->name) }} permanen?@if ($userSignals > 0) PERHATIAN: user ini punya {{ $userSignals }} signal yang dipublish — hapus signal-nya dulu.@endif Semua transaksi koin, unlock, dan posisi user ini akan ikut terhapus. Tidak bisa diundo.');">
                                    @csrf
                                    @method('DELETE')
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-300">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </span>
                                        <div>
                                            <div class="font-display text-sm text-ink-50">Hapus User</div>
                                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-400">
                                                @if ($userSignals > 0)
                                                    <span class="text-rose-300">{{ $userSignals }} signal aktif — tidak bisa dihapus</span>
                                                @else
                                                    Akan menghapus semua transaksi & data user
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" @disabled($userSignals > 0)
                                            class="inline-flex items-center justify-center gap-2 rounded-full border border-rose-500/30 bg-rose-500/10 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-rose-200 transition-all hover:border-rose-400/60 hover:bg-rose-500/15 hover:text-rose-100 disabled:cursor-not-allowed disabled:opacity-40">
                                        <x-icon name="x" class="h-3.5 w-3.5" />
                                        Hapus Permanen
                                    </button>
                                </form>
                            </div>
                        </details>
                    @empty
                        <div class="border-t border-ink-700/60 bg-ink-900 px-4 py-6 text-center text-sm text-ink-300">Belum ada user.</div>
                    @endforelse
                </div>

                <div data-user-filter-empty class="mt-4 hidden rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-6 text-center">
                    <div class="font-display text-lg text-ink-50">Tidak ada user yang cocok dengan filter.</div>
                    <p class="mx-auto mt-2 max-w-md text-sm text-ink-300">Coba ubah kata kunci atau reset filter.</p>
                </div>
            </div>

            <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-4 backdrop-blur-xl sm:p-5"
                 data-signal-carousel data-page-size="12" data-tx-filter-root>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gold-500/20 bg-gold-500/10 text-gold-200">
                            <x-icon name="database" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="font-display text-2xl leading-none text-ink-50">Audit Transaksi Koin</h2>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">{{ $transactions->count() }} transaksi terakhir</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span data-carousel-status class="hidden font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300 sm:inline"></span>
                        <button type="button" data-carousel-prev aria-label="Halaman sebelumnya" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100 disabled:cursor-not-allowed disabled:opacity-35">
                            <x-icon name="arrow-right" class="h-4 w-4 rotate-180" />
                        </button>
                        <button type="button" data-carousel-next aria-label="Halaman berikutnya" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/25 bg-gold-500/10 text-gold-100 transition-all hover:border-gold-400/70 disabled:cursor-not-allowed disabled:opacity-35">
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-ink-700/60 bg-ink-800/35 p-3 sm:p-4">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.7fr)_180px_auto]">
                        <label class="relative">
                            <span class="sr-only">Cari transaksi</span>
                            <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-300" />
                            <input type="search" data-tx-search placeholder="Cari nama user atau catatan transaksi"
                                   class="min-h-12 w-full rounded-2xl border border-ink-700/70 bg-ink-900/80 py-3 pl-11 pr-4 text-sm text-ink-100 outline-none placeholder:text-ink-400 focus:border-gold-500/50">
                        </label>
                        <label>
                            <span class="sr-only">Filter tipe transaksi</span>
                            <select data-tx-type class="min-h-12 w-full rounded-2xl border border-ink-700/70 bg-ink-900/80 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                <option value="all">Semua Tipe</option>
                                <option value="{{ \App\Models\CoinTransaction::TYPE_TOPUP }}">Top-up</option>
                                <option value="{{ \App\Models\CoinTransaction::TYPE_SPEND }}">Buka Analisa</option>
                                <option value="{{ \App\Models\CoinTransaction::TYPE_SUBSCRIPTION }}">Subscription</option>
                                <option value="{{ \App\Models\CoinTransaction::TYPE_REFUND }}">Refund</option>
                            </select>
                        </label>
                        <button type="button" data-tx-filter-clear class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-ink-700/70 bg-ink-900/80 px-4 text-sm font-medium text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto rounded-2xl border border-ink-700/60">
                    <div class="min-w-[700px] sm:min-w-[760px]" data-carousel-list>
                        <div class="grid grid-cols-[minmax(0,1.4fr)_120px_minmax(0,2fr)_100px_140px_140px] gap-3 bg-ink-800/70 px-4 py-3">
                            @foreach (['User', 'Tipe', 'Catatan', 'Jumlah', 'Saldo Setelah', 'Waktu'] as $header)
                                <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-gold-300">{{ $header }}</div>
                            @endforeach
                        </div>
                        @forelse ($transactions as $tx)
                            @php
                                $txHay = strtolower(trim((optional($tx->user)->name ?? '').' '.(optional($tx->user)->email ?? '').' '.($tx->description ?? '').' '.$tx->typeLabel()));
                            @endphp
                            <div data-carousel-item
                                 data-search-hay="{{ $txHay }}"
                                 data-type="{{ $tx->type }}"
                                 class="grid grid-cols-[minmax(0,1.4fr)_120px_minmax(0,2fr)_100px_140px_140px] gap-3 border-t border-ink-700/60 bg-ink-900 px-4 py-3 text-sm text-ink-100 transition-colors hover:bg-ink-800/40">
                                <div class="min-w-0 truncate text-gold-200" title="{{ optional($tx->user)->email ?? '' }}">{{ optional($tx->user)->name ?? '-' }}</div>
                                <div><span class="rounded-full border px-2 py-0.5 font-mono text-[9px] uppercase tracking-[0.18em] {{ $typeTone($tx->type) }}">{{ $tx->typeLabel() }}</span></div>
                                <div class="min-w-0 truncate text-ink-200" title="{{ $tx->description }}">{{ $tx->description ?? '-' }}</div>
                                <div class="font-mono {{ $tx->amount > 0 ? 'text-emerald-300' : ($tx->amount < 0 ? 'text-rose-300' : 'text-ink-200') }}">{{ $tx->amount > 0 ? '+' : '' }}{{ $coinFmt($tx->amount) }}</div>
                                <div class="font-mono text-ink-50">{{ $coinFmt($tx->balance_after) }}</div>
                                <div class="font-mono text-[11px] text-ink-300">{{ $tx->created_at->format('d M H:i') }}</div>
                            </div>
                        @empty
                            <div class="border-t border-ink-700/60 bg-ink-900 px-4 py-5 text-sm text-ink-300">Belum ada transaksi.</div>
                        @endforelse
                    </div>
                </div>

                <div data-tx-filter-empty class="mt-4 hidden rounded-2xl border border-dashed border-gold-500/25 bg-ink-800/35 p-6 text-center">
                    <div class="font-display text-lg text-ink-50">Tidak ada transaksi yang cocok dengan filter.</div>
                    <p class="mx-auto mt-2 max-w-md text-sm text-ink-300">Coba kata kunci lain atau pilih tipe yang berbeda.</p>
                </div>
            </div>
        </section>

        <div data-modal="add-user" @if (! $autoOpenAddUser) hidden @endif class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-ink-900/85 p-4 backdrop-blur-xl sm:items-center sm:p-6">
            <div class="relative w-full max-w-3xl rounded-2xl border border-gold-500/20 bg-ink-900/95 p-4 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.9)] sm:rounded-3xl sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                            <x-icon name="user" class="h-5 w-5" />
                        </span>
                        <div>
                            <div class="font-display text-xl leading-none text-ink-50 sm:text-2xl">Tambah User Baru</div>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Buat akun trader / admin</div>
                        </div>
                    </div>
                    <button type="button" data-modal-close aria-label="Tutup" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-ink-600/70 text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-ink-700/60 bg-ink-900 p-4 transition-colors hover:border-gold-500/30 sm:p-5">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                    <x-icon name="user" class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="font-display text-base text-ink-50">Identitas</div>
                                    <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Nama & email</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <label class="block">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Nama</span>
                                    <input type="text" name="name" required maxlength="120" value="{{ old('name') }}" placeholder="Nama lengkap user" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-800/50 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                </label>
                                <label class="block">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Email</span>
                                    <input type="email" name="email" required maxlength="191" value="{{ old('email') }}" placeholder="trader@contoh.com" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-800/50 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                </label>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-ink-700/60 bg-ink-900 p-4 transition-colors hover:border-gold-500/30 sm:p-5">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gold-500/30 bg-gold-500/10 text-gold-200">
                                    <x-icon name="lock" class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="font-display text-base text-ink-50">Akses</div>
                                    <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Password & role</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <label class="block">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Password</span>
                                    <input type="text" name="password" required minlength="6" maxlength="191" placeholder="min 6 karakter" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-800/50 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                </label>
                                <label class="block">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Role</span>
                                    <select name="role" required class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-800/50 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                        <option value="user" @selected(old('role') === 'user' || ! old('role'))>User (Trader)</option>
                                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-ink-700/60 bg-ink-900 p-4 transition-colors hover:border-gold-500/30 sm:p-5">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200">
                                    <x-icon name="wallet" class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="font-display text-base text-ink-50">Saldo Awal</div>
                                    <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Opsional</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <label class="block">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Saldo Koin</span>
                                    <input type="number" name="coin_balance" min="0" max="1000000" value="{{ old('coin_balance', 0) }}" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-800/50 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                </label>
                                <label class="block">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">Subscription (bulan)</span>
                                    <input type="number" name="subscription_months" min="0" max="24" value="{{ old('subscription_months', 0) }}" class="mt-1 min-h-11 w-full rounded-xl border border-ink-700 bg-ink-800/50 px-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse items-stretch gap-3 border-t border-ink-700/60 pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" data-modal-close class="rounded-xl border border-ink-700 px-5 py-2.5 text-sm text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">Batal</button>
                        <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-gold-500 px-6 py-2.5 text-sm font-semibold text-ink-900 transition-colors hover:bg-gold-300">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Buat User
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                const initFilter = (rootSelector, opts) => {
                    const root = document.querySelector(rootSelector);
                    if (!root) return;
                    const search = opts.search ? root.querySelector(opts.search) : null;
                    const selects = (opts.selects || []).map(sel => ({ key: sel.key, el: root.querySelector(sel.selector) }));
                    const items = root.querySelectorAll('[data-carousel-item]');
                    const empty = opts.empty ? root.querySelector(opts.empty) : null;
                    const reset = opts.reset ? root.querySelector(opts.reset) : null;
                    const status = root.querySelector('[data-carousel-status]');

                    const apply = () => {
                        const q = (search?.value ?? '').trim().toLowerCase();
                        const selectValues = selects.map(({ key, el }) => ({ key, value: el?.value || 'all' }));
                        let visible = 0;

                        items.forEach(item => {
                            const hay = item.dataset.searchHay || '';
                            const matchSearch = !q || hay.includes(q);
                            const matchSelects = selectValues.every(({ key, value }) =>
                                value === 'all' || item.dataset[key] === value
                            );
                            const show = matchSearch && matchSelects;
                            item.dataset.filterHidden = show ? 'false' : 'true';
                            if (show) visible++;
                        });

                        if (empty) empty.classList.toggle('hidden', visible > 0 || items.length === 0);
                        if (status && items.length === 0) status.classList.add('hidden');
                        root.__signalCarousel?.reset?.();
                    };

                    search?.addEventListener('input', apply);
                    selects.forEach(({ el }) => el?.addEventListener('change', apply));
                    reset?.addEventListener('click', () => {
                        if (search) search.value = '';
                        selects.forEach(({ el }) => { if (el) el.value = 'all'; });
                        apply();
                    });
                };

                // Daftar user
                initFilter('[data-user-filter-root]', {
                    search: '[data-user-search]',
                    selects: [
                        { key: 'role', selector: '[data-user-role]' },
                        { key: 'status', selector: '[data-user-status]' },
                    ],
                    empty: '[data-user-filter-empty]',
                    reset: '[data-user-filter-clear]',
                });

                // Audit transaksi
                initFilter('[data-tx-filter-root]', {
                    search: '[data-tx-search]',
                    selects: [
                        { key: 'type', selector: '[data-tx-type]' },
                    ],
                    empty: '[data-tx-filter-empty]',
                    reset: '[data-tx-filter-clear]',
                });
            })();
        </script>
    </x-dashboard-shell>
</x-layouts.app>
