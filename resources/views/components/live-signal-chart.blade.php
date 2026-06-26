@props(['signal', 'entry' => null, 'current' => null, 'frozen' => null])

@php
    $isFrozen = $frozen ?? $signal->isClosed();
    $closePrice = $signal->closePrice();
    $fallbackValue = $entry ?: $signal->entry_price ?: (($signal->take_profit + $signal->stop_loss) / 2);
    $liveValue = $current ?: $fallbackValue;
    $endValue = $isFrozen && $closePrice ? $closePrice : $liveValue;
    $entryValue = $isFrozen
        ? ($signal->entry_price ?: $fallbackValue)
        : $endValue;
    $tp2Value = $signal->take_profit_2 ?: '';
    $sideValue = $signal->position_side ?: \App\Models\TradeSignal::SIDE_LONG;
    $leverageValue = $signal->leverageValue();
    $closeLabel = match ($signal->status) {
        \App\Models\TradeSignal::STATUS_HIT_TP => 'Kena TP1',
        \App\Models\TradeSignal::STATUS_HIT_TP2 => 'Kena TP2',
        \App\Models\TradeSignal::STATUS_HIT_SL => 'Kena SL',
        default => '',
    };
@endphp

<div
    data-fullscreen-live-chart
    data-chart-ticker="{{ $signal->ticker }}"
    data-chart-entry="{{ $entryValue }}"
    data-chart-current="{{ $endValue }}"
    data-chart-show-entry="false"
    data-chart-tp1="{{ $signal->take_profit }}"
    data-chart-tp2="{{ $tp2Value }}"
    data-chart-sl="{{ $signal->stop_loss }}"
    data-chart-side="{{ $sideValue }}"
    data-chart-leverage="{{ $leverageValue }}"
    data-chart-frozen="{{ $isFrozen ? 'true' : 'false' }}"
    data-chart-close-price="{{ $closePrice ?: '' }}"
    data-chart-close-label="{{ $closeLabel }}"
    role="button"
    tabindex="0"
    aria-label="Buka chart {{ $signal->ticker }} fullscreen"
    {{ $attributes->merge([
        'class' => 'group relative min-h-[430px] cursor-zoom-in overflow-hidden border-b border-ink-700/60 bg-ink-900 sm:min-h-[460px] lg:aspect-[3/4] lg:min-h-0 lg:self-start lg:border-b-0 lg:border-r',
        'style' => 'align-self: start; aspect-ratio: 3 / 4;',
    ]) }}
>
    <div
        data-live-signal-chart
        data-chart-ticker="{{ $signal->ticker }}"
        data-chart-entry="{{ $entryValue }}"
        data-chart-current="{{ $endValue }}"
        data-chart-show-entry="false"
        data-chart-tp1="{{ $signal->take_profit }}"
        data-chart-tp2="{{ $tp2Value }}"
        data-chart-sl="{{ $signal->stop_loss }}"
        data-chart-side="{{ $sideValue }}"
        data-chart-leverage="{{ $leverageValue }}"
        data-chart-frozen="{{ $isFrozen ? 'true' : 'false' }}"
        data-chart-close-price="{{ $closePrice ?: '' }}"
        data-chart-close-label="{{ $closeLabel }}"
        class="live-trading-chart h-full min-h-[430px] w-full sm:min-h-[460px] lg:min-h-0"
    ></div>
    <span
        class="absolute top-3 right-3 z-20 inline-flex h-9 items-center gap-2 rounded-full border {{ $isFrozen ? 'border-rose-500/30 text-rose-100' : 'border-gold-500/30 text-gold-100' }} bg-ink-900/85 px-3 text-xs font-medium backdrop-blur-md transition-all hover:bg-ink-800/85"
    >
        @if ($isFrozen)
            <x-icon name="check-circle" class="h-4 w-4" />
            <span class="hidden sm:inline">{{ $closeLabel ?: 'Closed' }}</span>
        @else
            <x-icon name="search" class="h-4 w-4" />
            <span class="hidden sm:inline">Fullscreen</span>
        @endif
    </span>
</div>
