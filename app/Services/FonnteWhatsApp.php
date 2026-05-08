<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FonnteWhatsApp
{
    public function send(array $payload, ?UploadedFile $file = null): array
    {
        $token = (string) config('services.fonnte.token');

        if ($token === '') {
            throw new RuntimeException('Fonnte token belum dikonfigurasi.');
        }

        $request = Http::timeout((int) config('services.fonnte.timeout', 15))
            ->withHeaders(['Authorization' => $token])
            ->asMultipart();

        if ($file) {
            $request = $request->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $file->getClientOriginalName(),
            );
        }

        $response = $request->post($this->sendUrl(), $this->normalizePayload($payload));

        return $this->formatResponse($response);
    }

    public function sendText(string $target, string $message, array $options = []): array
    {
        return $this->send(array_merge($options, [
            'target' => $target,
            'message' => $message,
        ]));
    }

    private function normalizePayload(array $payload): array
    {
        $defaults = [
            'schedule' => 0,
            'typing' => false,
            'delay' => 2,
            'countryCode' => config('services.fonnte.country_code', '62'),
        ];

        return collect(array_merge($defaults, $payload))
            ->reject(fn ($value) => $value === null || $value === '')
            ->map(fn ($value) => is_bool($value) ? ($value ? 'true' : 'false') : $value)
            ->all();
    }

    private function formatResponse(Response $response): array
    {
        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }

    private function sendUrl(): string
    {
        return rtrim((string) config('services.fonnte.base_url', 'https://api.fonnte.com'), '/').'/send';
    }
}
