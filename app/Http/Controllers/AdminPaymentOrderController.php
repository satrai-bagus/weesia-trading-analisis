<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use App\Services\Billing;
use App\Services\FonnteWhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminPaymentOrderController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.dashboard');
        }

        $statusFilter = $request->query('status', 'awaiting_review');

        $query = PaymentOrder::with(['user', 'reviewedBy'])->latest();

        if (in_array($statusFilter, [
            PaymentOrder::STATUS_PENDING,
            PaymentOrder::STATUS_AWAITING_REVIEW,
            PaymentOrder::STATUS_APPROVED,
            PaymentOrder::STATUS_REJECTED,
        ], true)) {
            $query->where('status', $statusFilter);
        }

        $stats = [
            'awaiting' => PaymentOrder::where('status', PaymentOrder::STATUS_AWAITING_REVIEW)->count(),
            'pending' => PaymentOrder::where('status', PaymentOrder::STATUS_PENDING)->count(),
            'approved_today' => PaymentOrder::where('status', PaymentOrder::STATUS_APPROVED)
                ->whereDate('reviewed_at', today())->count(),
            'revenue_month' => PaymentOrder::where('status', PaymentOrder::STATUS_APPROVED)
                ->whereMonth('reviewed_at', now()->month)
                ->whereYear('reviewed_at', now()->year)
                ->sum('total_amount'),
        ];

        return view('dashboard.admin-orders', [
            'orders' => $query->paginate(20)->withQueryString(),
            'stats' => $stats,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function approve(Request $request, PaymentOrder $order, Billing $billing, FonnteWhatsApp $wa): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($order->status !== PaymentOrder::STATUS_AWAITING_REVIEW && $order->status !== PaymentOrder::STATUS_PENDING) {
            return back()->withErrors(['order' => 'Order ini sudah diproses.']);
        }

        $note = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:255'],
        ])['admin_note'] ?? null;

        try {
            if ($order->type === PaymentOrder::TYPE_TOKEN) {
                $description = "Top-up {$order->package_label} via order {$order->reference}";
                $billing->topUp($order->user, (int) $order->token_amount, $description, Auth::user());
            } else {
                $description = "Subscription {$order->subscription_months} bulan via order {$order->reference}";
                $billing->extendSubscription($order->user, (int) $order->subscription_months, Auth::user(), $description);
            }
        } catch (RuntimeException $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Approve order failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['order' => 'Gagal approve. Cek log.']);
        }

        $order->forceFill([
            'status' => PaymentOrder::STATUS_APPROVED,
            'admin_note' => $note,
            'reviewed_by_id' => Auth::id(),
            'reviewed_at' => now(),
        ])->save();

        $this->notifyUser($order, $wa, true);

        return back()->with('status', "Order {$order->reference} disetujui.");
    }

    public function reject(Request $request, PaymentOrder $order, FonnteWhatsApp $wa): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($order->status !== PaymentOrder::STATUS_AWAITING_REVIEW && $order->status !== PaymentOrder::STATUS_PENDING) {
            return back()->withErrors(['order' => 'Order ini sudah diproses.']);
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:255'],
        ]);

        $order->forceFill([
            'status' => PaymentOrder::STATUS_REJECTED,
            'admin_note' => $validated['admin_note'],
            'reviewed_by_id' => Auth::id(),
            'reviewed_at' => now(),
        ])->save();

        $this->notifyUser($order, $wa, false);

        return back()->with('status', "Order {$order->reference} ditolak.");
    }

    private function notifyUser(PaymentOrder $order, FonnteWhatsApp $wa, bool $approved): void
    {
        $phone = $order->whatsapp_number;
        if (! $phone) {
            return;
        }

        $message = $approved
            ? "Halo {$order->user->name},\n\nOrder *{$order->reference}* sudah berhasil!\nPaket: {$order->package_label}\n\n".
                ($order->type === PaymentOrder::TYPE_TOKEN
                    ? "{$order->token_amount} token sudah masuk ke akun kamu."
                    : "Subscription kamu diperpanjang {$order->subscription_months} bulan.").
                "\n\nTerima kasih sudah pakai Weesia FibPath."
            : "Halo {$order->user->name},\n\nOrder *{$order->reference}* belum bisa diproses.\nAlasan: {$order->admin_note}\n\nKalau ada pertanyaan, balas pesan ini.";

        try {
            $wa->sendText($phone, $message);
        } catch (Throwable $e) {
            Log::warning('Order WA notify failed', ['order' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
