<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    // Menampilkan riwayat aktivitas sistem (Khusus WAKA)
    public function index()
    {
        $user = Auth::user();
        if ($user->role->name !== 'waka') {
            abort(403, 'Akses ditolak. Audit log hanya dapat diakses oleh WAKA.');
        }

        $logs = AuditLog::with('user.role')->latest()->get();

        return view('audit_logs.index', compact('logs', 'user'));
    }
}