@php
    $currentUser = $user ?? auth()->user();
    $roleName = $currentUser?->role?->name ?? '';

    // Deteksi apakah akun role 'siswa' sedang login sebagai 'orang_tua' atau 'siswa'
    $activeLoginAs = session('login_as') ?? $currentUser?->last_login_as ?? $roleName;
    $isParentMode = ($roleName === 'siswa' && $activeLoginAs === 'orang_tua') || $roleName === 'orang_tua';

    $dashboardRoute = match (true) {
        $roleName === 'waka' => 'dashboard.waka',
        $roleName === 'bk' => 'dashboard.bk',
        $roleName === 'wali_kelas' => 'dashboard.wali',
        $roleName === 'guru' => 'dashboard.guru',
        $isParentMode => 'dashboard.ortu',
        $roleName === 'siswa' => 'dashboard.siswa',
        default => null,
    };

    // Nama & Label Role yang ditampilkan di kartu bawah sidebar
    $displayUserName = $currentUser?->name ?? 'Pengguna';
    $displayRoleLabel = $currentUser?->role?->display_name ?? $currentUser?->role?->name ?? 'Pengguna';
    $displaySubInfo = null;

    if ($isParentMode) {
        $displayUserName = $currentUser?->parent_name ?: $currentUser?->name;
        $displayRoleLabel = 'Orang Tua / Wali';
        if (! empty($currentUser?->parent_name) && ! empty($currentUser?->name) && $currentUser->parent_name !== $currentUser->name) {
            $displaySubInfo = 'Ortu dari: ' . $currentUser->name;
        } elseif ($currentUser?->role?->name === 'orang_tua') {
            $childNames = $currentUser->students()->pluck('name')->join(', ');
            if ($childNames !== '') {
                $displaySubInfo = 'Ortu dari: ' . $childNames;
            }
        }
    } elseif ($roleName === 'siswa') {
        $displayUserName = $currentUser?->name;
        $displayRoleLabel = 'Siswa';
        if (! empty($currentUser?->nis)) {
            $displaySubInfo = 'NIS: ' . $currentUser->nis;
        }
    } elseif (in_array($roleName, ['guru', 'wali_kelas'], true) && ! empty($currentUser?->nip)) {
        $displaySubInfo = 'NIP: ' . $currentUser->nip;
    }

    $menuItems = [
        ['route' => 'schedules.index', 'label' => 'Jadwal Pelajaran', 'icon' => 'bi-calendar-week', 'roles' => ['waka', 'guru', 'wali_kelas', 'siswa', 'orang_tua']],
        ['route' => 'journals.index', 'label' => 'Jurnal Mengajar', 'icon' => 'bi-journal-text', 'roles' => ['waka']],
        ['route' => 'master-data.index', 'label' => 'Manajemen Data', 'icon' => 'bi-folder2-open', 'roles' => ['waka']],
        ['route' => 'audit-logs.index', 'label' => 'Audit Log', 'icon' => 'bi-shield-check', 'roles' => ['waka']],
        ['route' => 'attendances.index', 'label' => 'Presensi & Jurnal', 'icon' => 'bi-calendar-check', 'roles' => ['guru']],
        ['route' => 'attendances.index', 'label' => 'Presensi Siswa', 'icon' => 'bi-calendar-check', 'roles' => ['bk', 'siswa', 'orang_tua']],
        ['route' => 'leaves.index', 'label' => 'Izin dan Sakit', 'icon' => 'bi-envelope-paper', 'roles' => ['bk', 'wali_kelas', 'siswa', 'orang_tua']],
        ['route' => 'reports.attendance', 'label' => 'Laporan Presensi', 'icon' => 'bi-file-earmark-text', 'roles' => ['bk', 'wali_kelas']],
        ['route' => 'notifications.index', 'label' => 'Notifikasi', 'icon' => 'bi-bell', 'roles' => ['bk', 'wali_kelas', 'guru', 'siswa', 'orang_tua']],
        ['route' => 'settings.index', 'label' => 'Pengaturan Akun', 'icon' => 'bi-gear', 'roles' => ['waka', 'bk', 'wali_kelas', 'guru', 'siswa', 'orang_tua']],
    ];
@endphp

<style>
    /* ============================================================
       SCP SYSTEM - GLOBAL WAKA THEME & ANTI-COLLISION LAYOUT
    ============================================================ */
    :root {
        --scp-primary: #174ea6;
        --scp-primary-hover: #123d80;
        --scp-blue: #2563eb;
        --scp-blue-light: #eff6ff;
        --scp-background: #f4f7fb;
        --scp-surface: #ffffff;
        --scp-text: #1e293b;
        --scp-text-dark: #172b4d;
        --scp-muted: #64748b;
        --scp-border: #e5eaf2;
        --scp-border-light: #edf1f6;
        --scp-success: #16a36a;
        --scp-success-bg: #e8f8ef;
        --scp-danger: #dc2626;
        --scp-danger-bg: #fef2f2;
        --scp-warning: #d97706;
        --scp-warning-bg: #fffbeb;
        --scp-sidebar-width: 280px;

        --primary: var(--scp-primary);
        --primary-dark: var(--scp-primary-hover);
        --primary-light: var(--scp-blue-light);
        --background: var(--scp-background);
        --surface: var(--scp-surface);
        --text: var(--scp-text);
        --muted: var(--scp-muted);
        --border: var(--scp-border);
        --success: var(--scp-success);
        --danger: var(--scp-danger);
        --sidebar-width: var(--scp-sidebar-width);

        --waka-primary: var(--scp-primary);
        --waka-blue: var(--scp-blue);
        --waka-background: var(--scp-background);
        --waka-text: var(--scp-text);
        --waka-muted: var(--scp-muted);
        --waka-border: var(--scp-border);
    }

    *, *::before, *::after {
        box-sizing: border-box;
    }

    html, body {
        width: 100%;
        min-width: 320px;
        min-height: 100%;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--scp-background) !important;
        color: var(--scp-text);
        font-size: 13px;
    }

    body {
        display: block !important;
        overflow-x: hidden !important;
    }

    /* MAIN CONTENT CONTAINER (ANTI-TABRAAN DENGAN SIDEBAR FIXED 280PX) */
    .main-content,
    .master-main,
    .schedule-main,
    .journal-main,
    .audit-main,
    .settings-main,
    .accounts-main {
        display: block !important;
        width: calc(100% - var(--scp-sidebar-width)) !important;
        min-width: 0 !important;
        min-height: 100vh !important;
        margin-left: var(--scp-sidebar-width) !important;
        padding: 30px 34px 45px !important;
        overflow-x: hidden !important;
        background: var(--scp-background) !important;
        box-sizing: border-box !important;
    }

    .content-wrapper {
        width: 100%;
        max-width: 1250px;
        margin: 0 auto;
    }

    /* PAGE HEADER */
    .page-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
        padding-bottom: 22px !important;
        margin-bottom: 26px !important;
        border-bottom: 1px solid var(--scp-border) !important;
    }

    .page-eyebrow,
    .page-breadcrumb {
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        color: var(--scp-primary) !important;
        font-size: 10px !important;
        font-weight: 800 !important;
        letter-spacing: 1.1px !important;
        text-transform: uppercase !important;
        margin-bottom: 4px !important;
    }

    .page-title {
        margin: 2px 0 0 !important;
        color: #172b4d !important;
        font-size: 24px !important;
        font-weight: 800 !important;
        letter-spacing: -.8px !important;
        line-height: 1.25 !important;
    }

    .page-description {
        margin: 4px 0 0 !important;
        color: var(--scp-muted) !important;
        font-size: 12px !important;
        line-height: 1.6 !important;
    }

    .academic-badge,
    .header-date {
        display: inline-flex !important;
        align-items: center !important;
        gap: 9px !important;
        padding: 11px 16px !important;
        border: 1px solid var(--scp-border) !important;
        border-radius: 10px !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .03) !important;
    }

    .academic-badge i,
    .header-date i {
        color: var(--scp-blue) !important;
        font-size: 16px !important;
    }

    /* HERO BANNER (WAKA SIGNATURE) */
    .hero-banner {
        position: relative !important;
        isolation: isolate !important;
        overflow: hidden !important;
        padding: 32px 36px !important;
        margin-bottom: 26px !important;
        border-radius: 16px !important;
        background: linear-gradient(115deg, #174ea6 0%, #2563eb 65%, #4385ed 100%) !important;
        box-shadow: 0 8px 22px rgba(37, 99, 235, .12) !important;
        color: #ffffff !important;
    }

    .hero-banner::before,
    .hero-banner::after {
        position: absolute !important;
        z-index: -1 !important;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, .07) !important;
        border: none !important;
        content: "" !important;
        pointer-events: none !important;
    }

    .hero-banner::before {
        top: -190px !important;
        right: 90px !important;
        width: 310px !important;
        height: 310px !important;
    }

    .hero-banner::after {
        right: -65px !important;
        bottom: -155px !important;
        top: auto !important;
        width: 240px !important;
        height: 240px !important;
    }

    .hero-content {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 28px !important;
        width: 100% !important;
    }

    .hero-text {
        max-width: 760px !important;
        min-width: 0 !important;
    }

    .hero-label,
    .badge-welcome {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 7px 13px !important;
        margin-bottom: 16px !important;
        border: 1px solid rgba(255, 255, 255, .22) !important;
        border-radius: 7px !important;
        background: rgba(255, 255, 255, .14) !important;
        color: #ffffff !important;
        font-size: 9.5px !important;
        font-weight: 800 !important;
        letter-spacing: .8px !important;
        text-transform: uppercase !important;
    }

    .hero-title,
    .hero-banner h2 {
        margin: 0 0 10px !important;
        color: #ffffff !important;
        font-size: 22px !important;
        font-weight: 800 !important;
        letter-spacing: -.6px !important;
        line-height: 1.3 !important;
    }

    .hero-description,
    .hero-banner p {
        max-width: 720px !important;
        margin: 0 !important;
        color: rgba(255, 255, 255, .9) !important;
        font-size: 12px !important;
        line-height: 1.75 !important;
    }

    .hero-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 84px !important;
        width: 84px !important;
        height: 84px !important;
        border: 1px solid rgba(255, 255, 255, .18) !important;
        border-radius: 20px !important;
        background: rgba(255, 255, 255, .13) !important;
        color: #ffffff !important;
        font-size: 36px !important;
    }

    /* CARDS & SECTIONS */
    .content-card {
        padding: 24px 26px;
        border: 1px solid var(--scp-border);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .025);
        margin-bottom: 24px;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 18px;
        margin-bottom: 18px;
        border-bottom: 1px solid #edf1f6;
    }

    .section-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        border-radius: 11px;
        background: #eff6ff;
        color: var(--scp-blue);
        font-size: 20px;
    }

    .section-title {
        margin: 0 0 4px;
        color: #1e293b;
        font-size: 14px;
        font-weight: 800;
    }

    .section-description {
        margin: 0;
        color: var(--scp-muted);
        font-size: 11.5px;
        line-height: 1.6;
    }

    /* DASHBOARD QUICK LINKS */
    .scp-dashboard-links {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)) !important;
        gap: 14px !important;
        margin-bottom: 24px !important;
    }

    .scp-dashboard-link {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 14px 16px !important;
        background: #ffffff !important;
        border: 1px solid var(--scp-border) !important;
        border-radius: 13px !important;
        text-decoration: none !important;
        color: var(--scp-text) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .025) !important;
        transition: all .2s ease !important;
    }

    .scp-dashboard-link:hover {
        border-color: #bfdbfe !important;
        background: #f8fbff !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(37, 99, 235, .08) !important;
        color: var(--scp-primary) !important;
    }

    .scp-dashboard-link-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 38px !important;
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        background: #eff6ff !important;
        color: var(--scp-blue) !important;
        font-size: 17px !important;
        transition: all .2s ease !important;
    }

    .scp-dashboard-link:hover .scp-dashboard-link-icon {
        background: var(--scp-primary) !important;
        color: #ffffff !important;
    }

    .scp-dashboard-link-label {
        flex: 1 1 auto !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        color: var(--scp-text) !important;
    }

    .scp-dashboard-link-arrow {
        flex: 0 0 auto !important;
        color: #94a3b8 !important;
        font-size: 14px !important;
    }

    /* RULE LIST */
    /* ============================================================
       SCP SYSTEM - GLOBAL PAGINATION STYLING (FIX GIANT ARROW & BROKEN LAYOUT)
    ============================================================ */
    .pagination {
        display: flex !important;
        padding-left: 0 !important;
        list-style: none !important;
        gap: 4px !important;
        margin: 0 !important;
        align-items: center !important;
    }

    .page-item .page-link {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 34px !important;
        height: 34px !important;
        padding: 5px 12px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        text-decoration: none !important;
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        transition: all .15s ease-in-out !important;
    }

    .page-item.active .page-link {
        z-index: 3 !important;
        color: #ffffff !important;
        background-color: var(--scp-primary, #174ea6) !important;
        border-color: var(--scp-primary, #174ea6) !important;
        box-shadow: 0 2px 6px rgba(23, 78, 166, 0.25) !important;
    }

    .page-item.disabled .page-link {
        color: #94a3b8 !important;
        pointer-events: none !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        opacity: 0.7 !important;
    }

    .page-item:not(.active):not(.disabled) .page-link:hover {
        color: var(--scp-primary, #174ea6) !important;
        background-color: #eff6ff !important;
        border-color: #bfdbfe !important;
    }

    /* Guard: Strict containment for any SVG in navigation/pagination */
    .pagination svg,
    nav svg,
    [aria-label="Pagination Navigation"] svg,
    [aria-label="Navigasi Halaman"] svg {
        max-width: 14px !important;
        max-height: 14px !important;
        width: 14px !important;
        height: 14px !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }

    .rule-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 0 !important;
    }

    .rule-item {
        display: flex !important;
        align-items: flex-start !important;
        gap: 15px !important;
        padding: 18px 5px !important;
        background: transparent !important;
        border: none !important;
        border-bottom: 1px solid #edf1f6 !important;
        border-radius: 0 !important;
    }

    .rule-item:last-child {
        padding-bottom: 5px !important;
        border-bottom: none !important;
    }

    .rule-check {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 30px !important;
        width: 30px !important;
        height: 30px !important;
        border-radius: 50% !important;
        background: #e8f8ef !important;
        color: #16a36a !important;
        font-size: 13px !important;
    }

    .rule-content {
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }

    .rule-title {
        display: block !important;
        margin-bottom: 4px !important;
        color: #25364d !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        line-height: 1.5 !important;
    }

    .rule-description {
        margin: 0 !important;
        color: #64748b !important;
        font-size: 11.5px !important;
        line-height: 1.7 !important;
    }

    /* FORMS & BUTTONS */
    .form-label {
        font-weight: 700 !important;
        font-size: 11.5px !important;
        color: #334155 !important;
        margin-bottom: 6px !important;
    }

    .form-control,
    .form-select {
        background-color: #ffffff;
        border: 1px solid #dce4ec;
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 8px;
        color: var(--scp-text);
        min-height: 38px;
    }

    .form-control-sm,
    .form-select-sm {
        min-height: 34px !important;
        padding: 5px 10px !important;
        font-size: 11.5px !important;
        border-radius: 7px !important;
    }

    .form-control:focus,
    .form-select:focus {
        background-color: #ffffff;
        border-color: var(--scp-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        outline: none;
    }

    .btn-primary-custom {
        background: linear-gradient(145deg, #2563eb, #174ea6) !important;
        border: 1px solid #174ea6 !important;
        color: #ffffff !important;
        border-radius: 8px !important;
        padding: 8px 16px !important;
        min-height: 38px;
        font-weight: 700 !important;
        font-size: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        transition: all .2s ease !important;
        text-decoration: none !important;
        white-space: nowrap;
        cursor: pointer;
    }

    .btn-primary-custom:hover {
        background: linear-gradient(145deg, #1d4ed8, #123d80) !important;
        border-color: #123d80 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(23, 78, 166, .2) !important;
    }

    .btn-outline-primary-custom,
    .btn-outline-custom {
        background: #ffffff !important;
        border: 1px solid #bfdbfe !important;
        color: var(--scp-primary) !important;
        border-radius: 8px !important;
        padding: 7px 14px !important;
        min-height: 36px;
        font-weight: 700 !important;
        font-size: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: all .2s ease !important;
        text-decoration: none !important;
        white-space: nowrap;
        cursor: pointer;
    }

    .btn-outline-primary-custom:hover,
    .btn-outline-custom:hover {
        background: #eff6ff !important;
        border-color: var(--scp-blue) !important;
        color: var(--scp-blue) !important;
    }

    .btn-primary-custom.btn-sm,
    .btn-outline-primary-custom.btn-sm,
    .btn-outline-custom.btn-sm {
        min-height: 32px !important;
        padding: 5px 12px !important;
        font-size: 11px !important;
    }

    /* TABLES */
    .table-responsive {
        border-radius: 10px;
        border: 1px solid var(--scp-border);
        background: #ffffff;
        overflow-x: auto;
    }

    .table {
        margin-bottom: 0 !important;
        width: 100%;
    }

    .table > :not(caption) > * > * {
        padding: 12px 14px !important;
        background-color: transparent;
        border-bottom: 1px solid #edf1f6;
        font-size: 12px;
        color: var(--scp-text);
        vertical-align: middle;
    }

    .table thead th {
        background-color: #f8fafc !important;
        color: #64748b !important;
        font-weight: 800 !important;
        font-size: 10.5px !important;
        text-transform: uppercase !important;
        letter-spacing: .4px !important;
        border-bottom: 1px solid var(--scp-border) !important;
        white-space: nowrap;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover td {
        background-color: #f8fbff !important;
    }

    /* ALERTS & BADGES */
    .alert-scp-success,
    .notice-success {
        background: #e8f8ef !important;
        color: #16a36a !important;
        border: 1px solid #c3edd3 !important;
        border-radius: 10px !important;
        padding: 12px 16px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        margin-bottom: 20px !important;
    }

    .alert-scp-danger,
    .notice-error {
        background: #fef2f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fecaca !important;
        border-radius: 10px !important;
        padding: 12px 16px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        margin-bottom: 20px !important;
    }

    .scp-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .scp-badge-success { background: #e8f8ef; color: #16a36a; }
    .scp-badge-danger { background: #fef2f2; color: #dc2626; }
    .scp-badge-warning { background: #fffbeb; color: #d97706; }
    .scp-badge-info { background: #eff6ff; color: #2563eb; }

    /* SIDEBAR DESKTOP */
    .scp-mobile-header {
        display: none !important;
    }

    .scp-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
        z-index: 1030 !important;
        display: flex !important;
        flex-direction: column !important;
        width: 280px !important;
        height: 100vh !important;
        padding: 22px 16px 14px !important;
        gap: 18px !important;
        overflow: hidden !important;
        background: #fff !important;
        border-right: 1px solid #e5eaf2 !important;
        box-shadow: 4px 0 20px rgba(23,78,166,.05) !important;
        transform: none !important;
        visibility: visible !important;
    }

    .scp-sidebar-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        min-height: 58px !important;
        padding: 0 4px 18px !important;
        border-bottom: 1px solid #edf1f6 !important;
    }

    .scp-brand {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        min-width: 0 !important;
        color: #1e293b !important;
        text-decoration: none !important;
    }

    .scp-brand-mark {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 44px !important;
        width: 44px !important;
        height: 44px !important;
        border-radius: 12px !important;
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06) !important;
        padding: 4px !important;
        overflow: hidden !important;
    }

    .scp-brand-mark img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        display: block !important;
    }

    .scp-brand-copy,
    .scp-user-copy {
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
    }

    .scp-brand-title {
        color: #1e293b !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        line-height: 1.3 !important;
    }

    .scp-brand-caption {
        margin-top: 3px !important;
        color: #8290a0 !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        letter-spacing: .6px !important;
        text-transform: uppercase !important;
    }

    .scp-menu-close {
        display: none !important;
    }

    .scp-sidebar-nav {
        display: flex !important;
        flex: 1 1 auto !important;
        flex-direction: column !important;
        gap: 5px !important;
        min-height: 0 !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        padding: 2px 0 10px !important;
    }

    .scp-sidebar .scp-sidebar-link {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        width: 100% !important;
        min-height: 45px !important;
        padding: 10px 13px !important;
        border: 0 !important;
        border-radius: 10px !important;
        background: transparent !important;
        color: #64748b !important;
        font-family: inherit !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        line-height: 1.4 !important;
        text-decoration: none !important;
        text-align: left !important;
        transition: .2s ease !important;
    }

    .scp-sidebar .scp-sidebar-link i {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 20px !important;
        color: #8da0b8 !important;
        font-size: 17px !important;
    }

    .scp-sidebar .scp-sidebar-link:hover {
        background: #eff6ff !important;
        color: #174ea6 !important;
    }

    .scp-sidebar .scp-sidebar-link:hover i {
        color: #2563eb !important;
    }

    .scp-sidebar .scp-sidebar-link[aria-current="page"] {
        background: #e8f1ff !important;
        color: #174ea6 !important;
        font-weight: 800 !important;
    }

    .scp-sidebar .scp-sidebar-link[aria-current="page"]::before {
        position: absolute !important;
        top: 9px !important;
        bottom: 9px !important;
        left: 0 !important;
        width: 3px !important;
        border-radius: 0 4px 4px 0 !important;
        background: #2563eb !important;
        content: "" !important;
    }

    .scp-sidebar .scp-sidebar-link[aria-current="page"] i {
        color: #2563eb !important;
    }

    .scp-nav-divider {
        height: 1px !important;
        margin: 8px 5px 10px !important;
        background: #edf1f6 !important;
    }

    .scp-sidebar .scp-dashboard-nav-link {
        background: #eff6ff !important;
        color: #174ea6 !important;
    }

    .scp-user-card {
        display: flex !important;
        align-items: center !important;
        gap: 11px !important;
        min-width: 0 !important;
        padding: 12px !important;
        border: 1px solid #dbeafe !important;
        border-radius: 12px !important;
        background: #f5f9ff !important;
    }

    .scp-user-avatar {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 40px !important;
        width: 40px !important;
        height: 40px !important;
        border-radius: 50% !important;
        background: #dbeafe !important;
        color: #174ea6 !important;
        font-size: 17px !important;
    }

    .scp-user-name {
        overflow: hidden !important;
        color: #1e293b !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .scp-user-role {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        margin-top: 2px !important;
        color: #174ea6 !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
    }

    .scp-user-subinfo {
        overflow: hidden !important;
        margin-top: 2px !important;
        color: #64748b !important;
        font-size: 10.5px !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .scp-sidebar-footer {
        flex: 0 0 auto !important;
        padding-top: 10px !important;
        border-top: 1px solid #edf1f6 !important;
    }

    .scp-sidebar .scp-logout-link {
        color: #b54747 !important;
    }

    .scp-sidebar .scp-logout-link i {
        color: #b54747 !important;
    }

    .scp-sidebar .scp-logout-link:hover {
        background: #fff1f0 !important;
    }

    .scp-sidebar-backdrop {
        display: none !important;
    }

    /* TABLET & MOBILE RESPONSIVE */
    @media (max-width: 991.98px) {
        body.scp-sidebar-open {
            overflow: hidden !important;
            touch-action: none !important;
        }

        .main-content,
        .master-main,
        .schedule-main,
        .journal-main,
        .audit-main,
        .settings-main,
        .accounts-main {
            width: 100% !important;
            height: auto !important;
            min-height: calc(100vh - 64px) !important;
            margin-left: 0 !important;
            padding: 84px 20px 32px !important;
            overflow-y: visible !important;
        }

        .scp-mobile-header {
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            left: 0 !important;
            z-index: 1050 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            min-height: 64px !important;
            padding: 9px 16px !important;
            background: #fff !important;
            border-bottom: 1px solid #e5eaf2 !important;
            box-shadow: 0 3px 12px rgba(23,78,166,.06) !important;
        }

        .scp-menu-toggle {
            position: relative !important;
            z-index: 1052 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            min-height: 40px !important;
            padding: 8px 14px !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 10px !important;
            background: #eff6ff !important;
            color: #174ea6 !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            cursor: pointer !important;
            pointer-events: auto !important;
            user-select: none !important;
            -webkit-tap-highlight-color: transparent !important;
        }

        .scp-menu-toggle:active {
            background: #dbeafe !important;
            transform: scale(0.98) !important;
        }

        .scp-sidebar {
            position: fixed !important;
            top: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            z-index: 1065 !important;
            width: min(290px, 86vw) !important;
            height: 100vh !important;
            height: 100dvh !important;
            padding: 18px 16px 16px !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            transform: translateX(-105%) !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition: transform .24s cubic-bezier(0.4, 0, 0.2, 1), visibility .24s ease !important;
            box-shadow: 8px 0 30px rgba(15, 23, 42, 0.18) !important;
        }

        .scp-sidebar.is-open {
            transform: translateX(0) !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }

        .scp-sidebar-nav {
            flex: 1 1 auto !important;
            min-height: 140px !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        .scp-sidebar .scp-sidebar-link {
            cursor: pointer !important;
            pointer-events: auto !important;
            -webkit-tap-highlight-color: transparent !important;
        }

        .scp-menu-close {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 0 0 38px !important;
            width: 38px !important;
            height: 38px !important;
            border: 1px solid #e5eaf2 !important;
            border-radius: 10px !important;
            background: #f8fafc !important;
            color: #475569 !important;
            cursor: pointer !important;
            pointer-events: auto !important;
        }

        .scp-menu-close:hover,
        .scp-menu-close:active {
            background: #eff6ff !important;
            border-color: #bfdbfe !important;
            color: #174ea6 !important;
        }

        .scp-sidebar-backdrop {
            position: fixed !important;
            inset: 0 !important;
            z-index: 1060 !important;
            display: block !important;
            width: 100vw !important;
            height: 100vh !important;
            height: 100dvh !important;
            border: 0 !important;
            background: rgba(15, 23, 42, 0.45) !important;
            backdrop-filter: blur(2px) !important;
            -webkit-backdrop-filter: blur(2px) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition: opacity .22s ease, visibility .22s ease !important;
        }

        .scp-sidebar-backdrop.is-visible {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }
    }

    @media (max-width: 576px) {
        .main-content,
        .master-main,
        .schedule-main,
        .journal-main,
        .audit-main,
        .settings-main,
        .accounts-main {
            padding: 82px 15px 24px !important;
        }

        .page-header {
            align-items: flex-start !important;
            flex-direction: column !important;
            gap: 12px !important;
        }

        .hero-banner {
            padding: 24px 20px !important;
        }

        .hero-icon {
            display: none !important;
        }

        .content-card,
        .custom-card {
            padding: 20px 16px !important;
        }
    }
</style>

<header class="scp-mobile-header">
    <a class="scp-brand" href="{{ $dashboardRoute ? route($dashboardRoute) : route('login') }}">
        <span class="scp-brand-mark">
            <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMK Negeri 1 Bantul">
        </span>
        <span class="scp-brand-copy">
            <span class="scp-brand-title">SCP System</span>
            <span class="scp-brand-caption">Panel sekolah</span>
        </span>
    </a>

    <button class="scp-menu-toggle" type="button"
        data-scp-sidebar-open aria-controls="scp-sidebar" aria-expanded="false">
        <i class="bi bi-list"></i>
        <span>Menu</span>
    </button>
</header>

<button class="scp-sidebar-backdrop"
    type="button"
    data-scp-sidebar-backdrop
    data-scp-sidebar-close
    aria-label="Tutup menu"></button>

<aside class="scp-sidebar" id="scp-sidebar" data-scp-sidebar>
    <div class="scp-sidebar-header">
        <a class="scp-brand" href="{{ $dashboardRoute ? route($dashboardRoute) : route('login') }}">
            <span class="scp-brand-mark">
                <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMK Negeri 1 Bantul">
            </span>
            <span class="scp-brand-copy">
                <span class="scp-brand-title">SCP System</span>
                <span class="scp-brand-caption">School Class Progress</span>
            </span>
        </a>

        <button class="scp-menu-close" type="button"
            data-scp-sidebar-close aria-label="Tutup menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="scp-sidebar-nav" aria-label="Menu utama">
        @if($dashboardRoute)
            <a class="scp-sidebar-link scp-dashboard-nav-link"
                href="{{ route($dashboardRoute) }}"
                @if(request()->routeIs($dashboardRoute)) aria-current="page" @endif>
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
            <div class="scp-nav-divider"></div>
        @endif

        @foreach($menuItems as $item)
            @if(in_array($roleName, $item['roles'], true))
                <a class="scp-sidebar-link"
                    href="{{ route($item['route']) }}"
                    @if(request()->routeIs($item['route']) || ($item['route'] === 'master-data.index' && request()->routeIs('accounts.*'))) aria-current="page" @endif>
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    @if($currentUser)
        <div class="scp-user-card">
            <span class="scp-user-avatar">
                <i class="bi {{ $isParentMode ? 'bi-people-fill' : 'bi-person-fill' }}"></i>
            </span>
            <span class="scp-user-copy">
                <span class="scp-user-name" title="{{ $displayUserName }}">{{ $displayUserName }}</span>
                <span class="scp-user-role">
                    <i class="bi bi-patch-check-fill"></i>
                    {{ $displayRoleLabel }}
                </span>
                @if($displaySubInfo)
                    <span class="scp-user-subinfo" title="{{ $displaySubInfo }}">{{ $displaySubInfo }}</span>
                @endif
            </span>
        </div>

        <form class="scp-sidebar-footer" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="scp-sidebar-link scp-logout-link" type="submit">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    @endif
</aside>

<script>
    (function () {
        const initScpSidebar = () => {
            const sidebar = document.querySelector('[data-scp-sidebar]');
            const backdrop = document.querySelector('[data-scp-sidebar-backdrop]');
            const openButton = document.querySelector('[data-scp-sidebar-open]');
            const closeButtons = document.querySelectorAll('[data-scp-sidebar-close]');

            if (!sidebar || !backdrop || !openButton || sidebar.dataset.scpSidebarBound === 'true') {
                return;
            }

            sidebar.dataset.scpSidebarBound = 'true';
            let previouslyFocusedElement = null;
            const mobileSidebar = window.matchMedia('(max-width: 991.98px)');
            const openButtonIcon = openButton.querySelector('i');

            const setOpen = (isOpen, restoreFocus = true) => {
                sidebar.classList.toggle('is-open', isOpen);
                backdrop.classList.toggle('is-visible', isOpen);
                openButton.setAttribute('aria-expanded', String(isOpen));
                sidebar.setAttribute('aria-hidden', String(!isOpen && mobileSidebar.matches));
                document.body.classList.toggle('scp-sidebar-open', isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);

                if (openButtonIcon) {
                    openButtonIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
                }

                if (isOpen) {
                    previouslyFocusedElement = document.activeElement;
                    sidebar.querySelector('.scp-sidebar-nav a')?.focus();
                } else if (restoreFocus && previouslyFocusedElement instanceof HTMLElement) {
                    previouslyFocusedElement.focus();
                }
            };

            sidebar.setAttribute('aria-hidden', String(mobileSidebar.matches));

            openButton.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                setOpen(!sidebar.classList.contains('is-open'));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    setOpen(false);
                });
            });

            sidebar.addEventListener('click', (event) => {
                if (event.target.closest('a') && mobileSidebar.matches) {
                    setOpen(false, false);
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
                    setOpen(false);
                }
            });

            if (typeof mobileSidebar.addEventListener === 'function') {
                mobileSidebar.addEventListener('change', () => setOpen(false, false));
            } else if (typeof mobileSidebar.addListener === 'function') {
                mobileSidebar.addListener(() => setOpen(false, false));
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initScpSidebar);
        } else {
            initScpSidebar();
        }
    })();
</script>
