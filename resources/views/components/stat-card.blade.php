@props(['label', 'value', 'change', 'tone' => 'gold'])

@php
    $toneClass = match ($tone) {
        'emerald' => 'text-emerald-300',
        'rose' => 'text-rose-300',
        default => 'text-gold-200',
    };

    $badgeClass = match ($tone) {
        'emerald' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
        'rose' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
        default => 'border-gold-500/30 bg-gold-500/10 text-gold-200',
    };

    $icon = match ($tone) {
        'emerald' => 'arrow-up-right',
        'rose' => 'activity',
        default => 'bar-chart',
    };
@endphp

<div class="group bg-ink-900 p-4 transition-colors hover:bg-ink-800/50 sm:p-6">
    <div class="flex items-start justify-between gap-4">
        <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-ink-300">{{ $label }}</div>
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border {{ $badgeClass }}">
            <x-icon :name="$icon" class="h-4 w-4" />
        </span>
    </div>
    <div class="mt-4 font-display text-3xl leading-none text-ink-50 sm:mt-5 sm:text-4xl">{{ $value }}</div>
    <div class="mt-3 text-xs {{ $toneClass }}">{{ $change }}</div>
</div>
