<?php

namespace App\Http\Controllers;

use App\Models\LiveSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminLiveSessionController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.dashboard');
        }

        $filter = $request->query('filter', 'upcoming');

        $query = LiveSession::with('createdBy')->orderBy('scheduled_at', 'desc');

        if ($filter === 'upcoming') {
            $query->whereNull('cancelled_at')->where('scheduled_at', '>=', now()->subHours(2));
        } elseif ($filter === 'past') {
            $query->where('scheduled_at', '<', now()->subHours(2));
        } elseif ($filter === 'cancelled') {
            $query->whereNotNull('cancelled_at');
        }

        $stats = [
            'upcoming' => LiveSession::active()->where('scheduled_at', '>=', now())->count(),
            'live_now' => LiveSession::active()
                ->where('scheduled_at', '<=', now())
                ->where('scheduled_at', '>=', now()->subDay())
                ->get()
                ->filter(fn (LiveSession $session) => $session->endsAt()->gte(now()))
                ->count(),
            'total' => LiveSession::count(),
        ];

        return view('dashboard.admin-live-sessions', [
            'sessions' => $query->paginate(20)->withQueryString(),
            'stats' => $stats,
            'filter' => $filter,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $this->validatePayload($request);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('live-session-covers', 'public');
        }

        LiveSession::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'],
            'provider' => $validated['provider'] ?? null,
            'meeting_link' => $validated['meeting_link'],
            'cover_image_path' => $coverPath,
            'created_by_id' => Auth::id(),
        ]);

        return back()->with('status', 'Live session dibuat.');
    }

    public function update(Request $request, LiveSession $liveSession): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $this->validatePayload($request);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'],
            'provider' => $validated['provider'] ?? null,
            'meeting_link' => $validated['meeting_link'],
        ];

        if ($request->hasFile('cover_image')) {
            if ($liveSession->cover_image_path) {
                Storage::disk('public')->delete($liveSession->cover_image_path);
            }
            $payload['cover_image_path'] = $request->file('cover_image')->store('live-session-covers', 'public');
        } elseif ($request->boolean('remove_cover_image') && $liveSession->cover_image_path) {
            Storage::disk('public')->delete($liveSession->cover_image_path);
            $payload['cover_image_path'] = null;
        }

        $liveSession->update($payload);

        return back()->with('status', 'Live session diupdate.');
    }

    public function cancel(LiveSession $liveSession): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($liveSession->cancelled_at) {
            return back()->withErrors(['session' => 'Sudah dibatalkan.']);
        }

        $liveSession->update(['cancelled_at' => now()]);

        return back()->with('status', 'Live session dibatalkan.');
    }

    public function destroy(LiveSession $liveSession): RedirectResponse
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($liveSession->cover_image_path) {
            Storage::disk('public')->delete($liveSession->cover_image_path);
        }

        $liveSession->delete();

        return back()->with('status', 'Live session dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'provider' => ['nullable', 'in:zoom,gmeet,other'],
            'meeting_link' => ['required', 'string', 'max:500', 'url'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
        ]);

        // Form input is local WIB; parse explicitly so it stores correctly under app.timezone=UTC
        $validated['scheduled_at'] = Carbon::parse($validated['scheduled_at'], 'Asia/Jakarta');

        return $validated;
    }
}
