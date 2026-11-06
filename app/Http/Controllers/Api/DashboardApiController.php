<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cycle;
use App\Models\LeaveRequest;
use App\Models\Major;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleLock;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'role',
            'schoolClass.major',
            'schoolClass.homeroomTeacher',
            'homeroomClasses.major',
            'students.schoolClass.major',
            'parents',
        ]);

        $role = $user->role?->name;
        $loginAs = $user->last_login_as ?: $role;
        $activeRound = Cycle::current()->active_round;
        $scheduleLock = ScheduleLock::firstOrCreate(['id' => 1], ['is_locked' => false]);
        $today = now()->toDateString();

        $unreadNotificationsCount = $role !== 'waka'
            ? $user->notifications()->where('is_read', false)->count()
            : 0;

        $summary = match (true) {
            $role === 'waka' => [
                'majors_count' => Major::count(),
                'classes_count' => Classes::count(),
                'subjects_count' => Subject::count(),
                'rooms_count' => Room::count(),
                'active_users_count' => User::where('is_active', true)->count(),
                'active_round_schedules_count' => Schedule::where('round', $activeRound)->count(),
                'today_journals_count' => TeachingJournal::whereDate('date', $today)->count(),
                'recent_audit_logs_count' => AuditLog::whereDate('created_at', $today)->count(),
            ],
            $role === 'guru' => [
                'my_schedules_count' => Schedule::where('teacher_id', $user->id)->where('round', $activeRound)->count(),
                'my_journals_count' => TeachingJournal::where('teacher_id', $user->id)->count(),
                'today_journals_count' => TeachingJournal::where('teacher_id', $user->id)->whereDate('date', $today)->count(),
                'recorded_attendances_today' => Attendance::where('recorded_by', $user->id)->whereDate('date', $today)->count(),
            ],
            $role === 'wali_kelas' => [
                'homeroom_classes' => $user->homeroomClasses->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phase' => $c->phase,
                    'major' => $c->major?->name,
                ])->values(),
                'students_count' => User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))
                    ->whereIn('class_id', $user->homeroomClasses->pluck('id'))
                    ->count(),
                'pending_leaves_count' => LeaveRequest::where('status', 'Pending')
                    ->whereHas('student', fn ($q) => $q->whereIn('class_id', $user->homeroomClasses->pluck('id')))
                    ->count(),
                'approved_leaves_count' => LeaveRequest::where('status', 'Approved')
                    ->whereHas('student', fn ($q) => $q->whereIn('class_id', $user->homeroomClasses->pluck('id')))
                    ->count(),
            ],
            $role === 'bk' => [
                'today_attendances_count' => Attendance::whereDate('date', $today)->count(),
                'today_alpa_count' => Attendance::whereDate('date', $today)->where('status', 'Alpa')->count(),
                'today_sakit_count' => Attendance::whereDate('date', $today)->where('status', 'Sakit')->count(),
                'today_izin_count' => Attendance::whereDate('date', $today)->where('status', 'Izin')->count(),
                'total_classes_count' => Classes::count(),
            ],
            $role === 'siswa' && $loginAs === 'orang_tua' => [
                'login_mode' => 'orang_tua',
                'parent_name' => $user->parent_name,
                'student_name' => $user->name,
                'student_nis' => $user->nis ?: $user->login_key,
                'class_name' => $user->schoolClass?->name,
                'class_schedules_count' => Schedule::where('class_id', $user->class_id)->where('round', $activeRound)->count(),
                'children_attendance_summary' => [
                    'Hadir' => Attendance::where('student_id', $user->id)->where('status', 'Hadir')->count(),
                    'Izin' => Attendance::where('student_id', $user->id)->where('status', 'Izin')->count(),
                    'Sakit' => Attendance::where('student_id', $user->id)->where('status', 'Sakit')->count(),
                    'Alpa' => Attendance::where('student_id', $user->id)->where('status', 'Alpa')->count(),
                ],
                'children_leaves_count' => LeaveRequest::where('student_id', $user->id)->count(),
                'pending_leaves_count' => LeaveRequest::where('student_id', $user->id)->where('status', 'Pending')->count(),
            ],
            $role === 'siswa' => [
                'login_mode' => 'siswa',
                'student_name' => $user->name,
                'student_nis' => $user->nis ?: $user->login_key,
                'parent_name' => $user->parent_name,
                'class_name' => $user->schoolClass?->name,
                'class_schedules_count' => Schedule::where('class_id', $user->class_id)->where('round', $activeRound)->count(),
                'attendance_summary' => [
                    'Hadir' => Attendance::where('student_id', $user->id)->where('status', 'Hadir')->count(),
                    'Izin' => Attendance::where('student_id', $user->id)->where('status', 'Izin')->count(),
                    'Sakit' => Attendance::where('student_id', $user->id)->where('status', 'Sakit')->count(),
                    'Alpa' => Attendance::where('student_id', $user->id)->where('status', 'Alpa')->count(),
                ],
                'my_leaves_count' => LeaveRequest::where('student_id', $user->id)->count(),
                'pending_leaves_count' => LeaveRequest::where('student_id', $user->id)->where('status', 'Pending')->count(),
            ],
            default => [],
        };

        return $this->successResponse([
            'user' => (new UserResource($user))->resolve($request),
            'role' => $role,
            'login_as' => $loginAs,
            'academic_year' => '2026/2027',
            'active_round' => (int) $activeRound,
            'schedule_locked' => (bool) $scheduleLock->is_locked,
            'unread_notifications_count' => $unreadNotificationsCount,
            'summary' => $summary,
        ], 'Data dashboard berhasil diambil.');
    }
}