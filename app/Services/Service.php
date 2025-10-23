<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class Service
{

    public function jsonResponse($data, $status = 200): JsonResponse
    {
        return response()->json($data, $status, ['Content-Type' => 'application/json']);
    }

    public function jsonErrorResponse($message, $status = 400): JsonResponse
    {
        return response()->json(['error' => $message], $status, ['Content-Type' => 'application/json']);
    }

}
