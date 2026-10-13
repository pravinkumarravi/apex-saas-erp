<?php

namespace Modules\RestApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\RestApi\Models\ApiKey;
use Symfony\Component\HttpFoundation\Response;

class RequireApiKeyMiddleware
{
    public function handle(Request $request, Closure $next, ?string $requiredAbility = null): Response
    {
        $plainKey = $request->header('X-API-KEY');

        if (!$plainKey) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer apk_')) {
                $plainKey = substr($authHeader, 7);
            }
        }

        if (!$plainKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing X-API-KEY or Bearer API token.',
            ], 401);
        }

        $apiKey = ApiKey::findByPlainKey($plainKey);

        if (!$apiKey || !$apiKey->isValid()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Invalid, revoked, or expired API Key.',
            ], 403);
        }

        if ($requiredAbility && !$apiKey->hasAbility($requiredAbility)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => "API Key lacks required ability: {$requiredAbility}",
            ], 403);
        }

        $apiKey->update(['last_used_at' => now()]);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
