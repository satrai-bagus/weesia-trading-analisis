@props(['signal', 'entry' => null, 'current' => null])

@php
    $fallbackValue = $entry ?: $signal->entry_price ?: (($signal->take_profit + $signal->stop_loss) / 2);
    $currentValue = $current ?: $fallbackValue;
    $entryValue = $currentValue;
    $tp2Value = $signal->take_profit_2 ?: '';
    $sideValue = $signal->position_side ?: \App\Models\TradeSignal::SIDE_LONG;
    $leverageValue = $signal->leverageValue();
@endphp

<div
    data-fullscreen-live-chart
    data-chart-ticker="{{ $signal->ticker }}"
    data-chart-entry="{{ $entryValue }}"
    data-chart-current="{{ $currentValue }}"
    data-chart-show-entry="false"
    data-chart-tp1="{{ $signal->take_profit }}"
    data-chart-tp2="{{ $tp2Value }}"
    data-chart-sl="{{ $signal->stop_loss }}"
    data-chart-side="{{ $sideValue }}"
    data-chart-leverage="{{ $leverageValue }}"
    role="button"
    tabindex="0"
    aria-label="Buka chart {{ $signal->ticker }} fullscreen"
    {{ $attributes->merge(['class' => 'group relative min-h-[430px] cursor-zoom-in overflow-hidden border-b border-ink-700/60 bg-ink-900 sm:min-h-[460px] lg:min-h-full lg:border-b-0 lg:border-r']) }}
>
    <div
        data-live-signal-chart
        data-chart-ticker="{{ $signal->ticker }}"
        data-chart-entry="{{ $entryValue }}"
        data-chart-current="{{ $currentValue }}"
        data-chart-show-entry="false"
        data-chart-tp1="{{ $signal->take_profit }}"
        data-chart-tp2="{{ $tp2Value }}"
        data-chart-sl="{{ $signal->stop_loss }}"
        data-chart-side="{{ $sideValue }}"
        data-chart-leverage="{{ $leverageValue }}"
        class="live-trading-chart h-full min-h-[430px] w-full sm:min-h-[460px] lg:min-h-full"
    ></div>
    <span
        class="absolute top-3 right-3 z-20 inline-flex h-9 items-center gap-2 rounded-full border border-gold-500/30 bg-ink-900/85 px-3 text-xs font-medium text-gold-100 backdrop-blur-md transition-all hover:border-gold-400/70 hover:bg-gold-500/15"
    >
        <x-icon name="search" class="h-4 w-4" />
        <span class="hidden sm:inline">Fullscreen</span>
    </span>
</div>
