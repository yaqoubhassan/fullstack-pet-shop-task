<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use App\Services\JwtService;
use App\Models\User;

class JwtAuthMiddleware
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token || !$this->jwtService->validateToken($token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userUuid = $this->jwtService->getUserUuidFromToken($token);
        $user = User::where('uuid', $userUuid)->first();

        auth()->setUser($user);
        $request->attributes->set('user', $user);

        return $next($request);
    }
}
