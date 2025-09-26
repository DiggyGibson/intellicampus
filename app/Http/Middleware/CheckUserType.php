<?php
# app/Http/Middleware/CheckUserType.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\UserActivityLog;

class CheckUserType
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Check if user type is in allowed types
        if (!in_array($user->user_type, $types)) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized access attempt by user type', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_type' => $user->user_type,
                'required_types' => $types,
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            // Log to user activity if model exists
            if (class_exists(UserActivityLog::class)) {
                UserActivityLog::create([
                    'user_id' => $user->id,
                    'activity' => 'unauthorized_access_attempt',
                    'description' => "Attempted to access route requiring user types: " . implode(', ', $types),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'route' => $request->route()?->getName(),
                ]);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied',
                    'error' => 'Your user type (' . $user->user_type . ') is not authorized for this resource',
                    'required_types' => $types,
                ], 403);
            }
            
            // Redirect based on user type
            $redirectRoute = $this->getRedirectRoute($user->user_type);
            
            return redirect()->route($redirectRoute)
                ->with('error', 'You do not have permission to access that area.');
        }

        // Check user status
        if (in_array($user->status, ['suspended', 'inactive', 'banned'])) {
            auth()->logout();
            
            $message = match($user->status) {
                'suspended' => 'Your account has been suspended. Please contact administration.',
                'inactive' => 'Your account is inactive. Please contact support.',
                'banned' => 'Your account has been banned.',
                default => 'Your account is not active.',
            };
            
            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }

    /**
     * Get redirect route based on user type
     */
    private function getRedirectRoute(string $userType): string
    {
        return match($userType) {
            'applicant' => 'admissions.portal.dashboard',
            'student' => 'student.dashboard',
            'faculty' => 'faculty.dashboard',
            'staff' => 'staff.dashboard',
            'admin' => 'admin.dashboard',
            'parent' => 'parent.dashboard',
            'alumni' => 'alumni.dashboard',
            default => 'dashboard',
        };
    }
}