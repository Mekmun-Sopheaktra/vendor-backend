<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait BaseApiResponse
{
    public function success($data, ?string $title = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $alert = ($title !== null || $message !== null) ? ['title' => $title, 'message' => $message] : null;

        return response()->json([
            'data' => $data,
            'status' => true,
            'alert' => $alert,
        ], $code);
    }

    //success response login
    public function successLogin($data, $permission, ?string $title = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $alert = ($title !== null || $message !== null) ? ['title' => $title, 'message' => $message] : null;

        return response()->json([
            'data' => $data,
            'permission' => $permission,
            'status' => true,
            'alert' => $alert,
        ], $code);
    }

    public function failed($data, ?string $title = null, ?string $message = null, int $code = 500): JsonResponse
    {
        $alert = ($title !== null || $message !== null) ? ['title' => $title, 'message' => $message] : null;

        return response()->json([
            'data' => null,
            'status' => false,
            'alert' => $alert,
        ], $code);
    }
}
