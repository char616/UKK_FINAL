<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Pengguna - SCP System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #174EA6;
            --primary-dark: #123D80;
            --primary-light: #EAF2FF;
            --background: #F4F7FB;
            --surface: #FFFFFF;
            --text: #253449;
            --muted: #718096;
            --border: #E2E8F0;
            --success: #16845B;
            --danger: #C24141;
            --sidebar-width: 280px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
        }

        .main-content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 30px 34px 45px;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .page-eyebrow {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .page-title {
            margin: 0 0 6px;
            color: #1E3552;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.7px;
        }

        .academic-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }

        .academic-badge i {
            color: var(--primary);
            font-size: 17px;
        }

        .academic-badge span {
            color: var(--text);
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* HERO BANNER */
        .hero-banner {
            position: relative;
            overflow: hidden;
            padding: 25px 28px;
            margin-bottom: 22px;
            color: #FFFFFF;
            background: linear-gradient(110deg, #174EA6 0%, #2563EB 100%);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(23, 78, 166, .10);
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 10px;
            margin-bottom: 11px;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 5px;
            background: rgba(255,255,255,.12);
            color: #FFFFFF;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .hero-title {
            margin: 0 0 7px;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -.4px;
        }

        .hero-description {
            margin: 0;
            color: rgba(255,255,255,.82);
            font-size: 11px;
            line-height: 1.6;
        }

        .hero-icon {
            display: grid;
            place-items: center;
            width: 55px;
            height: 55px;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 10px;
            background: rgba(255,255,255,.13);
            color: white;
            font-size: 24px;
        }

        /* CARD & SECTION */
        .content-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(31,52,73,.035);
            padding: 20px;
            margin-bottom: 20px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .section-icon {
            display: grid;
            place-items: center;
            width: 37px;
            height: 37px;
            flex-shrink: 0;
            border-radius: 8px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 16px;
        }

        .section-title {
            margin: 0 0 4px;
            color: #203957;
            font-size: 12px;
            font-weight: 800;
        }

        .section-description {
            margin: 0;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.6;
        }

        /* NOTIFICATION ITEM */
        .notif-item {
            padding: 16px 18px;
            border-radius: 8px;
            border: 1px solid var(--border);
            margin-bottom: 12px;
            background: #ffffff;
            transition: all .2s ease;
        }

        .notif-item.unread {
            background: #f4f8ff;
            border-color: #bfdbfe;
            border-left: 4px solid var(--primary);
        }

        .notif-item:hover {
            box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
        }

        @media (max-width: 991.98px) {
            .main-content {
                width: 100% !important;
                margin-left: 0 !important;
                padding: 88px 16px 35px !important;
            }
            .academic-badge {
                display: none;
            }
        }
    </style>
</head>
<body>

    @include('layouts.sidebar', ['user' => $user])

    <main class="main-content">
        <div class="content-wrapper">

            <header class="page-header">
                <div>
                    <div class="page-eyebrow">
                        <i class="bi bi-bell-fill"></i>
                        Pemberitahuan Pengguna
                    </div>
                    <h1 class="page-title">Pusat Notifikasi</h1>
                </div>

                <div class="academic-badge">
                    <i class="bi bi-calendar3"></i>
                    <span>Tahun Ajaran 2026/2027</span>
                </div>
            </header>

            <section class="hero-banner">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-label">
                            <i class="bi bi-broadcast"></i>
                            PEMBERITAHUAN SISTEM
                        </div>

                        <h2 class="hero-title">
                            Riwayat Informasi & Pesan
                        </h2>

                        <p class="hero-description">
                            Dapatkan informasi penting terkait status jadwal pelajaran, penguncian sistem, permohonan perizinan, dan pengumuman sekolah langsung pada akun Anda.
                        </p>
                    </div>

                    <div class="hero-icon">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                </div>
            </section>

            <div class="content-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-inbox-fill"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Daftar Notifikasi Terbaru</h3>
                        <p class="section-description">Pesan sistem dan aktivitas terkini yang ditujukan untuk akun Anda.</p>
                    </div>
                </div>

                <div class="notif-list mb-4">
                    @forelse($notifications as $notif)
                        <div class="notif-item {{ $notif->is_read ? '' : 'unread' }}">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi {{ $notif->is_read ? 'bi-envelope-open text-muted' : 'bi-envelope-fill text-primary' }}"></i>
                                    <h3 class="h6 fw-bold text-dark mb-0" style="font-size: 13px;">{{ $notif->title }}</h3>
                                    @if(!$notif->is_read)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 9px; padding: 2px 7px;">Baru</span>
                                    @endif
                                </div>
                                <span class="text-muted small" style="font-size: 11px;">
                                    <i class="bi bi-clock me-1"></i>{{ $notif->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="mb-0 text-muted small mt-1 ps-4" style="line-height: 1.6;">{{ $notif->message }}</p>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bell-slash fs-1 d-block text-muted opacity-50 mb-2"></i>
                            <span>Belum ada notifikasi atau pesan sistem untuk akun Anda saat ini.</span>
                        </div>
                    @endforelse
                </div>

                <div class="d-flex justify-content-center">
                    {{ $notifications->links() }}
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>