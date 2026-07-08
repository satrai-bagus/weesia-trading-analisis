@props(['title', 'updated' => null])

<x-layouts.app :title="$title . ' - Weesia'" :with-csrf="false">
    <main class="min-h-screen bg-ink-900 text-ink-100">
        <header class="fixed inset-x-0 top-0 z-50 border-b border-gold-500/10 bg-ink-900/80 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-3xl items-center justify-between px-6 sm:h-20">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <x-brand-mark />
                    <span class="leading-none">
                        <span class="block font-mono text-[15px] font-semibold uppercase tracking-[0.34em] text-ink-50">Weesia</span>
                        <span class="mt-1 block font-mono text-[9px] uppercase tracking-[0.25em] text-gold-400/80">Riset Market Crypto</span>
                    </span>
                </a>
                <a href="{{ route('landing') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-ink-600/70 px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-ink-200 transition-all hover:border-gold-500/50 hover:text-gold-100">
                    <x-icon name="arrow-right" class="h-3 w-3 rotate-180" />
                    Beranda
                </a>
            </div>
        </header>

        <section class="mx-auto max-w-3xl px-6 pb-16 pt-32 sm:pt-36">
            <div class="inline-flex items-center gap-3">
                <span class="h-px w-10 bg-gold-500/60"></span>
                <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-gold-300">Legal</span>
            </div>
            <h1 class="mt-5 font-display text-4xl leading-[1.1] text-ink-50 sm:text-5xl">{{ $title }}</h1>
            @if ($updated)
                <p class="mt-4 font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Terakhir diperbarui: {{ $updated }}</p>
            @endif

            <div class="mt-10 space-y-4 text-sm leading-relaxed text-ink-200 [&_h2]:font-display [&_h2]:text-2xl [&_h2]:text-ink-50 [&_h2]:pt-6 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-5 [&_li]:leading-relaxed [&_strong]:text-gold-200">
                {{ $slot }}
            </div>

            <div class="mt-14 rounded-2xl border border-rose-500/20 bg-rose-500/5 p-5 text-sm leading-relaxed text-ink-200">
                Aset kripto berisiko tinggi dan sangat fluktuatif. Seluruh konten Weesia adalah riset dan edukasi
                &mdash; bukan nasihat finansial dan bukan ajakan membeli atau menjual aset apa pun. Kinerja masa lalu
                tidak menjamin hasil di masa depan.
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-ink-700/60 pt-6">
                <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink-300">Dokumen lain:</span>
                @foreach ([['Disclaimer', route('legal.disclaimer')], ['Kebijakan Privasi', route('legal.privacy')], ['Syarat & Ketentuan', route('legal.terms')]] as [$label, $href])
                    <a href="{{ $href }}"
                        class="rounded-full border border-ink-700/70 px-4 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] text-ink-200 transition-colors hover:border-gold-500/50 hover:text-gold-100">{{ $label }}</a>
                @endforeach
            </div>
        </section>

        <footer class="border-t border-ink-700/60 bg-ink-900">
            <div class="mx-auto flex max-w-3xl flex-col items-start justify-between gap-3 px-6 py-8 text-xs text-ink-300 sm:flex-row sm:items-center">
                <span class="font-mono uppercase tracking-[0.2em]">&copy; {{ now()->year }} Weesia</span>
                <span class="font-mono uppercase tracking-[0.2em]">Riset &amp; Edukasi &mdash; Bukan Nasihat Finansial</span>
            </div>
        </footer>
    </main>
</x-layouts.app>
