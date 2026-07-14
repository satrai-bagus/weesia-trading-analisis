@props(['ticker', 'size' => 'md'])

@php
    $base = strtoupper(trim(explode('/', str_replace(['-', '_', ' '], '/', (string) $ticker))[0] ?? ''));

    foreach (['USDT', 'USDC', 'BUSD', 'USD', 'IDR'] as $quote) {
        if (str_ends_with($base, $quote) && strlen($base) > strlen($quote) + 1) {
            $base = substr($base, 0, -strlen($quote));
            break;
        }
    }

    $symbol = preg_replace('/[^a-z0-9]/', '', strtolower($base));

    $brandColors = [
        'btc' => '#f7931a', 'eth' => '#627eea', 'bnb' => '#f3ba2f', 'sol' => '#9945ff',
        'xrp' => '#8ba9c5', 'ada' => '#2a71d0', 'doge' => '#c2a633', 'avax' => '#e84142',
        'link' => '#2a5ada', 'matic' => '#8247e5', 'pol' => '#8247e5', 'dot' => '#e6007a',
        'ltc' => '#a6a9aa', 'arb' => '#12aaff', 'op' => '#ff0420', 'near' => '#00ec97',
        'inj' => '#00b3ff', 'sui' => '#4da2ff', 'atom' => '#7c87e8', 'apt' => '#4fd1c5',
        'sei' => '#f05a5a', 'trx' => '#ef0027', 'ton' => '#0098ea', 'uni' => '#ff007a',
        'aave' => '#b6509e', 'fil' => '#0090ff', 'etc' => '#3ab83a', 'icp' => '#f15a24',
        'render' => '#e14545', 'fet' => '#6c5ce7', 'wif' => '#c9a06c', 'pepe' => '#4c9641',
        'shib' => '#ffa409',
    ];

    $fallbackColor = $brandColors[$symbol] ?? '#17d183';

    $sizeClass = match ($size) {
        'sm' => 'h-8 w-8',
        'lg' => 'h-12 w-12',
        default => 'h-10 w-10',
    };

    $textClass = match ($size) {
        'sm' => 'text-[8px]',
        'lg' => 'text-[11px]',
        default => 'text-[9px]',
    };
@endphp

<span {{ $attributes->merge(['class' => "relative inline-flex {$sizeClass} shrink-0 items-center justify-center overflow-hidden rounded-full border border-ink-600/70 bg-ink-800 shadow-[0_8px_20px_-10px_rgba(0,0,0,0.8)]"]) }}>
    <span class="absolute inset-0 flex items-center justify-center font-mono {{ $textClass }} font-bold uppercase tracking-tight" style="color: {{ $fallbackColor }}; background: {{ $fallbackColor }}1f;">{{ substr($base, 0, 4) ?: '?' }}</span>
    @if ($symbol)
        <img src="https://cdn.jsdelivr.net/npm/cryptocurrency-icons@0.18.1/svg/color/{{ $symbol }}.svg"
             alt="{{ $base }}"
             loading="lazy"
             decoding="async"
             referrerpolicy="no-referrer"
             class="relative z-10 h-full w-full"
             onerror="this.remove()">
    @endif
</span>
