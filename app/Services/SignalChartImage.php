<?php

namespace App\Services;

use App\Models\TradeSignal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignalChartImage
{
    public function fallbackEntry(float $takeProfit, ?float $takeProfit2, float $stopLoss, string $side): float
    {
        $target = $takeProfit2 ?: $takeProfit;

        return round(($stopLoss + $target) / 2, 8);
    }

    public function store(
        string $ticker,
        float $entryPrice,
        float $takeProfit,
        ?float $takeProfit2,
        float $stopLoss,
        string $side,
        int $leverage,
    ): string {
        $filename = 'trade-signals/generated-'.Str::slug(str_replace('/', '-', $ticker)).'-'.now()->format('YmdHis').'-'.Str::random(6).'.svg';

        Storage::disk('public')->put($filename, $this->svg(
            $ticker,
            $entryPrice,
            $takeProfit,
            $takeProfit2,
            $stopLoss,
            $side,
            $leverage,
        ));

        return $filename;
    }

    public function svg(
        string $ticker,
        float $entryPrice,
        float $takeProfit,
        ?float $takeProfit2,
        float $stopLoss,
        string $side,
        int $leverage,
    ): string {
        $width = 760;
        $height = 760;
        $chartX = 68;
        $chartY = 186;
        $chartW = 624;
        $chartH = 294;
        $chartBottom = $chartY + $chartH;
        $chart = $this->chartLine($side, $chartX, $chartY, $chartW, $chartH);

        $sideLabel = $side === TradeSignal::SIDE_SHORT ? 'SHORT' : 'LONG';
        $escapedTicker = e($ticker);
        $entryText = $this->price($entryPrice);
        $tpText = $this->price($takeProfit);
        $tp2Text = $takeProfit2 ? $this->price($takeProfit2) : null;
        $slText = $this->price($stopLoss);
        $tp1Roi = $this->percent($this->leveragedMove($entryPrice, $takeProfit, $side, $leverage));
        $tp2Roi = $takeProfit2 ? $this->percent($this->leveragedMove($entryPrice, $takeProfit2, $side, $leverage)) : null;
        $slRoi = $this->percent($this->leveragedMove($entryPrice, $stopLoss, $side, $leverage));
        $metricTicker = $this->metricCard(68, 520, 176, 'Ticker', $ticker, 'ink');
        $metricTp = $this->metricCard(266, 520, 200, 'TP1', $tpText, 'emerald', $tp2Text ? 'TP2 '.$tp2Text : 'ROI '.$tp1Roi);
        $metricSl = $this->metricCard(488, 520, 204, 'SL', $slText, 'gold', 'Risk '.$slRoi);
        $highlightX = $chart['highlight'][0];
        $highlightY = $chart['highlight'][1];
        $finalX = $chart['final'][0];
        $finalY = $chart['final'][1];
        $footerText = $tp2Roi
            ? "Entry {$entryText}  /  {$sideLabel} {$leverage}X  /  TP1 {$tp1Roi}  /  TP2 {$tp2Roi}"
            : "Entry {$entryText}  /  {$sideLabel} {$leverage}X  /  TP1 {$tp1Roi}";
        $escapedFooter = e($footerText);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="{$width}" height="{$height}">
  <defs>
    <linearGradient id="cardBg" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="#050505"/>
      <stop offset="58%" stop-color="#070706"/>
      <stop offset="100%" stop-color="#11100d"/>
    </linearGradient>
    <linearGradient id="chartFill" x1="0" x2="0" y1="0" y2="1">
      <stop offset="0%" stop-color="#d4a72c" stop-opacity="0.34"/>
      <stop offset="62%" stop-color="#d4a72c" stop-opacity="0.12"/>
      <stop offset="100%" stop-color="#d4a72c" stop-opacity="0"/>
    </linearGradient>
    <linearGradient id="lineGold" x1="0" x2="1" y1="0" y2="0">
      <stop offset="0%" stop-color="#efe7b0"/>
      <stop offset="50%" stop-color="#f7efba"/>
      <stop offset="100%" stop-color="#d8ca80"/>
    </linearGradient>
    <filter id="softShadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="18" stdDeviation="20" flood-color="#000000" flood-opacity="0.45"/>
    </filter>
    <filter id="lineGlow" x="-20%" y="-80%" width="140%" height="260%">
      <feGaussianBlur stdDeviation="3" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
    <pattern id="pageGrid" width="54" height="54" patternUnits="userSpaceOnUse">
      <path d="M 54 0 L 0 0 0 54" fill="none" stroke="#d4a72c" stroke-opacity="0.045" stroke-width="1"/>
    </pattern>
  </defs>
  <rect width="{$width}" height="{$height}" fill="#050505"/>
  <rect width="{$width}" height="{$height}" fill="url(#pageGrid)"/>
  <rect x="24" y="18" width="712" height="724" rx="42" fill="url(#cardBg)" stroke="#d4a72c" stroke-opacity="0.18" filter="url(#softShadow)"/>

  <text x="68" y="60" fill="#8f8b82" font-family="Courier New, monospace" font-size="15" letter-spacing="8">LIVE MARKET PULSE</text>
  <text x="68" y="108" fill="#f5f3ee" font-family="Georgia, serif" font-size="42" font-weight="700">FibPath Stream</text>
  <text x="68" y="133" fill="#d4a72c" fill-opacity="0.82" font-family="Courier New, monospace" font-size="11" letter-spacing="4">{$sideLabel} {$leverage}X GENERATED CHART</text>
  <line x1="68" x2="692" y1="154" y2="154" stroke="#f5f3ee" stroke-opacity="0.08"/>

  <g transform="translate(614 40)">
    <circle cx="36" cy="36" r="34" fill="#2fc77c" fill-opacity="0.11" stroke="#2fc77c" stroke-opacity="0.28"/>
    <path d="M18 38 L28 38 L34 24 L43 48 L48 36 L56 36" fill="none" stroke="#6ee7a0" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
  </g>

  <rect x="{$chartX}" y="{$chartY}" width="{$chartW}" height="{$chartH}" rx="28" fill="#060606" fill-opacity="0.72" stroke="#f5f3ee" stroke-opacity="0.06"/>
  <line x1="{$chartX}" x2="692" y1="230" y2="230" stroke="#d4a72c" stroke-opacity="0.07" stroke-dasharray="6 12"/>
  <line x1="{$chartX}" x2="692" y1="280" y2="280" stroke="#d4a72c" stroke-opacity="0.07" stroke-dasharray="6 12"/>
  <line x1="{$chartX}" x2="692" y1="330" y2="330" stroke="#d4a72c" stroke-opacity="0.07" stroke-dasharray="6 12"/>
  <line x1="{$chartX}" x2="692" y1="380" y2="380" stroke="#d4a72c" stroke-opacity="0.07" stroke-dasharray="6 12"/>
  <path d="{$chart['area']}" fill="url(#chartFill)"/>
  <path d="{$chart['line']}" fill="none" stroke="#3a3314" stroke-opacity="0.45" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="{$chart['line']}" fill="none" stroke="url(#lineGold)" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" filter="url(#lineGlow)"/>
  <circle cx="{$highlightX}" cy="{$highlightY}" r="7" fill="#68d391" stroke="#68d391" stroke-opacity="0.22" stroke-width="10"/>
  <circle cx="{$finalX}" cy="{$finalY}" r="6" fill="#f5f0c7"/>
  <line x1="{$chartX}" x2="692" y1="{$chartBottom}" y2="{$chartBottom}" stroke="#d4a72c" stroke-opacity="0.04"/>

  {$metricTicker}
  {$metricTp}
  {$metricSl}

  <text x="68" y="690" fill="#8f8b82" font-family="Courier New, monospace" font-size="11" letter-spacing="3">{$escapedFooter}</text>
  <text x="68" y="716" fill="#fda4af" fill-opacity="0.8" font-family="Courier New, monospace" font-size="11" letter-spacing="3">SL RISK {$slRoi}</text>
  <text x="618" y="716" fill="#d4a72c" fill-opacity="0.78" font-family="Courier New, monospace" font-size="10" letter-spacing="4" text-anchor="end">WEESIA</text>
</svg>
SVG;
    }

    private function chartLine(string $side, int $x, int $y, int $width, int $height): array
    {
        $ratios = $side === TradeSignal::SIDE_SHORT
            ? [0.24, 0.35, 0.30, 0.52, 0.45, 0.67, 0.70, 0.62]
            : [0.64, 0.55, 0.59, 0.40, 0.46, 0.27, 0.24, 0.30];
        $points = [];
        $count = count($ratios);

        foreach ($ratios as $index => $ratio) {
            $pointX = round($x + (($index / ($count - 1)) * $width), 2);
            $pointY = round($y + ($ratio * $height), 2);
            $points[] = [$pointX, $pointY];
        }

        $line = '';
        foreach ($points as $index => $point) {
            $line .= ($index === 0 ? 'M' : ' L').$point[0].','.$point[1];
        }

        $first = $points[0];
        $last = $points[$count - 1];
        $bottom = $y + $height;
        $area = $line.' L'.$last[0].','.$bottom.' L'.$first[0].','.$bottom.' Z';

        return [
            'line' => $line,
            'area' => $area,
            'highlight' => $points[$count - 2],
            'final' => $last,
        ];
    }

    private function metricCard(int $x, int $y, int $width, string $label, string $value, string $tone, ?string $subValue = null): string
    {
        $palette = match ($tone) {
            'emerald' => ['stroke' => '#2fc77c', 'value' => '#62d98f', 'fill' => '#06100a'],
            'gold' => ['stroke' => '#d4a72c', 'value' => '#f5df76', 'fill' => '#100d04'],
            default => ['stroke' => '#f5f3ee', 'value' => '#f5f3ee', 'fill' => '#070707'],
        };
        $escapedLabel = e(strtoupper($label));
        $escapedValue = e($value);
        $escapedSubValue = $subValue ? e($subValue) : null;
        $valueLength = strlen($value);
        $valueSize = $valueLength > 13 ? 18 : ($valueLength > 10 ? 21 : 25);
        $stroke = $palette['stroke'];
        $valueColor = $palette['value'];
        $fill = $palette['fill'];
        $sub = '';

        if ($escapedSubValue) {
            $subY = 84;
            $sub = <<<SVG
    <text x="22" y="{$subY}" fill="#8f8b82" font-family="Courier New, monospace" font-size="10" letter-spacing="2">{$escapedSubValue}</text>
SVG;
        }

        return <<<SVG
  <g transform="translate({$x} {$y})">
    <rect width="{$width}" height="108" rx="24" fill="{$fill}" fill-opacity="0.56" stroke="{$stroke}" stroke-opacity="0.2"/>
    <text x="22" y="37" fill="#8f8b82" font-family="Courier New, monospace" font-size="13" letter-spacing="6">{$escapedLabel}</text>
    <text x="22" y="71" fill="{$valueColor}" font-family="Courier New, monospace" font-size="{$valueSize}" font-weight="700">{$escapedValue}</text>
{$sub}
  </g>
SVG;
    }

    private function leveragedMove(float $entry, float $target, string $side, int $leverage): float
    {
        $move = $side === TradeSignal::SIDE_SHORT
            ? (($entry - $target) / $entry) * 100
            : (($target - $entry) / $entry) * 100;

        return $move * $leverage;
    }

    private function percent(float $value): string
    {
        $prefix = $value > 0 ? '+' : '';

        return $prefix.rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.').'%';
    }

    private function price(float $value): string
    {
        if ($value >= 1000) {
            return number_format($value, 2, '.', ',');
        }

        return rtrim(rtrim(number_format($value, 8, '.', ','), '0'), '.');
    }
}
