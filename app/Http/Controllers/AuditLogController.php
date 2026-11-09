<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function destroy(AuditLog $auditLog): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role->name !== 'waka') {
            abort(403, 'Akses ditolak. Audit log hanya dapat dihapus oleh WAKA.');
        }

        $auditLog->delete();

        return back()->with('success', 'Data audit log berhasil dihapus.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role->name !== 'waka') {
            abort(403, 'Akses ditolak. Audit log hanya dapat dihapus oleh WAKA.');
        }

        $date = $request->input('date');
        $query = AuditLog::query();

        if (filled($date)) {
            $query->whereDate('created_at', $date);
        }

        $deletedCount = $query->delete();

        $message = filled($date)
            ? "Berhasil menghapus {$deletedCount} data audit log pada tanggal {$date}."
            : "Berhasil menghapus seluruh ({$deletedCount}) data audit log.";

        return redirect()->route('audit-logs.index', array_filter(['date' => $date]))
            ->with('success', $message);
    }
}
