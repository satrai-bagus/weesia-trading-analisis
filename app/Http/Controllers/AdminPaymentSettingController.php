<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminPaymentSettingController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.dashboard');
        }

        return view('dashboard.admin-payment-settings', [
            'setting' => PaymentSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'qris' => ['nullable', 'image', 'max:4096'],
            'qris_holder' => ['nullable', 'string', 'max:120'],
            'whatsapp_admin' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'banks' => ['nullable', 'array'],
            'banks.*.bank_name' => ['nullable', 'string', 'max:60'],
            'banks.*.account_number' => ['nullable', 'string', 'max:60'],
            'banks.*.account_name' => ['nullable', 'string', 'max:120'],
            'ewallets' => ['nullable', 'array'],
            'ewallets.*.bank_name' => ['nullable', 'string', 'max:60'],
            'ewallets.*.account_number' => ['nullable', 'string', 'max:60'],
            'ewallets.*.account_name' => ['nullable', 'string', 'max:120'],
        ]);

        $setting = PaymentSetting::current();

        if ($request->hasFile('qris')) {
            if ($setting->qris_path && Storage::disk('public')->exists($setting->qris_path)) {
                Storage::disk('public')->delete($setting->qris_path);
            }

            $setting->qris_path = $request->file('qris')->store('payment-qris', 'public');
        }

        if ($request->boolean('remove_qris') && $setting->qris_path) {
            if (Storage::disk('public')->exists($setting->qris_path)) {
                Storage::disk('public')->delete($setting->qris_path);
            }
            $setting->qris_path = null;
        }

        $setting->qris_holder = $validated['qris_holder'] ?? null;
        $setting->whatsapp_admin = $validated['whatsapp_admin'] ?? null;
        $setting->notes = $validated['notes'] ?? null;

        $setting->bank_accounts = collect($validated['banks'] ?? [])
            ->filter(fn ($row) => ! empty($row['bank_name']) && ! empty($row['account_number']))
            ->map(fn ($row) => [
                'bank_name' => trim($row['bank_name']),
                'account_number' => trim($row['account_number']),
                'account_name' => trim($row['account_name'] ?? ''),
            ])
            ->values()
            ->all();

        $setting->ewallet_accounts = collect($validated['ewallets'] ?? [])
            ->filter(fn ($row) => ! empty($row['bank_name']) && ! empty($row['account_number']))
            ->map(fn ($row) => [
                'bank_name' => trim($row['bank_name']),
                'account_number' => trim($row['account_number']),
                'account_name' => trim($row['account_name'] ?? ''),
            ])
            ->values()
            ->all();

        $setting->save();

        return back()->with('status', 'Settings pembayaran berhasil disimpan.');
    }
}
