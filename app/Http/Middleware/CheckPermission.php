<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * CheckPermission Middleware
 * 
 * Validates user permissions for fine-grained access control.
 * Works in conjunction with CheckRole middleware.
 * 
 * Usage: Route::middleware(['auth', 'permission:users.create,users.edit'])
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions  Comma-separated list of required permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return $this->handleUnauthorized($request, 'Authentication required');
        }

        $user = Auth::user();

        // Super admin bypass - they have all permissions
        if ($this->isSuperAdmin($user)) {
            return $next($request);
        }

        // Parse permissions
        $requiredPermissions = $this->parsePermissions($permissions);
        
        // Check if user has required permissions
        if ($this->userHasPermissions($user, $requiredPermissions, $request)) {
            // Log sensitive permission usage
            $this->logPermissionUsage($user, $requiredPermissions, $request);
            
            return $next($request);
        }

        return $this->handleUnauthorized($request, 'Insufficient permissions');
    }

    /**
     * Parse permission string into array
     */
    private function parsePermissions($permissions): array
    {
        if (is_string($permissions[0]) && strpos($permissions[0], ',') !== false) {
            return array_map('trim', explode(',', $permissions[0]));
        }
        return $permissions;
    }

    /**
     * Check if user has required permissions
     */
    private function userHasPermissions($user, array $permissions, Request $request): bool
    {
        // Check for ANY permission (OR logic)
        $requireAll = $request->get('require_all_permissions', false);
        
        if ($requireAll) {
            // User must have ALL specified permissions
            foreach ($permissions as $permission) {
                if (!$this->userHasPermission($user, $permission)) {
                    return false;
                }
            }
            return true;
        } else {
            // User needs at least ONE of the specified permissions
            foreach ($permissions as $permission) {
                if ($this->userHasPermission($user, $permission)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Check if user has a specific permission
     */
    private function userHasPermission($user, string $permission): bool
    {
        // Cache permission checks for performance
        $cacheKey = "user.{$user->id}.permission.{$permission}";
        
        return Cache::remember($cacheKey, 300, function () use ($user, $permission) {
            // Direct permission check
            if ($user->hasDirectPermission($permission)) {
                return true;
            }
            
            // Permission through roles
            foreach ($user->roles as $role) {
                if ($role->hasPermissionTo($permission)) {
                    return true;
                }
            }
            
            // Check wildcard permissions (e.g., 'users.*' covers 'users.create')
            if ($this->hasWildcardPermission($user, $permission)) {
                return true;
            }
            
            return false;
        });
    }

    /**
     * Check for wildcard permissions
     */
    private function hasWildcardPermission($user, string $permission): bool
    {
        $parts = explode('.', $permission);
        
        // Check for module-level wildcard (e.g., 'users.*')
        if (count($parts) > 1) {
            $moduleWildcard = $parts[0] . '.*';
            
            // Check direct wildcard permission
            if ($user->hasDirectPermission($moduleWildcard)) {
                return true;
            }
            
            // Check wildcard permission through roles
            foreach ($user->roles as $role) {
                if ($role->hasPermissionTo($moduleWildcard)) {
                    return true;
                }
            }
        }
        
        // Check for global wildcard ('*')
        if ($user->hasDirectPermission('*')) {
            return true;
        }
        
        foreach ($user->roles as $role) {
            if ($role->hasPermissionTo('*')) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user is super admin
     */
    private function isSuperAdmin($user): bool
    {
        return $user->hasRole(['super-administrator', 'super-admin']) 
            || $user->email === config('app.super_admin_email');
    }

    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized(Request $request, string $reason = 'Unauthorized'): mixed
    {
        // Log security event
        Log::warning('Permission denied', [
            'user_id' => Auth::id(),
            'email' => Auth::user()?->email,
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'reason' => $reason,
            'requested_permissions' => $request->route()?->middleware()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => $reason,
                'required_permissions' => $this->getSafePermissionList($request)
            ], 403);
        }

        return redirect()->back()
            ->with('error', 'You do not have permission to perform this action.')
            ->with('required_permissions', $this->getSafePermissionList($request));
    }

    /**
     * Get safe list of required permissions (for display)
     */
    private function getSafePermissionList(Request $request): array
    {
        // Don't expose sensitive permission names
        $sensitivePatterns = ['delete', 'destroy', 'admin', 'system'];
        $middleware = $request->route()?->middleware() ?? [];
        $permissions = [];
        
        foreach ($middleware as $mw) {
            if (str_starts_with($mw, 'permission:')) {
                $perms = str_replace('permission:', '', $mw);
                foreach (explode(',', $perms) as $perm) {
                    $isSensitive = false;
                    foreach ($sensitivePatterns as $pattern) {
                        if (str_contains($perm, $pattern)) {
                            $isSensitive = true;
                            break;
                        }
                    }
                    if (!$isSensitive) {
                        $permissions[] = $perm;
                    }
                }
            }
        }
        
        return $permissions;
    }

    /**
     * Log permission usage for audit
     */
    private function logPermissionUsage($user, array $permissions, Request $request): void
    {
        // Only log sensitive permissions
        $sensitivePermissions = [
            'users.delete',
            'system.configure',
            'financial.process',
            'grades.change',
            'transcripts.modify'
        ];
        
        $usedSensitivePermissions = array_intersect($permissions, $sensitivePermissions);
        
        if (!empty($usedSensitivePermissions)) {
            Log::channel('security')->info('Sensitive permission used', [
                'user_id' => $user->id,
                'email' => $user->email,
                'permissions' => $usedSensitivePermissions,
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String()
            ]);
        }
    }

    /**
     * Clear permission cache for a user
     */
    public static function clearPermissionCache($userId): void
    {
        $pattern = "user.{$userId}.permission.*";
        
        // If using Redis
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            $keys = Cache::getRedis()->keys($pattern);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } else {
            // For other cache drivers, we need to track keys manually
            $trackedKeys = Cache::get("permission_keys.user.{$userId}", []);
            foreach ($trackedKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget("permission_keys.user.{$userId}");
        }
    }
}