<?php

// ============================================
// MIDDLEWARE FILES
// ============================================

// app/Http/Middleware/ApplyScopeFilter.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ScopeManagementService;
use Illuminate\Support\Facades\Auth;

class ApplyScopeFilter
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
     * @param  string|null  $entityType
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $entityType = null)
    {
        // Store the entity type in the request for later use
        if ($entityType) {
            $request->attributes->set('scope_entity_type', $entityType);
        }

        // Store the scope service instance for use in controllers
        $request->attributes->set('scope_service', $this->scopeService);

        // Store user's scope context
        if (Auth::check()) {
            $user = Auth::user();
            $scopeContext = [
                'user_id' => $user->id,
                'roles' => $user->roles->pluck('slug')->toArray(),
                'department_id' => $user->department_id,
                'college_id' => $user->college_id,
                'school_id' => $user->school_id,
                'is_admin' => $user->hasRole(['super-admin', 'admin']),
            ];
            $request->attributes->set('scope_context', $scopeContext);
        }

        return $next($request);
    }
}