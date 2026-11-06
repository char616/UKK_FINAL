<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user->role->name !== 'waka', 403, 'WAKA mengirim pengingat jurnal melalui halaman Jurnal Mengajar.');

        $user->notifications()->where('is_read', false)->update(['is_read' => true]);
        $notifications = $user->notifications()->paginate(25);

        return view('notifications.index', compact('notifications', 'user'));
    }
}
