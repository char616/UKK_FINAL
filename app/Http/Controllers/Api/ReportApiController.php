<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ReportController;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Classes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportApiController extends Controller
{
    use ApiResponses;

    public function attendance(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);
        $attendanceQuery = $this->attendanceQuery($request, $filters);

        $summaryRows = (clone $attendanceQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $summary = [
            'Hadir' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Alpa' => 0,
        ];
        foreach ($summaryRows as $row) {
            $summary[$row->status] = (int) $row->total;
        }

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);
        $paginator = $attendanceQuery
            ->with(['student:id,name,login_key,class_id', 'schedule.class:id,name', 'schedule.subject:id,code,name', 'schedule.teacher:id,name'])
            ->orderByDesc('date')
            ->orderBy('schedule_id')
            ->orderBy('student_id')
            ->paginate($perPage);

        return $this->successResponse([
            'filters' => $filters,
            'summary' => $summary,
            'classes' => $this->availableClasses($request),
            'attendances' => AttendanceResource::collection($paginator->getCollection())->resolve($request),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Rekapitulasi laporan presensi berhasil diambil.');
    }

    public function exportAttendance(Request $request): StreamedResponse
    {
        return app(ReportController::class)->export($request);
    }

    private function validatedFilters(Request $request): array
    {
        $user = $request->user();
        $allowedClassIds = $user->role?->name === 'wali_kelas'
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

    private function attendanceQuery(Request $request, array $filters): Builder
    {
        $user = $request->user();

        return Attendance::query()
            ->whereBetween('date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['class_id'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('schedule', fn (Builder $scheduleQuery) => $scheduleQuery
                    ->where('class_id', $filters['class_id']));
            })
            ->when($user->role?->name === 'wali_kelas', function (Builder $query) use ($user): void {
                $classIds = $user->homeroomClasses()->select('classes.id');
                $query->whereHas('schedule', fn (Builder $scheduleQuery) => $scheduleQuery
                    ->whereIn('class_id', $classIds));
            });
    }

    private function availableClasses(Request $request)
    {
        $user = $request->user();
        $classes = Classes::query()->orderBy('name');

        if ($user->role?->name === 'wali_kelas') {
            $classes->whereIn('id', $user->homeroomClasses()->select('classes.id'));
        }

        return $classes->get(['id', 'name']);
    }
}

