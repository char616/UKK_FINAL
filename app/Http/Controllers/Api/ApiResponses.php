<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

trait ApiResponses
{
    protected function resolveApiUser(Request $request): ?User
    {
        if ($bearerToken = $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($bearerToken);

            if (
                $accessToken
                && (! $accessToken->expires_at || ! $accessToken->expires_at->isPast())
                && $accessToken->tokenable instanceof User
            ) {
                $accessToken->forceFill(['last_used_at' => now()])->save();

                return $accessToken->tokenable->withAccessToken($accessToken);
            }

            return null;
        }

        return $request->user('sanctum') ?? $request->user() ?? Auth::guard('web')->user();
    }

    protected function successResponse(mixed $data = null, string $message = 'Berhasil.', int $statusCode = 200, array $extra = []): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $data = $data->resolve(request());
        }

        return response()->json(array_merge([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $extra), $statusCode);
    }

    protected function errorResponse(string $message, int $statusCode = 400, mixed $errors = null, mixed $data = null): JsonResponse
    {
        $payload = [
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }
}

