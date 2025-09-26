<?php
// File 1: app/Http/Middleware/RedirectIfAuthenticated.php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * Redirects authenticated users to their role-specific dashboard
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();
                
                // Get the user's primary role
                $primaryRole = $user->roles->first();
                
                if ($primaryRole) {
                    // Get landing page from config
                    $landingPages = config('navigation.landing_pages');
                    $landingPage = $landingPages[$primaryRole->slug] ?? '/dashboard';
                    
                    return redirect($landingPage);
                }
                
                // Fallback based on user_type if no role
                return redirect($this->getDefaultRedirect($user));
            }
        }

        return $next($request);
    }

    /**
     * Get default redirect based on user type
     */
    private function getDefaultRedirect($user)
    {
        switch ($user->user_type) {
            case 'student':
                return '/student/dashboard';
            case 'faculty':
                return '/faculty/dashboard';
            case 'admin':
                return '/admin/dashboard';
            case 'staff':
                return '/staff/dashboard';
            default:
                return '/dashboard';
        }
    }
}