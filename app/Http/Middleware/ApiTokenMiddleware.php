<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-N8N-API-KEY');
        $validToken = config('services.n8n.api_key');

        if (empty($validToken) || $token !== $validToken) {
            return response()->json([
                'message' => 'Unauthorized. Token tidak valid atau tidak disertakan.',
            ], 401);
        }

        return $next($request);
    }
}
