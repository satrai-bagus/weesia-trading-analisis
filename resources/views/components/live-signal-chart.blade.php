@props(['signal', 'entry' => null, 'current' => null, 'frozen' => null, 'variant' => 'full'])

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

    $variant = in_array($variant, ['mini', 'panel'], true) ? $variant : 'full';
    $isMini = $variant === 'mini';

    $wrapperClass = match ($variant) {
        'mini' => 'group relative block h-[220px] w-full cursor-zoom-in overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-900',
        'panel' => 'group relative block h-[320px] w-full cursor-zoom-in overflow-hidden rounded-2xl border border-ink-700/60 bg-ink-900 sm:h-[380px]',
        default => 'group relative min-h-[430px] cursor-zoom-in overflow-hidden border-b border-ink-700/60 bg-ink-900 sm:min-h-[460px] lg:aspect-[3/4] lg:min-h-0 lg:self-start lg:border-b-0 lg:border-r',
    };

    $innerClass = $variant === 'full'
        ? 'live-trading-chart h-full min-h-[430px] w-full sm:min-h-[460px] lg:min-h-0'
        : 'live-trading-chart h-full w-full';
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
    {{ $variant === 'full'
        ? $attributes->merge(['class' => $wrapperClass, 'style' => 'align-self: start; aspect-ratio: 3 / 4;'])
        : $attributes->merge(['class' => $wrapperClass]) }}
>
    <div
        data-live-signal-chart
        @if ($isMini) data-chart-minimal="true" @endif
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
        class="{{ $innerClass }}"
    ></div>
    @if ($isMini)
        <span
            class="absolute right-2 top-2 z-20 inline-flex h-7 w-7 items-center justify-center rounded-full border {{ $isFrozen ? 'border-rose-500/30 text-rose-100' : 'border-gold-500/30 text-gold-100' }} bg-ink-900/85 backdrop-blur-md transition-all group-hover:border-gold-400/60"
            title="{{ $isFrozen ? ($closeLabel ?: 'Closed') : 'Fullscreen' }}"
        >
            <x-icon :name="$isFrozen ? 'check-circle' : 'search'" class="h-3.5 w-3.5" />
        </span>
    @else
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
    @endif
</div>
