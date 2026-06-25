<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DecryptRequest
{
    /**
     * Handle an incoming request.
     * Decrypt the payload in production environment.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('billapp.encryption_enabled') || !$request->has('encrypted')) {
            return $next($request);
        }

        try {
            // Double base64_decode & urldecode based on PRD
            $decryptedContent = json_decode(urldecode(base64_decode(base64_decode($request->encrypted))), true, 512, JSON_THROW_ON_ERROR);
            
            // Replace request input with decrypted data
            if (is_array($decryptedContent)) {
                $request->replace($decryptedContent);
            }
            
        } catch (\Throwable $e) {
            return encryptResponse(400, 'error', 'Invalid encrypted payload');
        }

        return $next($request);
    }
}
