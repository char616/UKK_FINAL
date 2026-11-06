<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\TeachingJournalController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// Halaman Login Utama
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.submit');
Route::get('/password/forgot', [PasswordResetController::class, 'showRequestForm'])->name('password.forgot');
Route::post('/password/forgot', [PasswordResetController::class, 'sendOtp'])->middleware('throttle:password-reset-send')->name('password.forgot.submit');
Route::get('/password/otp', [PasswordResetController::class, 'showVerifyForm'])->name('password.otp');
Route::post('/password/otp', [PasswordResetController::class, 'verifyOtp'])->middleware('throttle:password-reset-verify')->name('password.otp.submit');
Route::get('/password/reset', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'updatePassword'])->name('password.reset.submit');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Halaman yang Memerlukan Autentikasi (Auth) & Pembatasan Hak Akses (Middleware Role)
Route::middleware(['auth', 'active', 'password.changed'])->group(function () {
    Route::get('/settings/account', [AccountSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/account/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/settings/account/emails', [AccountSettingsController::class, 'storeEmail'])
        ->middleware('throttle:20,1')
        ->name('settings.emails.store');
    Route::patch('/settings/account/emails/{userEmail}/primary', [AccountSettingsController::class, 'makeEmailPrimary'])
        ->name('settings.emails.primary');
    Route::delete('/settings/account/emails/{userEmail}', [AccountSettingsController::class, 'destroyEmail'])
        ->name('settings.emails.destroy');

    // Dashboard Berdasarkan Role & Identitas Login Aktif
    Route::get('/dashboard/waka', [DashboardController::class, 'waka'])
        ->middleware('role:waka')->name('dashboard.waka');

    Route::get('/dashboard/bk', [DashboardController::class, 'bk'])
        ->middleware('role:bk')->name('dashboard.bk');

    Route::get('/dashboard/wali-kelas', [DashboardController::class, 'wali'])
        ->middleware('role:wali_kelas')->name('dashboard.wali');

    Route::get('/dashboard/guru', [DashboardController::class, 'guru'])
        ->middleware('role:guru')->name('dashboard.guru');

    Route::get('/dashboard/siswa', [DashboardController::class, 'siswa'])
        ->middleware('role:siswa')->name('dashboard.siswa');

    Route::get('/dashboard/orang-tua', [DashboardController::class, 'ortu'])
        ->middleware('role:orang_tua')->name('dashboard.ortu');

    // Rute Master Data WAKA
    Route::get('/master-data', [MasterDataController::class, 'index'])->middleware('role:waka')->name('master-data.index');
    Route::get('/master-data/students/import-template', [StudentImportController::class, 'downloadTemplate'])->middleware('role:waka')->name('master-data.students.import-template');
    Route::post('/master-data/students/import', [StudentImportController::class, 'store'])->middleware('role:waka')->name('master-data.students.import');
    Route::get('/accounts', [UserManagementController::class, 'index'])->middleware('role:waka')->name('accounts.index');
    Route::post('/accounts', [UserManagementController::class, 'store'])->middleware('role:waka')->name('accounts.store');
    Route::put('/accounts/{user}', [UserManagementController::class, 'update'])->middleware('role:waka')->name('accounts.update');
    Route::post('/master-data/majors', [MasterDataController::class, 'storeMajor'])->middleware('role:waka')->name('master-data.majors.store');
    Route::put('/master-data/majors/{major}', [MasterDataController::class, 'updateMajor'])->middleware('role:waka')->name('master-data.majors.update');
    Route::delete('/master-data/majors/{major}', [MasterDataController::class, 'destroyMajor'])->middleware('role:waka')->name('master-data.majors.destroy');
    Route::post('/master-data/classes', [MasterDataController::class, 'storeClass'])->middleware('role:waka')->name('master-data.classes.store');
    Route::put('/master-data/classes/{class}', [MasterDataController::class, 'updateClass'])->middleware('role:waka')->name('master-data.classes.update');
    Route::delete('/master-data/classes/{class}', [MasterDataController::class, 'destroyClass'])->middleware('role:waka')->name('master-data.classes.destroy');
    Route::post('/master-data/subjects', [MasterDataController::class, 'storeSubject'])->middleware('role:waka')->name('master-data.subjects.store');
    Route::put('/master-data/subjects/{subject}', [MasterDataController::class, 'updateSubject'])->middleware('role:waka')->name('master-data.subjects.update');
    Route::delete('/master-data/subjects/{subject}', [MasterDataController::class, 'destroySubject'])->middleware('role:waka')->name('master-data.subjects.destroy');
    Route::post('/master-data/rooms', [MasterDataController::class, 'storeRoom'])->middleware('role:waka')->name('master-data.rooms.store');
    Route::put('/master-data/rooms/{room}', [MasterDataController::class, 'updateRoom'])->middleware('role:waka')->name('master-data.rooms.update');
    Route::delete('/master-data/rooms/{room}', [MasterDataController::class, 'destroyRoom'])->middleware('role:waka')->name('master-data.rooms.destroy');
    Route::put('/master-data/students/{student}/assignment', [MasterDataController::class, 'updateStudentAssignment'])->middleware('role:waka')->name('master-data.students.assignment');
    Route::put('/master-data/classes/{class}/homeroom-teacher', [MasterDataController::class, 'updateHomeroomTeacher'])->middleware('role:waka')->name('master-data.classes.homeroom-teacher');

    // Rute Jadwal Mengajar & Lock Schedule (Waka & Guru)
    Route::get('/schedules', [ScheduleController::class, 'index'])
        ->middleware('role:waka,guru,wali_kelas,siswa,orang_tua')
        ->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->middleware('role:guru')->name('schedules.store');
    Route::put('/schedules/active-round', [ScheduleController::class, 'updateActiveRound'])->middleware('role:waka')->name('schedules.update-active-round');
    Route::post('/schedules/toggle-lock', [ScheduleController::class, 'toggleLock'])->middleware('role:waka')->name('schedules.toggle-lock');

    // Rute Presensi per Jam Pelajaran
    Route::get('/attendances', [AttendanceController::class, 'index'])->middleware('role:waka,bk,wali_kelas,guru,siswa,orang_tua')->name('attendances.index');
    Route::get('/attendances/create/{schedule}', [AttendanceController::class, 'create'])->middleware('role:guru')->name('attendances.create');
    Route::post('/attendances/store/{schedule}', [AttendanceController::class, 'store'])->middleware('role:guru')->name('attendances.store');

    // Rute Pengajuan Izin & Persetujuan (Wali Kelas & BK)
    Route::get('/leaves', [LeaveRequestController::class, 'index'])->middleware('role:wali_kelas,bk,siswa,orang_tua')->name('leaves.index');
    Route::get('/leaves/create', [LeaveRequestController::class, 'create'])->middleware('role:siswa,orang_tua')->name('leaves.create');
    Route::post('/leaves', [LeaveRequestController::class, 'store'])->middleware('role:siswa,orang_tua')->name('leaves.store');
    Route::post('/leaves/{leaveRequest}/status', [LeaveRequestController::class, 'updateStatus'])->middleware('role:wali_kelas,bk')->name('leaves.status');
    Route::get('/leaves/{leaveRequest}/attachment', [LeaveRequestController::class, 'downloadAttachment'])
        ->middleware('role:wali_kelas,bk,siswa,orang_tua,waka')
        ->name('leaves.attachment');

    // Rute Jurnal Mengajar Guru (Teaching Journal)
    Route::get('/journals', [TeachingJournalController::class, 'index'])->middleware('role:guru,waka')->name('journals.index');
    Route::post('/journals', [TeachingJournalController::class, 'store'])->middleware('role:guru')->name('journals.store');
    Route::put('/journals/{journal}', [TeachingJournalController::class, 'update'])->middleware('role:guru')->name('journals.update');
    Route::post('/journals/reminders', [TeachingJournalController::class, 'sendMissingJournalReminders'])->middleware('role:waka')->name('journals.reminders');
    Route::get('/journals/{journal}/attachment', [TeachingJournalController::class, 'downloadAttachment'])
        ->middleware('role:guru,waka')
        ->name('journals.attachment');

    // Rute Rekapitulasi Global (Waka & BK & Wali Kelas)
    Route::get('/reports/attendance/export', [ReportController::class, 'export'])
        ->middleware('role:bk,wali_kelas')
        ->name('reports.attendance.export');
    Route::get('/reports/attendance', [ReportController::class, 'index'])->middleware('role:bk,wali_kelas')->name('reports.attendance');

    // Rute Notifikasi Pengguna
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Rute Audit Log (Hanya untuk WAKA)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('role:waka')->name('audit-logs.index');
});

// Halaman Utama: tampilkan login bila belum masuk, atau redirect ke dashboard sesuai identitas aktif
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        $role = $user->role->name ?? null;
        $loginAs = session('login_as') ?: $user->last_login_as;

        if (in_array($role, ['siswa', 'orang_tua'], true)) {
            return $loginAs === 'orang_tua'
                ? redirect()->route('dashboard.ortu')
                : redirect()->route('dashboard.siswa');
        }

        return match ($role) {
            'waka' => redirect()->route('dashboard.waka'),
            'bk' => redirect()->route('dashboard.bk'),
            'wali_kelas' => redirect()->route('dashboard.wali'),
            'guru' => redirect()->route('dashboard.guru'),
            default => redirect()->route('login'),
        };
    }

    return view('auth.login');
});