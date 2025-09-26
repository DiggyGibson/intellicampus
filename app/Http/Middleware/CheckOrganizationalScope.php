<?php

// app/Http/Middleware/CheckOrganizationalScope.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckOrganizationalScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$requiredScopes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$requiredScopes)
    {
        $user = Auth::user();
        
        // Admin bypasses all checks
        if ($user->hasRole(['super-admin', 'admin'])) {
            return $next($request);
        }
        
        // Check if user has any of the required organizational scopes
        $userScopes = collect($user->getOrganizationalScope())->pluck('type')->toArray();
        
        $hasRequiredScope = false;
        foreach ($requiredScopes as $scope) {
            if (in_array($scope, $userScopes)) {
                $hasRequiredScope = true;
                break;
            }
        }
        
        if (!$hasRequiredScope) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have the required organizational scope to access this resource',
                    'required_scopes' => $requiredScopes,
                    'user_scopes' => $userScopes
                ], 403);
            }
            abort(403, 'You do not have the required organizational scope to access this resource');
        }
        
        return $next($request);
    }
}