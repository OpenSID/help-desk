<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiry
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token) {
            // Cari token berdasarkan plain_token
            $accessToken = \App\Models\Token::where('plain_token', $token)->first();

            // Jika token ditemukan tetapi kadaluarsa
            if ($accessToken && $accessToken->expires_at && $accessToken->expires_at->isPast()) {
                return response()->json([
                    'message' => 'Token expired'
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        return $next($request);
    }
}
