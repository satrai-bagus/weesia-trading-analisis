<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class CryptoMarketData
{
    private const COINS = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'SOL' => 'solana',
        'BNB' => 'binancecoin',
        'XRP' => 'ripple',
        'ADA' => 'cardano',
        'DOGE' => 'dogecoin',
        'AVAX' => 'avalanche-2',
        'LINK' => 'chainlink',
        'MATIC' => 'matic-network',
        'POL' => 'polygon-ecosystem-token',
        'DOT' => 'polkadot',
        'LTC' => 'litecoin',
        'ARB' => 'arbitrum',
        'OP' => 'optimism',
        'NEAR' => 'near',
        'INJ' => 'injective-protocol',
        'SUI' => 'sui',
        'ATOM' => 'cosmos',
        'APT' => 'aptos',
        'SEI' => 'sei-network',
        'TRX' => 'tron',
        'TON' => 'the-open-network',
        'UNI' => 'uniswap',
        'AAVE' => 'aave',
        'FIL' => 'filecoin',
        'ETC' => 'ethereum-classic',
        'ICP' => 'internet-computer',
        'RENDER' => 'render-token',
        'FET' => 'fetch-ai',
        'WIF' => 'dogwifcoin',
        'PEPE' => 'pepe',
        'SHIB' => 'shiba-inu',
    ];

    public function usdtTickers(): array
    {
        return Cache::remember('crypto-market.usdt-tickers', now()->addMinute(), function () {
            $prices = $this->pricesByCoinId(array_values(self::COINS));

            return collect(self::COINS)
                ->map(fn (string $coinId, string $symbol) => [
                    'ticker' => "{$symbol}/USDT",
                    'symbol' => $symbol,
                    'coin_id' => $coinId,
                    'price' => $prices[$coinId] ?? null,
                ])
                ->values()
                ->all();
        });
    }

    public function pricesForTickers(iterable $tickers): array
    {
        $tickerMap = collect($tickers)
            ->mapWithKeys(function (string $ticker) {
                $normalized = $this->normalizeTicker($ticker);
                $symbol = $this->baseSymbol($normalized);
                $coinId = self::COINS[$symbol] ?? null;

                return $coinId ? [$normalized => $coinId] : [];
            });

        if ($tickerMap->isEmpty()) {
            return [];
        }

        $prices = $this->pricesByCoinId($tickerMap->values()->unique()->all());

        return $tickerMap
            ->mapWithKeys(fn (string $coinId, string $ticker) => [$ticker => $prices[$coinId] ?? null])
            ->filter(fn ($price) => $price !== null)
            ->all();
    }

    public function summariesForTickers(iterable $tickers): array
    {
        $tickerMap = collect($tickers)
            ->mapWithKeys(function (string $ticker) {
                $normalized = $this->normalizeTicker($ticker);
                $symbol = $this->baseSymbol($normalized);
                $coinId = self::COINS[$symbol] ?? null;

                return $coinId ? [$normalized => $coinId] : [];
            });

        if ($tickerMap->isEmpty()) {
            return [];
        }

        $market = $this->marketByCoinId($tickerMap->values()->unique()->all());

        return $tickerMap
            ->mapWithKeys(function (string $coinId, string $ticker) use ($market) {
                $summary = $market[$coinId] ?? null;

                if (! $summary || $summary['price'] === null) {
                    return [];
                }

                return [$ticker => $summary];
            })
            ->all();
    }

    public function priceForTicker(string $ticker): ?float
    {
        $normalized = $this->normalizeTicker($ticker);

        return $this->pricesForTickers([$normalized])[$normalized] ?? null;
    }

    public function normalizeTicker(string $ticker): string
    {
        $clean = Str::upper(str_replace([' ', '-', '_'], ['', '/', '/'], trim($ticker)));

        if (str_contains($clean, '/')) {
            [$base] = explode('/', $clean, 2);

            return "{$base}/USDT";
        }

        foreach (['USDT', 'USD'] as $quote) {
            if (Str::endsWith($clean, $quote)) {
                return Str::replaceEnd($quote, '', $clean).'/USDT';
            }
        }

        return "{$clean}/USDT";
    }

    private function pricesByCoinId(array $coinIds): array
    {
        return collect($this->marketByCoinId($coinIds))
            ->mapWithKeys(fn (array $data, string $coinId) => [$coinId => $data['price'] ?? null])
            ->filter(fn ($price) => $price !== null)
            ->all();
    }

    private function marketByCoinId(array $coinIds): array
    {
        $coinIds = collect($coinIds)->filter()->unique()->values();

        if ($coinIds->isEmpty()) {
            return [];
        }

        $cacheKey = 'crypto-market.market.'.md5($coinIds->implode(','));

        return Cache::remember($cacheKey, now()->addSeconds(30), function () use ($coinIds) {
            try {
                $response = Http::timeout(8)
                    ->retry(1, 250, null, false)
                    ->withOptions([
                        'verify' => (bool) config('services.coingecko.verify_ssl', false),
                    ])
                    ->get(rtrim((string) config('services.coingecko.base_url'), '/').'/simple/price', [
                        'ids' => $coinIds->implode(','),
                        'vs_currencies' => 'usd',
                        'include_24hr_change' => 'true',
                        'precision' => 'full',
                    ]);
            } catch (Throwable) {
                return [];
            }

            if (! $response->successful()) {
                return [];
            }

            return collect($response->json())
                ->mapWithKeys(fn (array $data, string $coinId) => [
                    $coinId => [
                        'price' => isset($data['usd']) ? (float) $data['usd'] : null,
                        'change_24h' => isset($data['usd_24h_change']) ? (float) $data['usd_24h_change'] : null,
                    ],
                ])
                ->filter(fn (array $summary) => $summary['price'] !== null)
                ->all();
        });
    }

    private function baseSymbol(string $ticker): string
    {
        return Str::before($ticker, '/');
    }
}
