<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Role aliases for backward compatibility
     */
    protected $roleAliases = [
        'admin' => ['administrator', 'admin', 'system-admin'],
        'super-admin' => ['super-administrator', 'super-admin', 'superadmin', 'Super Administrator'],
        'student' => ['student', 'enrolled-student'],
        'faculty' => ['faculty', 'instructor', 'teacher', 'professor'],
        'registrar' => ['registrar', 'registrar-office'],
        'advisor' => ['advisor', 'academic-advisor', 'student-advisor'],
        'department-head' => ['department-head', 'department-chair', 'dept-head'],
        'dean' => ['dean', 'school-dean', 'college-dean'],
        'applicant' => ['applicant', 'admission-applicant'],
    ];

    /**
     * Restricted routes for applicants
     */
    protected $applicantRestrictedRoutes = [
        'student.*',
        'faculty.*',
        'admin.*',
        'registrar.*',
        'department.*',
        'grades.*',
        'transcripts.*',
        'financial.*',
        'users.*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user account is active
        if (!$this->isUserActive($user)) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact support.');
        }
        
        // Special handling for applicants
        if ($this->isApplicant($user)) {
            if ($this->isRestrictedForApplicant($request)) {
                Log::warning('Applicant attempted to access restricted area', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'route' => $request->route()->getName(),
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('admissions.portal.index')
                    ->with('warning', 'Please complete your admission process to access student portal.');
            }
        }
        
        // Super Administrator bypass
        if ($this->isSuperAdmin($user)) {
            return $next($request);
        }
        
        // If no specific roles required, just check authentication
        if (empty($roles)) {
            return $next($request);
        }
        
        // Check if user has any of the required roles
        if ($this->userHasRequiredRole($user, $roles)) {
            return $next($request);
        }
        
        // User doesn't have the required role
        Log::warning('Unauthorized access attempt', [
            'user_id' => $user->id,
            'required_roles' => $roles,
            'user_roles' => $this->getUserRoles($user),
            'route' => $request->route()->getName(),
            'ip' => $request->ip()
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized access',
                'required_roles' => $roles
            ], 403);
        }
        
        abort(403, 'Unauthorized access. Required role(s): ' . implode(' or ', $roles));
    }
    
    /**
     * Check if user is active
     */
    protected function isUserActive($user): bool
    {
        // Use the isActive method from your User model
        if (method_exists($user, 'isActive')) {
            return $user->isActive();
        }
        
        // Fallback to checking status field
        if (isset($user->status)) {
            return $user->status === 'active';
        }
        
        return true;
    }
    
    /**
     * Check if user is an applicant
     */
    protected function isApplicant($user): bool
    {
        // Check user_type first
        if (isset($user->user_type) && $user->user_type === 'applicant') {
            return true;
        }
        
        // Check if user has applicant role
        if ($user->hasRole(['applicant', 'admission-applicant'])) {
            // Make sure they don't also have student role
            return !$user->hasRole('student');
        }
        
        return false;
    }
    
    /**
     * Check if route is restricted for applicants
     */
    protected function isRestrictedForApplicant(Request $request): bool
    {
        $routeName = $request->route()->getName();
        
        if (!$routeName) {
            return false;
        }
        
        foreach ($this->applicantRestrictedRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user is super admin
     */
    protected function isSuperAdmin($user): bool
    {
        // Use the isSuperAdmin method from your User model
        return $user->isSuperAdmin();
    }
    
    /**
     * Check if user has any of the required roles
     */
    protected function userHasRequiredRole($user, array $roles): bool
    {
        foreach ($roles as $role) {
            // Handle multiple roles separated by |
            $roleOptions = explode('|', $role);
            
            foreach ($roleOptions as $roleOption) {
                $roleOption = trim($roleOption);
                
                // Use the hasRole method from your User model
                if ($user->hasRole($roleOption)) {
                    return true;
                }
                
                // Check role aliases
                if ($this->checkRoleWithAliases($user, $roleOption)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check role with aliases
     */
    protected function checkRoleWithAliases($user, string $role): bool
    {
        // Check if role has aliases
        if (isset($this->roleAliases[$role])) {
            foreach ($this->roleAliases[$role] as $alias) {
                if ($user->hasRole($alias)) {
                    return true;
                }
            }
        }
        
        // Check if role is an alias of another role
        foreach ($this->roleAliases as $mainRole => $aliases) {
            if (in_array($role, $aliases) && $user->hasRole($mainRole)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user roles for logging
     */
    protected function getUserRoles($user): array
    {
        try {
            // Get role names from the relationship
            $roles = $user->roles()->pluck('name')->toArray();
            
            // Also add user_type if it exists
            if (isset($user->user_type)) {
                $roles[] = 'user_type:' . $user->user_type;
            }
            
            return $roles;
        } catch (\Exception $e) {
            return ['unknown'];
        }
    }
}