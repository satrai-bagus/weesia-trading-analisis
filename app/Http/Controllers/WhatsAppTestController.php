<?php

namespace App\Http\Controllers;

use App\Services\FonnteWhatsApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class WhatsAppTestController extends Controller
{
    public function send(Request $request, FonnteWhatsApp $whatsApp): JsonResponse|RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'target' => ['required', 'string', 'max:600'],
            'message' => ['required', 'string', 'max:2000'],
            'url' => ['nullable', 'url', 'max:2048'],
            'filename' => ['nullable', 'string', 'max:120'],
            'delay' => ['nullable', 'integer', 'min:0', 'max:60'],
            'countryCode' => ['nullable', 'string', 'max:5'],
            'typing' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'max:5120'],
        ]);

        $payload = [
            'target' => $validated['target'],
            'message' => $validated['message'],
            'url' => $validated['url'] ?? null,
            'filename' => $validated['filename'] ?? null,
            'delay' => $validated['delay'] ?? 2,
            'countryCode' => $validated['countryCode'] ?? config('services.fonnte.country_code', '62'),
            'typing' => $request->boolean('typing'),
        ];

        try {
            $result = $whatsApp->send($payload, $request->file('file'));
        } catch (Throwable $exception) {
            return $this->respondWithFailure($request, $exception->getMessage());
        }

        if (! $result['ok']) {
            $message = is_array($result['body'])
                ? ($result['body']['reason'] ?? $result['body']['message'] ?? 'Fonnte menolak request.')
                : (string) $result['body'];

            return $this->respondWithFailure($request, $message ?: 'Fonnte menolak request.');
        }

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('status', 'Pesan WhatsApp test berhasil dikirim lewat Fonnte.');
    }

    private function respondWithFailure(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 422);
        }

        return back()
            ->withInput()
            ->withErrors(['whatsapp' => $message]);
    }
}
