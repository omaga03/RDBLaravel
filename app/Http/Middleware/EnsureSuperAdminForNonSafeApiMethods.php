<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminForNonSafeApiMethods
{
    /**
     * Handle an incoming request.
     * Enforces read-only (GET/HEAD) access for all API users except Super Admin (The Creator / ID 129).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Safe HTTP methods (GET, HEAD, OPTIONS) are permitted for all authenticated token holders
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // 2. Allow token self-revocation / logout
        if ($request->is('api/v1/auth/logout')) {
            return $next($request);
        }

        $user = $request->user();

        // 3. Any mutating actions (POST, PUT, PATCH, DELETE) require Super Admin status
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json([
                'message' => 'เฉพาะผู้ดูแลระบบระดับสูงสุด (The Creator) เท่านั้นที่มีสิทธิ์ส่งคำขอเพิ่มหรือแก้ไขข้อมูลผ่าน API (ผู้ใช้อื่นได้รับสิทธิ์เฉพาะคำขอแบบ GET เท่านั้น)',
                'error' => 'MethodNotAllowedForRole',
                'allowed_methods' => ['GET'],
            ], 403);
        }

        return $next($request);
    }
}
