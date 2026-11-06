<?php

use App\Http\Controllers\Api\AccountSettingsApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AuditLogApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\LeaveRequestApiController;
use App\Http\Controllers\Api\MasterDataApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\PasswordResetApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\ScheduleApiController;
use App\Http\Controllers\Api\ScpApiController;
use App\Http\Controllers\Api\TeachingJournalApiController;
use App\Http\Controllers\Api\UserManagementApiController;
use Illuminate\Support\Facades\Route;

// Health Check & Pengujian Email
Route::get('/health', [ScpApiController::class, 'health'])->name('api.health');
Route::post('/mail/test-otp', [ScpApiController::class, 'testOtpEmail'])->name('api.mail.test-otp');
Route::post('/mail/test-attendance', [ScpApiController::class, 'testAttendanceEmail'])->name('api.mail.test-attendance');

// Autentikasi Mobile (Sanctum Bearer Token) & Pemulihan Password OTP
Route::post('/auth/login', [AuthApiController::class, 'login'])->name('api.login');
Route::post('/auth/register', [AuthApiController::class, 'register'])->name('api.register');
Route::post('/password/forgot', [PasswordResetApiController::class, 'sendOtp'])->name('api.password.forgot');
Route::post('/password/verify-otp', [PasswordResetApiController::class, 'verifyOtp'])->name('api.password.verify-otp');
Route::post('/password/reset', [PasswordResetApiController::class, 'resetPassword'])->name('api.password.reset');

// Endpoint yang Mendukung Autentikasi Bearer Token (atau Public Preview)
Route::get('/master-data', [MasterDataApiController::class, 'index'])->name('api.master-data');
Route::get('/schedules', [ScheduleApiController::class, 'index'])->name('api.schedules');
Route::get('/attendances', [AttendanceApiController::class, 'index'])->name('api.attendances');
Route::get('/leaves', [LeaveRequestApiController::class, 'index'])->name('api.leaves');
Route::post('/leaves', [LeaveRequestApiController::class, 'store'])->name('api.leaves.store');
Route::get('/journals', [TeachingJournalApiController::class, 'index'])->name('api.journals');

// Endpoint Terproteksi Autentikasi Sanctum Bearer Token
Route::middleware(['auth:sanctum', 'active', 'password.changed'])->group(function () {
    // Profil & Logout
    Route::get('/auth/profile', [AuthApiController::class, 'profile'])->name('api.auth.profile');
    Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('api.auth.logout');

    // Pengaturan Akun & Email Tertaut (untuk OTP reset password)
    Route::get('/settings/account', [AccountSettingsApiController::class, 'show'])->name('api.settings.show');
    Route::put('/settings/account/password', [AccountSettingsApiController::class, 'updatePassword'])->name('api.settings.password.update');
    Route::get('/settings/account/emails', [AccountSettingsApiController::class, 'emails'])->name('api.settings.emails.index');
    Route::post('/settings/account/emails', [AccountSettingsApiController::class, 'storeEmail'])->name('api.settings.emails.store');
    Route::patch('/settings/account/emails/{userEmail}/primary', [AccountSettingsApiController::class, 'makeEmailPrimary'])->name('api.settings.emails.primary');
    Route::delete('/settings/account/emails/{userEmail}', [AccountSettingsApiController::class, 'destroyEmail'])->name('api.settings.emails.destroy');

    // Dashboard Spesifik Berdasarkan 6 Role SCP
    Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('api.dashboard');
    Route::get('/dashboard/waka', [DashboardApiController::class, 'index'])->middleware('role:waka')->name('api.dashboard.waka');
    Route::get('/dashboard/bk', [DashboardApiController::class, 'index'])->middleware('role:bk')->name('api.dashboard.bk');
    Route::get('/dashboard/wali-kelas', [DashboardApiController::class, 'index'])->middleware('role:wali_kelas')->name('api.dashboard.wali');
    Route::get('/dashboard/guru', [DashboardApiController::class, 'index'])->middleware('role:guru')->name('api.dashboard.guru');
    Route::get('/dashboard/siswa', [DashboardApiController::class, 'index'])->middleware('role:siswa')->name('api.dashboard.siswa');
    Route::get('/dashboard/orang-tua', [DashboardApiController::class, 'index'])->middleware('role:orang_tua')->name('api.dashboard.ortu');

    // Master Data & Impor Siswa (WAKA)
    Route::get('/master-data/students/import-template', [MasterDataApiController::class, 'downloadImportTemplate'])
        ->middleware('role:waka')
        ->name('api.master-data.students.import-template');
    Route::post('/master-data/students/import', [MasterDataApiController::class, 'importStudents'])
        ->middleware('role:waka')
        ->name('api.master-data.students.import');
    Route::post('/master-data/majors', [MasterDataApiController::class, 'storeMajor'])->middleware('role:waka')->name('api.master-data.majors.store');
    Route::put('/master-data/majors/{major}', [MasterDataApiController::class, 'updateMajor'])->middleware('role:waka')->name('api.master-data.majors.update');
    Route::delete('/master-data/majors/{major}', [MasterDataApiController::class, 'destroyMajor'])->middleware('role:waka')->name('api.master-data.majors.destroy');
    Route::post('/master-data/classes', [MasterDataApiController::class, 'storeClass'])->middleware('role:waka')->name('api.master-data.classes.store');
    Route::put('/master-data/classes/{class}', [MasterDataApiController::class, 'updateClass'])->middleware('role:waka')->name('api.master-data.classes.update');
    Route::delete('/master-data/classes/{class}', [MasterDataApiController::class, 'destroyClass'])->middleware('role:waka')->name('api.master-data.classes.destroy');
    Route::post('/master-data/subjects', [MasterDataApiController::class, 'storeSubject'])->middleware('role:waka')->name('api.master-data.subjects.store');
    Route::put('/master-data/subjects/{subject}', [MasterDataApiController::class, 'updateSubject'])->middleware('role:waka')->name('api.master-data.subjects.update');
    Route::delete('/master-data/subjects/{subject}', [MasterDataApiController::class, 'destroySubject'])->middleware('role:waka')->name('api.master-data.subjects.destroy');
    Route::post('/master-data/rooms', [MasterDataApiController::class, 'storeRoom'])->middleware('role:waka')->name('api.master-data.rooms.store');
    Route::put('/master-data/rooms/{room}', [MasterDataApiController::class, 'updateRoom'])->middleware('role:waka')->name('api.master-data.rooms.update');
    Route::delete('/master-data/rooms/{room}', [MasterDataApiController::class, 'destroyRoom'])->middleware('role:waka')->name('api.master-data.rooms.destroy');
    Route::put('/master-data/students/{student}/assignment', [MasterDataApiController::class, 'updateStudentAssignment'])->middleware('role:waka')->name('api.master-data.students.assignment');
    Route::put('/master-data/classes/{class}/homeroom-teacher', [MasterDataApiController::class, 'updateHomeroomTeacher'])->middleware('role:waka')->name('api.master-data.classes.homeroom-teacher');

    // Manajemen Akun Pengguna (WAKA)
    Route::get('/accounts', [UserManagementApiController::class, 'index'])->middleware('role:waka')->name('api.accounts.index');
    Route::post('/accounts', [UserManagementApiController::class, 'store'])->middleware('role:waka')->name('api.accounts.store');
    Route::put('/accounts/{user}', [UserManagementApiController::class, 'update'])->middleware('role:waka')->name('api.accounts.update');

    // Jadwal Mengajar & Penguncian Jadwal (Guru & WAKA)
    Route::post('/schedules', [ScheduleApiController::class, 'store'])->middleware('role:guru')->name('api.schedules.store');
    Route::put('/schedules/active-round', [ScheduleApiController::class, 'updateActiveRound'])->middleware('role:waka')->name('api.schedules.update-active-round');
    Route::post('/schedules/toggle-lock', [ScheduleApiController::class, 'toggleLock'])->middleware('role:waka')->name('api.schedules.toggle-lock');

    // Presensi per Jam Pelajaran (Guru)
    Route::get('/attendances/schedules/{schedule}', [AttendanceApiController::class, 'showSchedule'])->middleware('role:guru')->name('api.attendances.schedule');
    Route::get('/attendances/create/{schedule}', [AttendanceApiController::class, 'showSchedule'])->middleware('role:guru')->name('api.attendances.create');
    Route::post('/attendances/schedules/{schedule}', [AttendanceApiController::class, 'store'])->middleware('role:guru')->name('api.attendances.store');
    Route::post('/attendances/store/{schedule}', [AttendanceApiController::class, 'store'])->middleware('role:guru')->name('api.attendances.store.legacy');

    // Pengajuan Izin & Persetujuan Wali Kelas
    Route::get('/leaves/create', [LeaveRequestApiController::class, 'createMeta'])->middleware('role:siswa,orang_tua')->name('api.leaves.create');
    Route::post('/leaves/{leaveRequest}/status', [LeaveRequestApiController::class, 'updateStatus'])->middleware('role:wali_kelas')->name('api.leaves.status');
    Route::put('/leaves/{leaveRequest}/status', [LeaveRequestApiController::class, 'updateStatus'])->middleware('role:wali_kelas')->name('api.leaves.status.put');
    Route::get('/leaves/{leaveRequest}/attachment', [LeaveRequestApiController::class, 'downloadAttachment'])
        ->middleware('role:waka,wali_kelas,siswa,orang_tua')
        ->name('api.leaves.attachment');

    // Jurnal Mengajar Guru & Pengingat WAKA
    Route::post('/journals', [TeachingJournalApiController::class, 'store'])->middleware('role:guru')->name('api.journals.store');
    Route::put('/journals/{journal}', [TeachingJournalApiController::class, 'update'])->middleware('role:guru')->name('api.journals.update');
    Route::post('/journals/reminders', [TeachingJournalApiController::class, 'sendMissingJournalReminders'])->middleware('role:waka')->name('api.journals.reminders');
    Route::get('/journals/{journal}/attachment', [TeachingJournalApiController::class, 'downloadAttachment'])
        ->middleware('role:guru,waka')
        ->name('api.journals.attachment');

    // Laporan Rekapitulasi Presensi (WAKA, BK, Wali Kelas)
    Route::get('/reports/attendance', [ReportApiController::class, 'attendance'])->middleware('role:waka,bk,wali_kelas')->name('api.reports.attendance');
    Route::get('/reports/attendance/export', [ReportApiController::class, 'exportAttendance'])->middleware('role:waka,bk,wali_kelas')->name('api.reports.attendance.export');

    // Notifikasi Pengguna
    Route::get('/notifications', [NotificationApiController::class, 'index'])->name('api.notifications.index');
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllRead'])->name('api.notifications.read-all');

    // Audit Log Sistem (WAKA)
    Route::get('/audit-logs', [AuditLogApiController::class, 'index'])->middleware('role:waka')->name('api.audit-logs.index');
});