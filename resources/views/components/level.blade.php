@props(['label', 'value', 'tone' => 'ink'])

@php
    $classes = match ($tone) {
        'emerald' => 'border-emerald-500/25 text-emerald-300',
        'rose' => 'border-rose-500/25 text-rose-300',
        'gold' => 'border-gold-500/25 text-gold-200',
        default => 'border-ink-700 text-ink-100',
    };
@endphp

<div {{ $attributes->merge(['class' => "min-w-0 rounded-2xl border bg-ink-800/50 p-3 {$classes}"]) }}>
    <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-300">{{ $label }}</div>
    <div data-level-value class="mt-1 min-w-0 break-words font-mono text-sm leading-snug sm:text-[15px]">{{ $value }}</div>
</div>
