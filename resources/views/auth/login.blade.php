<x-layouts.app title="Login - Weesia">
    <main class="relative flex min-h-screen items-center overflow-hidden bg-ink-900 px-6 py-24 text-ink-100">
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]">
            <svg class="h-full w-full" preserveAspectRatio="none">
                <defs>
                    <pattern id="login-grid" width="52" height="52" patternUnits="userSpaceOnUse">
                        <path d="M 52 0 L 0 0 0 52" fill="none" stroke="#d4a72c" stroke-width="0.8" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#login-grid)" />
            </svg>
        </div>

        <section class="relative mx-auto grid w-full max-w-6xl grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_440px]">
            <div class="reveal flex flex-col justify-center">
                <div class="inline-flex w-fit items-center gap-3">
                    <span class="h-px w-10 bg-gold-500/60"></span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Member Access</span>
                </div>
                <h1 class="mt-6 max-w-3xl font-display text-5xl leading-[1.05] text-ink-50 sm:text-6xl">
                    Akses analisa crypto <span class="text-gold-gradient italic">FibPath.</span>
                </h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-ink-200">
                    Daftar atau masuk pakai akun Google &mdash; tanpa password ribet. Akun baru otomatis dapat <span class="text-gold-200">5 token gratis</span> untuk coba analisa FibPath pertama kamu.
                </p>

            </div>

            <div class="reveal rounded-3xl border border-gold-500/20 bg-ink-900/80 p-6 shadow-[0_30px_90px_-40px_rgba(0,0,0,0.8)] backdrop-blur-xl sm:p-8">
                <div class="mb-8">
                    <div class="font-display text-3xl text-ink-50">Masuk / Daftar</div>
                    <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">Weesia Member Area</div>
                </div>

                @if (isset($errors) && $errors->any())
                    <div class="mb-5 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <a href="{{ route('auth.google.redirect') }}" class="group relative inline-flex w-full items-center justify-center gap-3 overflow-hidden rounded-full border border-ink-600/70 bg-ink-50 px-6 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_18px_50px_-20px_rgba(0,0,0,0.8)] transition-all hover:border-gold-500/40 hover:shadow-[0_22px_60px_-20px_rgba(212,167,44,0.45)]">
                    <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.6-6 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/>
                        <path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34 6.1 29.3 4 24 4c-7.6 0-14.2 4.3-17.7 10.7z"/>
                        <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.3c-2 1.6-4.5 2.5-7.2 2.5-5.3 0-9.7-3.4-11.3-8.1l-6.5 5C9.8 39.7 16.4 44 24 44z"/>
                        <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.7 2.2-2.1 4.1-4 5.5l6.2 5.3c-.4.4 6.5-4.7 6.5-14.8 0-1.3-.1-2.4-.4-3.5z"/>
                    </svg>
                    <span>Continue with Google</span>
                </a>

                <div class="my-6 flex items-center gap-3">
                    <span class="h-px flex-1 bg-ink-700/70"></span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.24em] text-ink-400">atau pakai email</span>
                    <span class="h-px flex-1 bg-ink-700/70"></span>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf
                    <label class="block">
                        <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Email Akses</span>
                        <span class="login-field flex items-center gap-3 rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-ink-100 transition-all focus-within:border-gold-500/50">
                            <x-icon name="mail" class="h-4 w-4 text-ink-300" />
                            <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required placeholder="Email akses kamu" class="login-input min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-ink-400">
                        </span>
                    </label>

                    <label class="block">
                        <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Password</span>
                        <span class="login-field flex items-center gap-3 rounded-2xl border border-ink-700/70 bg-ink-800/60 px-4 py-3 text-ink-100 transition-all focus-within:border-gold-500/50">
                            <x-icon name="lock" class="h-4 w-4 text-ink-300" />
                            <input name="password" type="password" autocomplete="current-password" required placeholder="Password akses kamu" class="login-input min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-ink-400">
                        </span>
                    </label>

                    <button class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-gold-300 to-gold-500 px-6 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_14px_44px_-16px_rgba(212,167,44,0.8)] transition-all hover:shadow-[0_20px_60px_-18px_rgba(212,167,44,1)]">
                        Masuk ke Dashboard
                        <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </button>

                    <p class="text-center text-xs leading-relaxed text-ink-400">
                        Daftar pakai Google &mdash; otomatis dapat <span class="text-gold-200">5 token gratis</span> untuk coba analisa pertama kamu.
                    </p>

                    <a href="{{ route('landing') }}" class="inline-flex w-full items-center justify-center text-sm text-ink-300 transition-colors hover:text-gold-200">Kembali ke halaman utama</a>
                </form>
            </div>
        </section>
    </main>
</x-layouts.app>
