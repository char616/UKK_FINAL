<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    // Menampilkan riwayat aktivitas sistem (Khusus WAKA)
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->role->name !== 'waka') {
            abort(403, 'Akses ditolak. Audit log hanya dapat diakses oleh WAKA.');
        }

        $date = $request->query('date');
        $logs = AuditLog::with('user.role')
            ->when($date, fn ($query) => $query->whereDate('created_at', $date))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('audit_logs.index', compact('logs', 'user', 'date'));
    }
}
