<?php

// app/Http/Middleware/EnforceScopePolicy.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ScopeManagementService;
use Illuminate\Support\Facades\Auth;

class EnforceScopePolicy
{
    protected $scopeService;

    public function __construct(ScopeManagementService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $action
     * @param  string  $entityType
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $action, $entityType)
    {
        $user = Auth::user();
        
        // Try to get entity from route
        $entity = null;
        $entityParam = $request->route($entityType);
        
        if ($entityParam) {
            $modelClass = $this->getModelClass($entityType);
            if ($modelClass) {
                $entity = is_object($entityParam) ? $entityParam : $modelClass::find($entityParam);
            }
        }
        
        if ($entity) {
            $allowed = $this->scopeService->canPerformAction($user, $action, $entityType, $entity);
            
            if (!$allowed) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "You don't have permission to {$action} this {$entityType}"
                    ], 403);
                }
                abort(403, "You don't have permission to {$action} this {$entityType}");
            }
        }
        
        return $next($request);
    }
    
    /**
     * Get model class from entity type
     */
    private function getModelClass($entityType)
    {
        $models = [
            'course' => \App\Models\Course::class,
            'student' => \App\Models\Student::class,
            'department' => \App\Models\Department::class,
            'section' => \App\Models\CourseSection::class,
            'grade' => \App\Models\Grade::class,
        ];
        
        return $models[$entityType] ?? null;
    }
}
