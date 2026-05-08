<x-layouts.app :title="$article->title">
    <x-dashboard-shell :active="auth()->user()->role === 'admin' ? 'admin-education' : 'education'">
        @php
            $articleEmbed = $article->youtubeEmbedUrl();
        @endphp

        <article class="relative mx-auto max-w-7xl px-4 pb-12 pt-24 sm:px-6 sm:pt-28">
            <div class="reveal">
                <a href="{{ route('education.index', ['category' => $article->category]) }}" class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300 transition-colors hover:text-gold-200">
                    <x-icon name="arrow-right" class="h-3 w-3 rotate-180" />
                    {{ $article->categoryLabel() }}
                </a>
            </div>

            <header class="reveal mt-5 grid grid-cols-1 gap-6 border-b border-ink-700/60 pb-8 lg:grid-cols-[minmax(0,1fr)_440px] lg:items-end">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-gold-500/30 bg-gold-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-gold-200">{{ $article->categoryLabel() }}</span>
                        <span class="rounded-full border border-ink-700/70 bg-ink-800/60 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-300">{{ $article->steps->count() }} step</span>
                        @if ($articleEmbed)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-emerald-200">
                                <x-icon name="video" class="h-3 w-3" />
                                Video
                            </span>
                        @endif
                    </div>
                    <h1 class="mt-4 font-display text-4xl leading-tight text-ink-50 sm:text-5xl">{{ $article->title }}</h1>
                    @if ($article->summary)
                        <p class="mt-4 max-w-3xl text-base leading-relaxed text-ink-300">{{ $article->summary }}</p>
                    @endif
                </div>

                <div class="overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-800/40">
                    @if ($article->cover_image_path)
                        <img src="{{ asset('storage/'.$article->cover_image_path) }}" alt="{{ $article->title }}" class="aspect-video w-full object-cover">
                    @else
                        <div class="flex aspect-video items-center justify-center text-ink-500">
                            <x-icon name="clipboard" class="h-10 w-10" />
                        </div>
                    @endif
                </div>
            </header>

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_300px]">
                <div class="space-y-5">
                    @if ($articleEmbed)
                        <section class="reveal overflow-hidden rounded-2xl border border-gold-500/25 bg-ink-900/75">
                            <iframe src="{{ $articleEmbed }}" title="Video {{ $article->title }}" class="aspect-video w-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </section>
                    @endif

                    @foreach ($article->steps as $index => $step)
                        @php
                            $stepEmbed = $step->youtubeEmbedUrl();
                        @endphp
                        <section id="step-{{ $index + 1 }}" class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-5 sm:p-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                                <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gold-500/35 bg-gold-500/10 font-mono text-xs text-gold-200">{{ $index + 1 }}</div>
                                <div class="min-w-0 flex-1">
                                    <h2 class="font-display text-2xl leading-tight text-ink-50">{{ $step->title ?: 'Step '.($index + 1) }}</h2>
                                    @if ($step->body)
                                        <div class="mt-3 space-y-3 text-sm leading-relaxed text-ink-300">
                                            {!! nl2br(e($step->body)) !!}
                                        </div>
                                    @endif

                                    @if ($step->image_path)
                                        <div class="mt-5 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-800/40">
                                            <img src="{{ asset('storage/'.$step->image_path) }}" alt="{{ $step->title ?: 'Step '.($index + 1) }}" class="w-full object-cover">
                                        </div>
                                    @endif

                                    @if ($stepEmbed)
                                        <div class="mt-5 overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-800/40">
                                            <iframe src="{{ $stepEmbed }}" title="Video step {{ $index + 1 }}" class="aspect-video w-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>

                <aside class="lg:sticky lg:top-28 lg:self-start">
                    <div class="reveal rounded-2xl border border-ink-700/60 bg-ink-900/75 p-5">
                        <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Daftar Step</div>
                        <div class="mt-4 space-y-2">
                            @foreach ($article->steps as $index => $step)
                                <a href="#step-{{ $index + 1 }}" class="flex items-center gap-3 rounded-xl border border-ink-700/60 bg-ink-800/40 px-3 py-2 text-sm text-ink-300 transition-all hover:border-gold-500/40 hover:text-gold-100">
                                    <span class="font-mono text-[10px] text-gold-300">{{ $index + 1 }}</span>
                                    <span class="line-clamp-1">{{ $step->title ?: 'Step '.($index + 1) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if ($related->isNotEmpty())
                        <div class="reveal mt-4 rounded-2xl border border-ink-700/60 bg-ink-900/75 p-5">
                            <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-gold-300">Lanjut Baca</div>
                            <div class="mt-4 space-y-3">
                                @foreach ($related as $item)
                                    <a href="{{ route('education.show', $item) }}" class="block rounded-xl border border-ink-700/60 bg-ink-800/40 p-3 transition-all hover:border-gold-500/40">
                                        <div class="font-display text-base leading-tight text-ink-50">{{ $item->title }}</div>
                                        <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-300">{{ $item->steps_count }} step</div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        </article>
    </x-dashboard-shell>
</x-layouts.app>
