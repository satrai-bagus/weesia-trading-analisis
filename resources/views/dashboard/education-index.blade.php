<x-layouts.app title="Edukasi Trading">
    <x-dashboard-shell :active="auth()->user()->role === 'admin' ? 'admin-education' : 'education'">
        <section class="relative mx-auto max-w-7xl px-4 pb-12 pt-24 sm:px-6 sm:pt-28">
            <div class="reveal flex flex-col items-start justify-between gap-4 border-b border-ink-700/60 pb-6 lg:flex-row lg:items-end">
                <div>
                    <div class="inline-flex items-center gap-3">
                        <span class="h-px w-10 bg-gold-500/60"></span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Weesia &middot; Edukasi</span>
                    </div>
                    <h1 class="mt-3 font-display text-3xl text-ink-50 sm:text-4xl">Belajar Trading Lebih Rapi</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-300">Tutorial singkat berbasis langkah, cocok untuk memahami chart, membaca analisa, dan proses beli di exchange.</p>
                </div>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.education') }}" class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.22em] text-gold-100 transition-all hover:border-gold-400/70">
                        <x-icon name="settings" class="h-3.5 w-3.5" />
                        Kelola Edukasi
                    </a>
                @endif
            </div>

            <div class="reveal mt-6 flex flex-wrap items-center gap-2">
                <a href="{{ route('education.index', ['category' => 'all']) }}" class="rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-all {{ $category === 'all' ? 'border-gold-500/50 bg-gold-500/15 text-gold-100' : 'border-ink-700/70 bg-ink-800/50 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">Semua</a>
                @foreach ($categories as $value => $label)
                    <a href="{{ route('education.index', ['category' => $value]) }}" class="rounded-full border px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] transition-all {{ $category === $value ? 'border-gold-500/50 bg-gold-500/15 text-gold-100' : 'border-ink-700/70 bg-ink-800/50 text-ink-300 hover:border-gold-500/40 hover:text-gold-100' }}">{{ $label }}</a>
                @endforeach
            </div>

            <div class="reveal mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($articles as $article)
                    <a href="{{ route('education.show', $article) }}" class="group overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-900/70 transition-all hover:border-gold-500/35 hover:bg-ink-800/70">
                        <div class="relative overflow-hidden bg-ink-800/60">
                            @if ($article->cover_image_path)
                                <img src="{{ asset('storage/'.$article->cover_image_path) }}" alt="{{ $article->title }}" class="aspect-video w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                            @else
                                <div class="flex aspect-video items-center justify-center text-ink-500">
                                    <x-icon name="clipboard" class="h-8 w-8" />
                                </div>
                            @endif
                            <span class="absolute left-3 top-3 rounded-full border border-gold-500/30 bg-ink-900/80 px-3 py-1 font-mono text-[9px] uppercase tracking-[0.18em] text-gold-200 backdrop-blur-md">{{ $article->categoryLabel() }}</span>
                        </div>
                        <div class="p-5">
                            <div class="flex flex-wrap items-center gap-2 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">
                                <span>{{ $article->steps_count }} step</span>
                                @if ($article->youtube_url)
                                    <span class="inline-flex items-center gap-1 text-gold-300">
                                        <x-icon name="video" class="h-3 w-3" />
                                        Video
                                    </span>
                                @endif
                            </div>
                            <h2 class="mt-2 font-display text-xl leading-tight text-ink-50 transition-colors group-hover:text-gold-100">{{ $article->title }}</h2>
                            @if ($article->summary)
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-300">{{ $article->summary }}</p>
                            @endif
                            <div class="mt-4 inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-300">
                                Buka Modul
                                <x-icon name="arrow-right" class="h-3 w-3 transition-transform group-hover:translate-x-1" />
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-ink-700/60 bg-ink-800/30 px-6 py-12 text-center">
                        <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full border border-ink-700/70 bg-ink-800/60 text-ink-400">
                            <x-icon name="clipboard" class="h-5 w-5" />
                        </div>
                        <p class="mt-3 text-sm text-ink-300">Belum ada edukasi published untuk kategori ini.</p>
                    </div>
                @endforelse
            </div>

            <div class="reveal mt-6">
                {{ $articles->links() }}
            </div>
        </section>
    </x-dashboard-shell>
</x-layouts.app>
