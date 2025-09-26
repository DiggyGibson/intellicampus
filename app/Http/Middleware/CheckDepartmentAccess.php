<?php

// app/Http/Middleware/CheckDepartmentAccess.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class CheckDepartmentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = 'view')
    {
        $user = Auth::user();
        
        // Get department from route parameters
        $department = null;
        if ($request->route('department')) {
            $department = $request->route('department');
            if (!($department instanceof Department)) {
                $department = Department::find($request->route('department'));
            }
        } elseif ($request->route('department_id')) {
            $department = Department::find($request->route('department_id'));
        } elseif ($request->has('department_id')) {
            $department = Department::find($request->input('department_id'));
        }
        
        if ($department) {
            // Check access based on permission level
            $hasAccess = match($permission) {
                'manage' => $user->canManageDepartment($department),
                'edit' => $user->canManageDepartment($department),
                'view' => $user->canAccessDepartment($department),
                default => false
            };
            
            if (!$hasAccess) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied to this department'
                    ], 403);
                }
                abort(403, 'Access denied to this department');
            }
            
            // Store department in request for later use
            $request->attributes->set('department', $department);
        }
        
        return $next($request);
    }
}