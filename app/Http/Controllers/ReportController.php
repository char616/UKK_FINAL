<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $attendanceQuery = $this->attendanceQuery($filters);
        $attendanceSummary = (clone $attendanceQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $attendances = $attendanceQuery
            ->with(['student', 'schedule.class', 'schedule.subject', 'schedule.teacher'])
            ->orderByDesc('date')
            ->orderBy('schedule_id')
            ->orderBy('student_id')
            ->paginate(25)
            ->withQueryString();

        return view('reports.attendance', [
            'user' => Auth::user(),
            'classes' => $this->availableClasses(),
            'filters' => $filters,
            'attendanceSummary' => $attendanceSummary,
            'attendances' => $attendances,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $fileName = 'rekap-presensi-' . $filters['date_from'] . '-sampai-' . $filters['date_to'] . '.csv';

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuka stream ekspor laporan.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal', 'Siswa', 'Kelas', 'Mata Pelajaran', 'Guru', 'Status', 'Catatan'], ',', '"', '');

            $this->attendanceQuery($filters)
                ->with(['student', 'schedule.class', 'schedule.subject', 'schedule.teacher'])
                ->orderBy('date')
                ->orderBy('schedule_id')
                ->orderBy('student_id')
                ->chunkById(500, function ($attendances) use ($output): void {
                    foreach ($attendances as $attendance) {
                        fputcsv($output, [
                            $attendance->date,
                            $this->safeCsvValue($attendance->student?->name),
                            $this->safeCsvValue($attendance->schedule?->class?->name),
                            $this->safeCsvValue($attendance->schedule?->subject?->name),
                            $this->safeCsvValue($attendance->schedule?->teacher?->name),
                            $this->safeCsvValue($attendance->status),
                            $this->safeCsvValue($attendance->notes),
                        ], ',', '"', '');
                    }
                });

            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validatedFilters(Request $request): array
    {
        $user = Auth::user();
        abort_unless(in_array($user->role?->name, ['waka', 'bk', 'wali_kelas'], true), 403);

        $allowedClassIds = $user->role->name === 'wali_kelas'
            ? $user->homeroomClasses()->pluck('classes.id')->all()
            : null;

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'class_id' => [
                'nullable',
                'integer',
                'exists:classes,id',
                ...($allowedClassIds === null ? [] : [Rule::in($allowedClassIds)]),
            ],
        ]);

        return [
            'date_from' => $validated['date_from'] ?? now()->startOfMonth()->toDateString(),
            'date_to' => $validated['date_to'] ?? now()->toDateString(),
            'class_id' => $validated['class_id'] ?? '',
        ];
    }

    private function attendanceQuery(array $filters): Builder
    {
        $user = Auth::user();

        return Attendance::query()
            ->whereBetween('date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['class_id'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('schedule', fn (Builder $scheduleQuery) => $scheduleQuery
                    ->where('class_id', $filters['class_id']));
            })
            ->when($user->role->name === 'wali_kelas', function (Builder $query) use ($user): void {
                $classIds = $user->homeroomClasses()->select('classes.id');
                $query->whereHas('schedule', fn (Builder $scheduleQuery) => $scheduleQuery
                    ->whereIn('class_id', $classIds));
            });
    }

    private function availableClasses()
    {
        $user = Auth::user();
        $classes = Classes::query()->orderBy('name');

        if ($user->role->name === 'wali_kelas') {
            $classes->whereIn('id', $user->homeroomClasses()->select('classes.id'));
        }

        return $classes->get(['id', 'name']);
    }

    private function safeCsvValue(?string $value): string
    {
        if ($value !== null && preg_match('/^[\x00-\x20]*[=+\-@]/', $value) === 1) {
            return "'" . $value;
        }

        return $value ?? '';
    }
}
