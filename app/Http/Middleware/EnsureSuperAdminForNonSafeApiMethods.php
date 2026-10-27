<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminForNonSafeApiMethods
{
    /**
     * Handle an incoming request.
     * Enforces strictly read-only (GET/HEAD) access across all data endpoints in the API system.
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

        // 3. Mutating actions (POST, PUT, PATCH, DELETE) are strictly disallowed across the entire API system
        return response()->json([
            'message' => 'ระบบบริการ API ของสถาบันวิจัยฯ ให้บริการสำหรับเรียกดูข้อมูล (GET) เท่านั้น ไม่อนุญาตให้ส่งคำขอประเภท POST, PUT หรือ DELETE',
            'error' => 'MethodNotAllowed',
            'allowed_methods' => ['GET'],
        ], 405);
    }
}
