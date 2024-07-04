<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;
use App\Services\JwtService;
use App\Models\User;

class AdminMiddleware
{
     protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token || !$this->jwtService->validateToken($token)) {
            return response()->json([
                'success' => 0,
                'data' => [],
                'error' => 'Unauthenticated',
                'errors' => [],
                'trace' => []
            ], 401);
        }

        $userUuid = $this->jwtService->getUserUuidFromToken($token);
        $user = User::where('uuid', $userUuid)->first();

        if (!$user || !$user->is_admin) { // Ensure the user has the admin role
            return response()->json([
                'success' => 0,
                'data' => [],
                'error' => 'Unauthorized: Not enough privileges',
                'errors' => [],
                'trace' => []
            ], 422);
        }

        auth()->setUser($user);
        $request->attributes->set('user', $user);

        return $next($request);
    }
}
