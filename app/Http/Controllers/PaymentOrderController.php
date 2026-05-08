<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentOrderController extends Controller
{
    public function checkout(): View
    {
        return view('dashboard.checkout', [
            'subscriptions' => config('packages.subscriptions', []),
            'tokens' => config('packages.tokens', []),
            'orders' => PaymentOrder::where('user_id', Auth::id())
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package_key' => ['required', 'string'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
        ]);

        [$type, $config] = $this->resolvePackage($validated['package_key']);

        if (! $config) {
            return back()->withErrors(['package_key' => 'Paket tidak valid.']);
        }

        $uniqueCode = random_int(1, 999);
        $price = (int) $config['price'];

        $order = PaymentOrder::create([
            'user_id' => Auth::id(),
            'reference' => PaymentOrder::generateReference(),
            'type' => $type,
            'package_key' => $validated['package_key'],
            'package_label' => $config['label'],
            'token_amount' => $type === PaymentOrder::TYPE_TOKEN ? (int) ($config['tokens'] ?? 0) : null,
            'subscription_months' => $type === PaymentOrder::TYPE_SUBSCRIPTION ? (int) ($config['months'] ?? 0) : null,
            'price' => $price,
            'unique_code' => $uniqueCode,
            'total_amount' => $price + $uniqueCode,
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
            'status' => PaymentOrder::STATUS_PENDING,
        ]);

        return redirect()->route('orders.show', $order);
    }

    public function show(PaymentOrder $order): View|RedirectResponse
    {
        abort_unless($order->user_id === Auth::id() || Auth::user()->role === 'admin', 403);

        return view('dashboard.order-show', [
            'order' => $order->load('user'),
            'paymentSetting' => PaymentSetting::current(),
        ]);
    }

    public function uploadProof(Request $request, PaymentOrder $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_if($order->isFinalized(), 422, 'Order sudah selesai diproses.');

        $validated = $request->validate([
            'proof' => ['required', 'image', 'max:4096'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
        ]);

        if ($order->proof_path && Storage::disk('public')->exists($order->proof_path)) {
            Storage::disk('public')->delete($order->proof_path);
        }

        $path = $request->file('proof')->store('payment-proofs', 'public');

        $order->update([
            'proof_path' => $path,
            'whatsapp_number' => $validated['whatsapp_number'] ?? $order->whatsapp_number,
            'status' => PaymentOrder::STATUS_AWAITING_REVIEW,
        ]);

        return back()->with('status', 'Bukti transfer terkirim. Admin akan verifikasi maksimal 1x24 jam.');
    }

    public function index(): View
    {
        return view('dashboard.orders-index', [
            'orders' => PaymentOrder::where('user_id', Auth::id())
                ->latest()
                ->paginate(15),
        ]);
    }

    public function cancel(PaymentOrder $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_if($order->isFinalized(), 422);

        if ($order->status === PaymentOrder::STATUS_PENDING) {
            $order->delete();

            return redirect()->route('user.dashboard')->with('status', 'Order dibatalkan.');
        }

        return back()->withErrors(['order' => 'Order yang sudah ada bukti transfer tidak bisa dibatalkan. Hubungi admin.']);
    }

    private function resolvePackage(string $key): array
    {
        $sub = config("packages.subscriptions.$key");
        if ($sub) {
            return [PaymentOrder::TYPE_SUBSCRIPTION, $sub];
        }

        $token = config("packages.tokens.$key");
        if ($token) {
            return [PaymentOrder::TYPE_TOKEN, $token];
        }

        return [null, null];
    }
}
