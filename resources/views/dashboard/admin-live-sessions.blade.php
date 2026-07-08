<x-layouts.app title="Admin - Live Session">
    <x-dashboard-shell active="admin-live-sessions">
        @php
            $filterOptions = [
                'upcoming' => 'Akan Datang',
                'past' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'all' => 'Semua',
            ];
            $providers = [
                'zoom' => 'Zoom',
                'gmeet' => 'Google Meet',
                'other' => 'Lainnya',
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
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Admin &middot; Live Session</span>
                    </div>
                    <h1 class="mt-3 font-display text-3xl text-ink-50 sm:text-4xl">Jadwal Live Trading</h1>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-ink-300">Set jadwal Zoom / Google Meet. Banner akan otomatis muncul di dashboard user 7 hari sebelum sesi.</p>
                </div>
                <a href="#new-session" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.22em] font-semibold text-ink-900 shadow-[0_10px_30px_-10px_rgba(23,209,131,0.7)] transition-all hover:shadow-[0_14px_40px_-10px_rgba(23,209,131,0.95)]">
                    <x-icon name="plus" class="h-3.5 w-3.5" />
                    Buat Jadwal
                </a>
            </div>

            <div class="reveal mt-6 grid grid-cols-3 gap-px overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-700/40">
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Live Sekarang</div>
                    <div class="mt-2 font-display text-3xl text-rose-300">{{ $stats['live_now'] }}</div>
                </div>
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Akan Datang</div>
                    <div class="mt-2 font-display text-3xl text-gold-200">{{ $stats['upcoming'] }}</div>
                </div>
                <div class="bg-ink-900 p-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Total Session</div>
                    <div class="mt-2 font-display text-3xl text-ink-50">{{ $stats['total'] }}</div>
                </div>
            </div>

            <div id="new-session" class="reveal mt-6 rounded-3xl border border-gold-500/25 bg-ink-900/75 p-6 backdrop-blur-xl sm:p-8">
                <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Jadwal Baru</div>
                <h2 class="mt-2 font-display text-2xl text-ink-50">Buat Sesi Live Trading</h2>

                <form method="POST" action="{{ route('admin.live-sessions.store') }}" enctype="multipart/form-data" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf

                    <label class="block sm:col-span-2">
                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Judul Sesi <span class="text-rose-300">*</span></span>
                        <input type="text" name="title" value="{{ old('title') }}" required maxlength="140" placeholder="Live Trading BTC Setup Weekly"
                               class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Deskripsi (opsional)</span>
                        <textarea name="description" rows="3" maxlength="2000" placeholder="Bahas setup market hari ini, Q&A live..."
                                  class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">{{ old('description') }}</textarea>
                    </label>

                    <label class="block">
                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Tanggal & Jam <span class="text-rose-300">*</span></span>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required
                               class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                    </label>

                    <label class="block">
                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Durasi (menit) <span class="text-rose-300">*</span></span>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" required min="5" max="600"
                               class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                    </label>

                    <label class="block">
                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Platform</span>
                        <select name="provider" class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                            <option value="">- Pilih -</option>
                            @foreach ($providers as $value => $label)
                                <option value="{{ $value }}" @selected(old('provider') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Link Meeting <span class="text-rose-300">*</span></span>
                        <input type="url" name="meeting_link" value="{{ old('meeting_link') }}" required maxlength="500" placeholder="https://zoom.us/j/..."
                               class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 font-mono text-xs text-ink-100 outline-none focus:border-gold-500/50">
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Banner Promo (opsional)</span>
                        <span class="mt-1 block text-[11px] text-ink-400">Rasio 16:9 disarankan (1200x675 px). JPG/PNG max 4MB. Akan ditampilkan sebagai visual utama di banner user.</span>
                        <input type="file" name="cover_image" accept="image/*"
                               class="mt-2 block w-full rounded-2xl border border-ink-700/70 bg-ink-800/60 px-3 py-2 text-xs text-ink-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gold-500/15 file:px-3 file:py-1.5 file:text-xs file:text-gold-200">
                    </label>

                    <div class="flex items-center justify-end sm:col-span-2">
                        <button class="inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(23,209,131,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(23,209,131,1)]">
                            <x-icon name="calendar" class="h-4 w-4" />
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>

            <div class="reveal mt-8 flex flex-wrap items-center gap-2">
                @foreach ($filterOptions as $value => $label)
                    @php $isActive = $filter === $value; @endphp
                    <a href="{{ route('admin.live-sessions', ['filter' => $value]) }}" class="rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-all {{ $isActive ? 'border-gold-500/50 bg-gold-500/15 text-gold-100' : 'border-ink-700/70 bg-ink-800/50 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="reveal mt-4 space-y-3">
                @forelse ($sessions as $session)
                    @php
                        $status = $session->status();
                        $tone = match ($session->statusTone()) {
                            'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
                            'gold' => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
                            default => 'border-ink-700/70 bg-ink-800/40 text-ink-300',
                        };
                        $isCancelled = $status === \App\Models\LiveSession::STATUS_CANCELLED;
                        $isEnded = $status === \App\Models\LiveSession::STATUS_ENDED;
                    @endphp
                    <article class="rounded-2xl border border-ink-700/60 bg-ink-900/70 p-4 backdrop-blur-md sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            @if ($session->cover_image_path)
                                <div class="overflow-hidden rounded-xl border border-ink-700/60 bg-ink-800/40 sm:w-40 sm:shrink-0">
                                    <img src="{{ asset('storage/'.$session->cover_image_path) }}" alt="{{ $session->title }}" class="aspect-video w-full object-cover">
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] {{ $tone }}">{{ $session->statusLabel() }}</span>
                                    <span class="rounded-full border border-ink-700/70 bg-ink-800/60 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $session->providerLabel() }}</span>
                                    <span class="rounded-full border border-ink-700/70 bg-ink-800/60 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $session->duration_minutes }} menit</span>
                                </div>
                                <h3 class="mt-2 font-display text-xl text-ink-50">{{ $session->title }}</h3>
                                @if ($session->description)
                                    <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-ink-300">{{ $session->description }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-icon name="clock" class="h-3 w-3" />
                                        {{ $session->scheduledAtLocal()->translatedFormat('d M Y - H:i') }} WIB
                                    </span>
                                    @if ($session->createdBy)
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-icon name="user" class="h-3 w-3" />
                                            {{ $session->createdBy->name }}
                                        </span>
                                    @endif
                                    <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener" class="inline-flex max-w-full items-center gap-1.5 truncate text-gold-300 transition-colors hover:text-gold-100" title="{{ $session->meeting_link }}">
                                        <x-icon name="arrow-up-right" class="h-3 w-3 shrink-0" />
                                        <span class="truncate">{{ $session->meeting_link }}</span>
                                    </a>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                @unless ($isCancelled || $isEnded)
                                    <form method="POST" action="{{ route('admin.live-sessions.cancel', $session) }}" onsubmit="return confirm('Batalkan sesi ini? User tidak bisa join lagi.')">
                                        @csrf
                                        <button class="inline-flex items-center gap-2 rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-rose-200 transition-all hover:border-rose-400/60 hover:text-rose-100">
                                            <x-icon name="x" class="h-3 w-3" />
                                            Batalkan
                                        </button>
                                    </form>
                                @endunless

                                <form method="POST" action="{{ route('admin.live-sessions.destroy', $session) }}" onsubmit="return confirm('Hapus permanen? Tidak bisa diundo.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex items-center gap-2 rounded-full border border-ink-700/70 bg-ink-800/60 px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-all hover:border-rose-500/40 hover:text-rose-200">
                                        <x-icon name="trash" class="h-3 w-3" />
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        @unless ($isCancelled || $isEnded)
                            <details class="group mt-3 rounded-2xl border border-ink-700/60 bg-ink-800/30">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300 transition-colors hover:text-gold-200 [&::-webkit-details-marker]:hidden">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="edit" class="h-3 w-3" />
                                        Edit Jadwal
                                    </span>
                                    <span class="font-mono text-[9px] text-ink-500 group-open:hidden">Klik untuk buka</span>
                                </summary>
                                <form method="POST" action="{{ route('admin.live-sessions.update', $session) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-3 border-t border-ink-700/60 p-4 sm:grid-cols-2">
                                    @csrf
                                    @method('PATCH')

                                    <label class="block sm:col-span-2">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Judul</span>
                                        <input type="text" name="title" value="{{ $session->title }}" required maxlength="140"
                                               class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    </label>
                                    <label class="block sm:col-span-2">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Deskripsi</span>
                                        <textarea name="description" rows="2" maxlength="2000"
                                                  class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">{{ $session->description }}</textarea>
                                    </label>
                                    <label class="block">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Jadwal</span>
                                        <input type="datetime-local" name="scheduled_at" value="{{ $session->scheduledAtLocal()->format('Y-m-d\TH:i') }}" required
                                               class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    </label>
                                    <label class="block">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Durasi (menit)</span>
                                        <input type="number" name="duration_minutes" value="{{ $session->duration_minutes }}" required min="5" max="600"
                                               class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                    </label>
                                    <label class="block">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Platform</span>
                                        <select name="provider" class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 text-sm text-ink-100 outline-none focus:border-gold-500/50">
                                            <option value="">- Pilih -</option>
                                            @foreach ($providers as $value => $label)
                                                <option value="{{ $value }}" @selected($session->provider === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Link Meeting</span>
                                        <input type="url" name="meeting_link" value="{{ $session->meeting_link }}" required maxlength="500"
                                               class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-2 font-mono text-xs text-ink-100 outline-none focus:border-gold-500/50">
                                    </label>

                                    <div class="sm:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-[140px_minmax(0,1fr)]">
                                        <div class="overflow-hidden rounded-xl border border-ink-700/60 bg-ink-800/40">
                                            @if ($session->cover_image_path)
                                                <img src="{{ asset('storage/'.$session->cover_image_path) }}" alt="Cover" class="aspect-video w-full object-cover">
                                            @else
                                                <div class="flex aspect-video items-center justify-center font-mono text-[10px] uppercase tracking-[0.2em] text-ink-500">No image</div>
                                            @endif
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block">
                                                <span class="block font-mono text-[10px] uppercase tracking-[0.22em] text-gold-300">Ganti Banner (opsional)</span>
                                                <input type="file" name="cover_image" accept="image/*"
                                                       class="mt-1 block w-full rounded-xl border border-ink-700/70 bg-ink-900 px-3 py-1.5 text-xs text-ink-200 file:mr-3 file:rounded-lg file:border-0 file:bg-gold-500/15 file:px-2 file:py-1 file:text-[11px] file:text-gold-200">
                                            </label>
                                            @if ($session->cover_image_path)
                                                <label class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-rose-300">
                                                    <input type="checkbox" name="remove_cover_image" value="1" class="h-3.5 w-3.5 rounded border-ink-600 bg-ink-800 text-rose-400 focus:ring-rose-400">
                                                    Hapus banner
                                                </label>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end sm:col-span-2">
                                        <button class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-100 transition-all hover:border-gold-400/70">
                                            <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                            Update
                                        </button>
                                    </div>
                                </form>
                            </details>
                        @endunless
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-ink-700/60 bg-ink-800/30 px-6 py-12 text-center">
                        <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full border border-ink-700/70 bg-ink-800/60 text-ink-400">
                            <x-icon name="calendar" class="h-5 w-5" />
                        </div>
                        <p class="mt-3 text-sm text-ink-300">Belum ada sesi {{ $filter === 'all' ? '' : strtolower($filterOptions[$filter] ?? '') }}.</p>
                    </div>
                @endforelse
            </div>

            <div class="reveal mt-6">
                {{ $sessions->links() }}
            </div>
        </section>
    </x-dashboard-shell>
</x-layouts.app>
