<?php

namespace Modules\CRM\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApiClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof ApiClient) {
            return response()->json([
                'message' => 'Access forbidden. Only API Clients are allowed.',
            ], 403);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'API Client is inactive.',
            ], 403);
        }

        // Update last used at
        $user->update(['last_used_at' => now()]);

        return $next($request);
    }
}
