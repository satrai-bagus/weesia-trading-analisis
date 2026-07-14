<x-layouts.app title="Registrasi - Weesia">
    <main class="relative flex min-h-screen items-center overflow-hidden bg-ink-900 px-6 py-24 text-ink-100">
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]">
            <svg class="h-full w-full" preserveAspectRatio="none">
                <defs>
                    <pattern id="register-grid" width="52" height="52" patternUnits="userSpaceOnUse">
                        <path d="M 52 0 L 0 0 0 52" fill="none" stroke="#17d183" stroke-width="0.8" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#register-grid)" />
            </svg>
        </div>

        <section class="relative mx-auto grid w-full max-w-6xl grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_460px]">
            <div class="reveal flex flex-col justify-center">
                <div class="inline-flex w-fit items-center gap-3">
                    <span class="h-px w-10 bg-gold-500/60"></span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Create Account</span>
                </div>
                <h1 class="mt-6 max-w-3xl font-display text-5xl leading-[1.05] text-ink-50 sm:text-6xl">
                    Mulai akses analisa <span class="text-gold-gradient italic">Weesia.</span>
                </h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-ink-200">
                    Daftar dengan nama, email, dan password untuk mulai masuk ke dashboard Weesia. Setelah akun aktif, kamu bisa melihat analisa trading, menyimpan pantauan, dan mengelola akses analisa dengan lebih rapi.
                </p>
            </div>

            <div class="reveal rounded-3xl border border-gold-500/20 bg-ink-900/80 p-6 shadow-[0_30px_90px_-40px_rgba(0,0,0,0.8)] backdrop-blur-xl sm:p-8">
                <div class="mb-8">
                    <div class="font-display text-3xl text-ink-50">Registrasi User</div>
                    <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Weesia Member Area</div>
                </div>

                @if (! empty($checkoutIntent))
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-gold-500/30 bg-gold-500/10 px-4 py-3 text-sm text-gold-100">
                        <x-icon name="wallet" class="mt-0.5 h-4 w-4 shrink-0 text-gold-200" />
                        <span>Daftar dulu untuk lanjut ke checkout. Akun baru langsung dapat <span class="font-semibold text-gold-50">5 token gratis</span> untuk buka analisa.</span>
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="mb-5 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf
                    <label class="block">
                        <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Nama</span>
                        <span class="login-field flex items-center gap-3 rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-ink-100 transition-all focus-within:border-gold-500/50">
                            <x-icon name="user" class="h-4 w-4 text-ink-300" />
                            <input name="name" type="text" value="{{ old('name') }}" autocomplete="name" required maxlength="120" placeholder="Nama user" class="login-input min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-ink-400">
                        </span>
                    </label>

                    <label class="block">
                        <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Email</span>
                        <span class="login-field flex items-center gap-3 rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-ink-100 transition-all focus-within:border-gold-500/50">
                            <x-icon name="mail" class="h-4 w-4 text-ink-300" />
                            <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="191" placeholder="Email user" class="login-input min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-ink-400">
                        </span>
                    </label>

                    <label class="block">
                        <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Password</span>
                        <span class="login-field flex items-center gap-3 rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-ink-100 transition-all focus-within:border-gold-500/50">
                            <x-icon name="lock" class="h-4 w-4 text-ink-300" />
                            <input name="password" type="password" autocomplete="new-password" required minlength="6" maxlength="191" placeholder="Minimal 6 karakter" class="login-input min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-ink-400">
                        </span>
                    </label>

                    <label class="block">
                        <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Konfirmasi Password</span>
                        <span class="login-field flex items-center gap-3 rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-ink-100 transition-all focus-within:border-gold-500/50">
                            <x-icon name="shield-check" class="h-4 w-4 text-ink-300" />
                            <input name="password_confirmation" type="password" autocomplete="new-password" required minlength="6" maxlength="191" placeholder="Ulangi password" class="login-input min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-ink-400">
                        </span>
                    </label>

                    <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(23,209,131,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(23,209,131,1)]">
                        Buat Akun
                        <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </button>

                    <p class="text-center text-xs leading-relaxed text-ink-400">
                        Dengan mendaftar, kamu memahami bahwa konten Weesia adalah riset &amp; edukasi &mdash; bukan nasihat finansial &mdash; dan menyetujui <a href="{{ route('legal.terms') }}" class="underline underline-offset-2 transition-colors hover:text-gold-200">Ketentuan</a> serta <a href="{{ route('legal.privacy') }}" class="underline underline-offset-2 transition-colors hover:text-gold-200">Kebijakan Privasi</a>.
                    </p>

                    <p class="text-center text-xs leading-relaxed text-ink-400">
                        Sudah punya akun?
                        <a href="{{ route('login', array_filter(['redirect' => request('redirect')])) }}" class="text-gold-200 transition-colors hover:text-gold-100">Masuk di sini</a>
                    </p>

                    <a href="{{ route('landing') }}" class="inline-flex w-full items-center justify-center text-sm text-ink-300 transition-colors hover:text-gold-200">Kembali ke halaman utama</a>
                </form>
            </div>
        </section>
    </main>
</x-layouts.app>
