<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function markSeen(DashboardController $dashboard): JsonResponse
    {
        $user = Auth::user();
        $user->forceFill(['last_hit_seen_at' => now()])->save();

        return response()->json([
            'ok' => true,
            'alerts' => $dashboard->alertsPayload(),
        ]);
    }
}
