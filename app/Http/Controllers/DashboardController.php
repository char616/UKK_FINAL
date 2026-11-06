<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Cycle;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function waka(): View
    {
        $user = Auth::user();
        $activeRound = Cycle::current()->active_round;
        $todayDate = now()->toDateString();

        $stats = [
            'total_students' => User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))->count(),
            'total_teachers' => User::whereHas('role', fn ($q) => $q->where('name', 'guru'))->count(),
            'total_homeroom' => User::whereHas('role', fn ($q) => $q->where('name', 'wali_kelas'))->count(),
            'total_bk' => User::whereHas('role', fn ($q) => $q->where('name', 'bk'))->count(),
            'total_classes' => Classes::count(),
            'today_journals' => TeachingJournal::whereDate('date', $todayDate)->count(),
        ];

        return view('dashboard.waka', compact('user', 'stats'));
    }

    public function guru(): View
    {
        $user = Auth::user();
        $activeRound = Cycle::current()->active_round;
        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];

        $todayDate = now()->toDateString();
        $todayDay = match (now()->dayOfWeek) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Minggu',
        };

        $schedules = Schedule::with(['class.major', 'subject', 'room', 'jamPelajaran'])
            ->where('teacher_id', $user->id)
            ->where('round', $activeRound)
            ->get()
            ->sortBy([
                fn ($a, $b) => ($dayOrder[$a->day] ?? 99) <=> ($dayOrder[$b->day] ?? 99),
                fn ($a, $b) => strcmp((string) $a->start_time, (string) $b->start_time),
            ])
            ->values();

        $todaySchedules = $schedules->where('day', $todayDay)->values();
        $todayJournalMap = TeachingJournal::where('teacher_id', $user->id)
            ->whereDate('date', $todayDate)
            ->pluck('materi', 'schedule_id');

        // Otomatis cek pengingat KBM 30 menit sebelum pelajaran selesai
        app(TeachingJournalController::class)->triggerScheduledTeachingReminders($user);

        return view('dashboard.guru', compact('user', 'schedules', 'todaySchedules', 'todayJournalMap', 'todayDate', 'todayDay'));
    }

    public function siswa(): View
    {
        $user = Auth::user();

        $stats = [
            'hadir' => Attendance::where('student_id', $user->id)->where('status', 'Hadir')->count(),
            'izin' => Attendance::where('student_id', $user->id)->where('status', 'Izin')->count(),
            'sakit' => Attendance::where('student_id', $user->id)->where('status', 'Sakit')->count(),
            'alpa' => Attendance::where('student_id', $user->id)->where('status', 'Alpa')->count(),
        ];
        $total = $stats['hadir'] + $stats['izin'] + $stats['sakit'] + $stats['alpa'];
        $stats['total'] = $total;
        $stats['persentase'] = $total > 0 ? round(($stats['hadir'] / $total) * 100) : 100;

        $recentLeaves = LeaveRequest::where('student_id', $user->id)
            ->with(['processedBy', 'jamMulai', 'jamSelesai'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.siswa', compact('user', 'stats', 'recentLeaves'));
    }

    public function ortu(): View
    {
        $user = Auth::user();

        // Tangani jika akun siswa sedang login mode orang tua atau akun relasi ortu murni
        $students = $user->role->name === 'orang_tua'
            ? $user->students()->with('schoolClass')->get()
            : collect([$user->load('schoolClass')]);

        $studentsData = $students->map(function ($student) {
            $hadir = Attendance::where('student_id', $student->id)->where('status', 'Hadir')->count();
            $izin = Attendance::where('student_id', $student->id)->where('status', 'Izin')->count();
            $sakit = Attendance::where('student_id', $student->id)->where('status', 'Sakit')->count();
            $alpa = Attendance::where('student_id', $student->id)->where('status', 'Alpa')->count();
            $total = $hadir + $izin + $sakit + $alpa;

            $recentLeaves = LeaveRequest::where('student_id', $student->id)
                ->with(['processedBy', 'jamMulai', 'jamSelesai'])
                ->latest()
                ->take(5)
                ->get();

            return [
                'student' => $student,
                'stats' => [
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                    'total' => $total,
                    'persentase' => $total > 0 ? round(($hadir / $total) * 100) : 100,
                ],
                'recentLeaves' => $recentLeaves,
            ];
        });

        return view('dashboard.ortu', compact('user', 'studentsData'));
    }

    public function wali(): View
    {
        $user = Auth::user();
        $homeroomClasses = $user->homeroomClasses()->with('major')->get();
        $homeroomClassIds = $homeroomClasses->pluck('id')->toArray();

        // Agregat kelas
        $classStats = [
            'total_students' => User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))
                ->whereIn('class_id', $homeroomClassIds)->count(),
            'hadir' => Attendance::whereHas('student', fn ($q) => $q->whereIn('class_id', $homeroomClassIds))
                ->where('status', 'Hadir')->count(),
            'izin' => Attendance::whereHas('student', fn ($q) => $q->whereIn('class_id', $homeroomClassIds))
                ->where('status', 'Izin')->count(),
            'sakit' => Attendance::whereHas('student', fn ($q) => $q->whereIn('class_id', $homeroomClassIds))
                ->where('status', 'Sakit')->count(),
            'alpa' => Attendance::whereHas('student', fn ($q) => $q->whereIn('class_id', $homeroomClassIds))
                ->where('status', 'Alpa')->count(),
        ];

        // Breakdown kehadiran per anak di kelas binaan
        $students = User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))
            ->whereIn('class_id', $homeroomClassIds)
            ->with('schoolClass')
            ->withCount([
                'attendances as hadir_count' => fn ($q) => $q->where('status', 'Hadir'),
                'attendances as izin_count' => fn ($q) => $q->where('status', 'Izin'),
                'attendances as sakit_count' => fn ($q) => $q->where('status', 'Sakit'),
                'attendances as alpa_count' => fn ($q) => $q->where('status', 'Alpa'),
            ])
            ->orderBy('name')
            ->get();

        // Izin terbaru siswa kelas binaan
        $recentLeaves = LeaveRequest::with(['student.schoolClass', 'processedBy', 'jamMulai', 'jamSelesai'])
            ->whereHas('student', fn ($q) => $q->whereIn('class_id', $homeroomClassIds))
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.wali', compact('user', 'homeroomClasses', 'classStats', 'students', 'recentLeaves'));
    }

    public function bk(Request $request): View
    {
        $user = Auth::user();
        $classes = Classes::orderBy('name')->get();
        $selectedClassId = $request->query('class_id');
        $search = trim((string) $request->query('search', ''));

        // Agregat sekolah
        $overallStats = [
            'total_students' => User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))->count(),
            'hadir' => Attendance::where('status', 'Hadir')->count(),
            'izin' => Attendance::where('status', 'Izin')->count(),
            'sakit' => Attendance::where('status', 'Sakit')->count(),
            'alpa' => Attendance::where('status', 'Alpa')->count(),
            'today_alpa' => Attendance::whereDate('date', now()->toDateString())->where('status', 'Alpa')->count(),
        ];

        // Breakdown kehadiran per anak untuk pemantauan BK
        $studentQuery = User::whereHas('role', fn ($q) => $q->where('name', 'siswa'))
            ->with('schoolClass')
            ->withCount([
                'attendances as hadir_count' => fn ($q) => $q->where('status', 'Hadir'),
                'attendances as izin_count' => fn ($q) => $q->where('status', 'Izin'),
                'attendances as sakit_count' => fn ($q) => $q->where('status', 'Sakit'),
                'attendances as alpa_count' => fn ($q) => $q->where('status', 'Alpa'),
            ]);

        if ($selectedClassId) {
            $studentQuery->where('class_id', $selectedClassId);
        }

        if ($search !== '') {
            $studentQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('login_key', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $studentQuery->orderBy('name')->paginate(25)->withQueryString();

        // Izin terbaru
        $recentLeaves = LeaveRequest::with(['student.schoolClass', 'processedBy', 'jamMulai', 'jamSelesai'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.bk', compact('user', 'classes', 'selectedClassId', 'search', 'overallStats', 'students', 'recentLeaves'));
    }
}

