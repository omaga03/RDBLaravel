<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResearcherVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is fully verified or has admin privileges
        if ($user->canAccessFullBackend()) {
            // If already fully verified, redirect away from onboarding
            if ($request->routeIs('backend.onboarding.*')) {
                return redirect()->route('backend.site.index');
            }
            return $next($request);
        }

        // Whitelisted routes for pending/unverified users
        $allowedRoutePrefixes = [
            'backend.onboarding.',
            'backend.user.stop_impersonate',
            'backend.rbac.stop_impersonate',
            'logout',
        ];

        // Always allow stopping impersonation regardless of route name or method
        if ($request->routeIs('backend.user.stop_impersonate') || $request->routeIs('backend.rbac.stop_impersonate')) {
            return $next($request);
        }

        foreach ($allowedRoutePrefixes as $prefix) {
            if (str_starts_with($request->route()?->getName() ?? '', $prefix)) {
                return $next($request);
            }
        }

        // Allow self user profile / password edit
        if ($request->routeIs('backend.user.show') || $request->routeIs('backend.user.edit') || $request->routeIs('backend.user.update')) {
            $targetUserId = $request->route('user') instanceof \App\Models\User 
                ? $request->route('user')->id 
                : (int)$request->route('user');
            if ($targetUserId === $user->id) {
                return $next($request);
            }
        }

        // Otherwise redirect to onboarding index
        return redirect()->route('backend.onboarding.index');
    }
}
