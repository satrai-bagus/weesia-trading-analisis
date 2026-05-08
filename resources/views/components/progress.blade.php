@props(['label', 'value'])

<div>
    <div class="mb-2 flex items-center justify-between">
        <span class="text-sm text-ink-200">{{ $label }}</span>
        <span class="font-mono text-sm text-gold-200">{{ $value }}%</span>
    </div>
    <div class="h-2 overflow-hidden rounded-full bg-ink-700">
        <div class="h-full rounded-full bg-gradient-to-r from-gold-500 via-gold-300 to-emerald-300" style="width: {{ $value }}%"></div>
    </div>
</div>
