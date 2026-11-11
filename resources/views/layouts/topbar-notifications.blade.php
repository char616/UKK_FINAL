@php
    $currentUser = auth()->user();
    $roleName = $currentUser?->role?->name ?? '';
    $unreadNotifCount = $currentUser ? $currentUser->notifications()->where('is_read', false)->count() : 0;
    $latestNotifications = $currentUser ? $currentUser->notifications()->latest()->take(5)->get() : collect();
@endphp

<div class="d-inline-flex align-items-center gap-2 topbar-notification-container">

    @if($roleName === 'waka')
        <!-- TOMBOL PINTASAN AUDIT LOG KHUSUS WAKA -->
        <a href="{{ route('audit-logs.index') }}" class="btn-topbar-shortcut" title="Rekam Jejak Sistem & Audit Log">
            <i class="bi bi-shield-check text-primary fs-6"></i>
            <span class="d-none d-md-inline small fw-bold text-dark">Audit Log</span>
        </a>
    @endif

    <!-- DROPDOWN LONCENG NOTIFIKASI -->
    <div class="dropdown">
        <button type="button" class="btn-topbar-bell position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Pusat Notifikasi">
            <i class="bi {{ $unreadNotifCount > 0 ? 'bi-bell-fill text-primary' : 'bi-bell text-secondary' }} fs-5"></i>
            @if($unreadNotifCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 10px; padding: 3px 6px;">
                    {{ $unreadNotifCount > 99 ? '99+' : $unreadNotifCount }}
                </span>
            @endif
        </button>

        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-0 mt-2 topbar-dropdown-menu" style="width: 380px; max-width: 95vw; z-index: 1060;">
            <!-- HEADER DROPDOWN -->
            <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bell-fill text-primary"></i>
                    <strong class="text-dark" style="font-size: 13.5px;">Pusat Notifikasi</strong>
                    @if($unreadNotifCount > 0)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 10px;">
                            {{ $unreadNotifCount }} Baru
                        </span>
                    @endif
                </div>

                @if($unreadNotifCount > 0)
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" style="font-size: 11px;" title="Tandai semua telah dibaca">
                            <i class="bi bi-check2-all me-0.5"></i> Baca Semua
                        </button>
                    </form>
                @endif
            </div>

            <!-- DAFTAR NOTIFIKASI TERKINI -->
            <div class="topbar-notif-list" style="max-height: 360px; overflow-y: auto;">
                @forelse($latestNotifications as $notif)
                    <a href="{{ route('notifications.go', $notif) }}" class="dropdown-item d-flex align-items-start gap-2.5 p-3 border-bottom {{ $notif->is_read ? 'bg-white' : 'bg-light' }} topbar-notif-item">
                        <div class="topbar-notif-icon-circle flex-shrink-0 mt-0.5">
                            <i class="bi {{ $notif->category_icon }}"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="fw-bold text-dark text-truncate" style="font-size: 12px; max-width: 190px;">
                                    {{ $notif->title }}
                                </span>
                                <small class="text-muted font-monospace" style="font-size: 9.5px;">
                                    {{ $notif->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <p class="mb-1 text-muted text-break" style="font-size: 11px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $notif->formatted_message }}
                            </p>
                            <span class="topbar-action-hint text-primary fw-semibold" style="font-size: 10.5px;">
                                Buka halaman tujuan <i class="bi bi-arrow-right-short"></i>
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-4 px-3 text-muted">
                        <i class="bi bi-bell-slash fs-2 d-block text-secondary opacity-50 mb-2"></i>
                        <span class="small">Belum ada notifikasi baru untuk akun Anda.</span>
                    </div>
                @endforelse
            </div>

            <!-- FOOTER DROPDOWN -->
            <div class="p-2 bg-light border-top d-flex justify-content-between align-items-center">
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary w-100 fw-bold rounded-2 py-1.5" style="font-size: 11.5px;">
                    <i class="bi bi-inbox-fill me-1"></i> Buka Halaman Notifikasi Penuh
                </a>
            </div>

            @if($roleName === 'waka')
                <div class="p-2 bg-white border-top text-center">
                    <a href="{{ route('audit-logs.index') }}" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                        <i class="bi bi-shield-check text-primary"></i> Kelola Rekam Jejak Audit Log
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

<style>
    .topbar-notification-container {
        position: relative;
    }

    .btn-topbar-bell {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 9px;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    .btn-topbar-bell:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }

    .btn-topbar-shortcut {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 9px;
        text-decoration: none;
        transition: all 0.15s ease-in-out;
    }

    .btn-topbar-shortcut:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }

    .topbar-notif-icon-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        font-size: 15px;
    }

    .topbar-notif-item {
        white-space: normal !important;
        transition: background 0.15s ease;
    }

    .topbar-notif-item:hover {
        background-color: #EFF6FF !important;
    }

    .topbar-notif-item:hover .topbar-action-hint {
        text-decoration: underline;
    }
</style>
