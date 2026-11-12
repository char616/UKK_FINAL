<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role?->name === 'waka') {
            return $this->errorResponse('WAKA mengirim pengingat jurnal melalui halaman Jurnal Mengajar.', 403);
        }

        $notificationsQuery = $user->notifications()->visibleToRole($user->role?->name ?? '');
        if ($request->boolean('mark_as_read', true)) {
            $notificationsQuery->where('is_read', false)->update(['is_read' => true]);
        }

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);
        $paginator = $user->notifications()->visibleToRole($user->role?->name ?? '')->paginate($perPage);

        return $this->successResponse([
            'notifications' => NotificationResource::collection($paginator->getCollection())->resolve($request),
            'unread_count' => $user->notifications()->visibleToRole($user->role?->name ?? '')->where('is_read', false)->count(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Daftar notifikasi berhasil diambil.');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role?->name === 'waka') {
            return $this->errorResponse('WAKA mengirim pengingat jurnal melalui halaman Jurnal Mengajar.', 403);
        }

        $updated = $user->notifications()
            ->visibleToRole($user->role?->name ?? '')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->successResponse([
            'marked_read_count' => $updated,
        ], 'Semua notifikasi berhasil ditandai telah dibaca.');
    }
}

