<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $roleName = $user->role?->name ?? '';

        $notifications = $user->notifications()->latest()->paginate(25);
        $unreadCount = $user->notifications()->where('is_read', false)->count();

        return view('notifications.index', compact('notifications', 'user', 'roleName', 'unreadCount'));
    }

    /**
     * Tandai notifikasi dibaca dan langsung arahkan ke halaman tujuan
     */
    public function goToTarget(Notification $notification): RedirectResponse
    {
        $user = auth()->user();

        // Pastikan hanya pemilik notifikasi yang dapat membuka
        if ($notification->user_id !== $user->id) {
            abort(403, 'Akses notifikasi tidak diizinkan.');
        }

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        $targetUrl = $notification->target_url;

        return redirect()->to($targetUrl);
    }

    /**
     * Tandai semua notifikasi pengguna sebagai dibaca
     */
    public function markAllRead(): RedirectResponse
    {
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
}
