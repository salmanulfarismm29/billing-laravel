<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

if (!function_exists('encryptResponse')) {
    /**
     * Standardized API response format with optional double base64 encryption.
     */
    function encryptResponse(int $code, string $status, string $message, mixed $data = null): JsonResponse
    {
        $responsePayload = [
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];

        if (config('billapp.encryption_enabled')) {
            try {
                $json = json_encode($responsePayload, JSON_THROW_ON_ERROR);
                $encrypted = base64_encode(base64_encode($json));
                
                return response()->json([
                    'payload' => $encrypted,
                ], $code);
            } catch (\Throwable $e) {
                Log::error('Response encryption failed', [
                    'error' => $e->getMessage()
                ]);
                
                // Fallback to error
                return response()->json([
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'Encryption failure',
                    'data' => null
                ], 500);
            }
        }

        return response()->json($responsePayload, $code);
    }
}
