<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Collection;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * NavigationService - FIXED VERSION
 * 
 * Dynamically generates navigation menus based on user permissions,
 * enabled modules, and context. Ensures users only see menu items
 * they have access to.
 */
class NavigationService
{
    protected $user;
    protected $config;
    protected $enabledModules;
    protected $debug = false; // Enable for debugging

    public function __construct()
    {
        $this->user = Auth::user();
        $this->config = config('navigation');
        $this->enabledModules = config('modules.enabled', []);
        $this->debug = config('app.debug', false);
    }

    /**
     * Get the complete navigation menu for the current user
     */
    public function getMenu(string $type = 'sidebar'): Collection
    {
        if (!$this->user) {
            return $this->getPublicMenu($type);
        }

        // Cache menu per user and role combination
        $cacheKey = "navigation.{$type}.user.{$this->user->id}." . md5($this->user->roles->pluck('id')->join(','));
        
        // For debugging, bypass cache
        if ($this->debug) {
            return $this->buildMenu($type);
        }
        
        return Cache::remember($cacheKey, 3600, function () use ($type) {
            return $this->buildMenu($type);
        });
    }

    /**
     * Build the navigation menu
     */
    protected function buildMenu(string $type): Collection
    {
        $menuConfig = $this->config[$type] ?? [];
        $menu = collect();

        if ($this->debug) {
            Log::info('Building menu', [
                'type' => $type,
                'user_id' => $this->user->id,
                'roles' => $this->user->roles->pluck('name')->toArray(),
                'config_sections' => count($menuConfig)
            ]);
        }

        foreach ($menuConfig as $section) {
            $items = $this->processMenuSection($section);
            
            if ($this->debug) {
                Log::info('Processed section', [
                    'title' => $section['title'] ?? 'No title',
                    'items_count' => $items->count()
                ]);
            }
            
            if ($items->isNotEmpty()) {
                $menu->push([
                    'title' => $section['title'] ?? null,
                    'items' => $items->toArray(), // Convert to array for blade template
                    'icon' => $section['icon'] ?? null,
                    'order' => $section['order'] ?? 100
                ]);
            }
        }

        return $menu->sortBy('order')->values();
    }

    /**
     * Process a menu section and filter items by permissions
     */
    protected function processMenuSection(array $section): Collection
    {
        $items = collect($section['items'] ?? []);
        
        $processedItems = $items->filter(function ($item) {
            $canAccess = $this->canAccessMenuItem($item);
            
            if ($this->debug) {
                Log::info('Item access check', [
                    'label' => $item['label'] ?? 'No label',
                    'route' => $item['route'] ?? 'No route',
                    'can_access' => $canAccess
                ]);
            }
            
            return $canAccess;
        })->map(function ($item) {
            return $this->formatMenuItem($item);
        })->values();

        return $processedItems;
    }

    /**
     * Check if user can access a menu item - FIXED VERSION
     */
    protected function canAccessMenuItem(array $item): bool
    {
        // CRITICAL FIX: Check super-admin FIRST and properly
        if ($this->user) {
            $userRoles = $this->user->roles->pluck('slug')->toArray();
            
            // Super administrators and system administrators see everything (that exists)
            if (in_array('super-administrator', $userRoles) || 
                in_array('system-administrator', $userRoles)) {
                
                // For super admins, only check if route exists
                if (isset($item['route'])) {
                    $routeExists = Route::has($item['route']);
                    
                    if ($this->debug && !$routeExists) {
                        Log::warning('Route does not exist for super-admin item', [
                            'route' => $item['route'],
                            'label' => $item['label'] ?? 'No label'
                        ]);
                    }
                    
                    return $routeExists;
                }
                
                // No route specified, allow it (might be a header or separator)
                return true;
            }
        }
        
        // Check if module is enabled (if module is specified)
        if (isset($item['module']) && !$this->isModuleEnabled($item['module'])) {
            return false;
        }

        // Check roles - if roles are specified, user must have at least one
        if (isset($item['roles'])) {
            // Handle wildcard for all authenticated users
            if (in_array('*', $item['roles'])) {
                // User is authenticated (we checked $this->user exists above)
                return true;
            }
            
            if (!$this->userHasAnyRole($item['roles'])) {
                return false;
            }
        }

        // Check permissions if specified
        if (isset($item['permissions'])) {
            if (!$this->userHasAnyPermission($item['permissions'])) {
                return false;
            }
        }

        // Check custom gate if specified
        if (isset($item['gate'])) {
            if (!$this->checkGate($item['gate'])) {
                return false;
            }
        }

        // Check route existence
        if (isset($item['route'])) {
            if (!Route::has($item['route'])) {
                if ($this->debug) {
                    Log::warning('Route does not exist', [
                        'route' => $item['route'],
                        'label' => $item['label'] ?? 'No label'
                    ]);
                }
                return false;
            }
        }

        // Special checks for applicants
        if (isset($item['restrict_applicants']) && $item['restrict_applicants']) {
            if ($this->isApplicant()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Format a menu item for display - FIXED VERSION
     */
    protected function formatMenuItem(array $item): array
    {
        $formatted = [
            'label' => $item['label'] ?? 'Untitled',
            'icon' => $item['icon'] ?? 'fas fa-circle',
            'url' => $this->getItemUrl($item),
            'active' => $this->isItemActive($item),
            'badge' => $this->getItemBadge($item),
            'has_children' => false,
            'children' => []
        ];

        // Process children recursively
        if (isset($item['children']) && is_array($item['children'])) {
            $children = collect($item['children'])->filter(function ($child) {
                return $this->canAccessMenuItem($child);
            })->map(function ($child) {
                return $this->formatMenuItem($child);
            })->values()->toArray();

            if (!empty($children)) {
                $formatted['children'] = $children;
                $formatted['has_children'] = true;
            }
        }

        return $formatted;
    }

    /**
     * Get URL for menu item
     */
    protected function getItemUrl(array $item): ?string
    {
        if (isset($item['route'])) {
            try {
                if (Route::has($item['route'])) {
                    if (isset($item['params'])) {
                        return route($item['route'], $item['params']);
                    }
                    return route($item['route']);
                }
            } catch (\Exception $e) {
                if ($this->debug) {
                    Log::error('Error generating route URL', [
                        'route' => $item['route'],
                        'error' => $e->getMessage()
                    ]);
                }
                return '#';
            }
        }

        return $item['url'] ?? '#';
    }

    /**
     * Check if menu item is active
     */
    protected function isItemActive(array $item): bool
    {
        if (isset($item['route'])) {
            // Check active patterns first if they exist
            if (isset($item['active_patterns'])) {
                foreach ($item['active_patterns'] as $pattern) {
                    if (request()->routeIs($pattern)) {
                        return true;
                    }
                }
            }
            
            // Check exact route match
            return request()->routeIs($item['route']);
        }

        if (isset($item['url'])) {
            return request()->fullUrl() === $item['url'];
        }

        return false;
    }

    /**
     * Get badge content for menu item
     */
    protected function getItemBadge(array $item): ?array
    {
        if (!isset($item['badge'])) {
            return null;
        }

        $badge = $item['badge'];

        // Dynamic badge with callback
        if (isset($badge['callback']) && is_callable($badge['callback'])) {
            try {
                $value = call_user_func($badge['callback'], $this->user);
                if ($value > 0) {
                    return [
                        'value' => $value,
                        'class' => $badge['class'] ?? 'bg-danger'
                    ];
                }
            } catch (\Exception $e) {
                if ($this->debug) {
                    Log::error('Badge callback error', ['error' => $e->getMessage()]);
                }
            }
        }

        // Static badge
        if (isset($badge['value'])) {
            return [
                'value' => $badge['value'],
                'class' => $badge['class'] ?? 'bg-info'
            ];
        }

        return null;
    }

    /**
     * Get public menu for guests
     */
    protected function getPublicMenu(string $type): Collection
    {
        $menuConfig = $this->config['public'][$type] ?? [];
        $menu = collect();

        foreach ($menuConfig as $section) {
            $items = collect($section['items'] ?? [])->filter(function ($item) {
                // For public menu, only check if route exists
                if (isset($item['route'])) {
                    return Route::has($item['route']);
                }
                return true;
            })->map(function ($item) {
                return $this->formatMenuItem($item);
            })->values();

            if ($items->isNotEmpty()) {
                $menu->push([
                    'title' => $section['title'] ?? null,
                    'items' => $items->toArray(),
                    'icon' => $section['icon'] ?? null,
                    'order' => $section['order'] ?? 100
                ]);
            }
        }

        return $menu;
    }

    /**
     * Check if user has any of the specified roles
     */
    protected function userHasAnyRole(array $roles): bool
    {
        if (!$this->user) {
            return false;
        }

        // Get user role slugs
        $userRoles = $this->user->roles->pluck('slug')->toArray();
        
        // Also check role names for backward compatibility
        $userRoleNames = $this->user->roles->pluck('name')->toArray();
        
        foreach ($roles as $role) {
            if (in_array($role, $userRoles) || in_array($role, $userRoleNames)) {
                return true;
            }
        }

        // Also check using hasRole method if available
        if (method_exists($this->user, 'hasRole')) {
            return $this->user->hasRole($roles);
        }

        return false;
    }

    /**
     * Check if user has any of the specified permissions
     */
    protected function userHasAnyPermission(array $permissions): bool
    {
        if (!$this->user) {
            return false;
        }

        // Check using hasAnyPermission method if available
        if (method_exists($this->user, 'hasAnyPermission')) {
            return $this->user->hasAnyPermission($permissions);
        }

        // Fallback to checking permissions directly
        $userPermissions = $this->user->getAllPermissions()->pluck('name')->toArray();
        
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a gate/policy allows access
     */
    protected function checkGate(string $gate): bool
    {
        if (!$this->user) {
            return false;
        }

        try {
            return Gate::allows($gate);
        } catch (\Exception $e) {
            if ($this->debug) {
                Log::warning('Gate check failed', [
                    'gate' => $gate,
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }

    /**
     * Check if a module is enabled
     */
    protected function isModuleEnabled(string $module): bool
    {
        // Check in enabled modules array
        if (!empty($this->enabledModules)) {
            return in_array($module, $this->enabledModules);
        }

        // Fallback to checking config
        return config("modules.{$module}.enabled", true);
    }

    /**
     * Check if current user is an applicant
     */
    protected function isApplicant(): bool
    {
        if (!$this->user) {
            return false;
        }

        return $this->userHasAnyRole(['applicant', 'admission-candidate']) 
            || $this->user->user_type === 'applicant';
    }

    /**
     * Get breadcrumbs for current page
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $route = request()->route();

        if (!$route) {
            return $breadcrumbs;
        }

        // Always add home
        $breadcrumbs[] = [
            'label' => 'Home',
            'url' => route('dashboard'),
            'icon' => 'fas fa-home'
        ];

        // Get breadcrumb config for current route
        $routeName = $route->getName();
        if ($routeName) {
            $breadcrumbConfig = config("navigation.breadcrumbs.{$routeName}", []);

            foreach ($breadcrumbConfig as $crumb) {
                if ($this->canAccessMenuItem($crumb)) {
                    $breadcrumbs[] = [
                        'label' => $crumb['label'],
                        'url' => isset($crumb['route']) && Route::has($crumb['route']) 
                            ? route($crumb['route']) 
                            : '#',
                        'icon' => $crumb['icon'] ?? null
                    ];
                }
            }

            // Add current page
            $currentPage = $this->getCurrentPageTitle();
            if ($currentPage) {
                $breadcrumbs[] = [
                    'label' => $currentPage,
                    'url' => null,
                    'active' => true
                ];
            }
        }

        return $breadcrumbs;
    }

    /**
     * Get current page title
     */
    protected function getCurrentPageTitle(): ?string
    {
        $route = request()->route();
        if (!$route) {
            return null;
        }

        // Try to get from route action
        $action = $route->getAction();
        if (isset($action['title'])) {
            return $action['title'];
        }

        // Generate from route name
        $routeName = $route->getName();
        if ($routeName) {
            $name = str_replace('.', ' ', $routeName);
            return ucwords(str_replace('-', ' ', $name));
        }

        return null;
    }

    /**
     * Clear navigation cache for a user
     */
    public function clearUserNavigationCache(User $user): void
    {
        $types = ['sidebar', 'navbar', 'mobile'];
        
        foreach ($types as $type) {
            $cacheKey = "navigation.{$type}.user.{$user->id}." . 
                md5($user->roles->pluck('id')->join(','));
            Cache::forget($cacheKey);
        }
    }

    /**
     * Get quick actions for current user
     */
    public function getQuickActions(): Collection
    {
        $actions = collect();

        if (!$this->user) {
            return $actions;
        }

        // Student quick actions
        if ($this->userHasAnyRole(['student'])) {
            // Check if routes exist before adding
            if (Route::has('registration.index')) {
                $actions->push([
                    'label' => 'Register for Courses',
                    'icon' => 'fas fa-plus-circle',
                    'url' => route('registration.index'),
                    'color' => 'primary'
                ]);
            }
            
            if (Route::has('student.grades.index')) {
                $actions->push([
                    'label' => 'View Grades',
                    'icon' => 'fas fa-chart-line',
                    'url' => route('student.grades.index'),
                    'color' => 'success'
                ]);
            }
            
            if (Route::has('financial.student.payment')) {
                $actions->push([
                    'label' => 'Make Payment',
                    'icon' => 'fas fa-credit-card',
                    'url' => route('financial.student.payment'),
                    'color' => 'warning'
                ]);
            }
        }

        // Faculty quick actions
        if ($this->userHasAnyRole(['faculty', 'instructor'])) {
            if (Route::has('grades.my-sections')) {
                $actions->push([
                    'label' => 'Enter Grades',
                    'icon' => 'fas fa-edit',
                    'url' => route('grades.my-sections'),
                    'color' => 'success'
                ]);
            }
            
            if (Route::has('faculty.sections.current')) {
                $actions->push([
                    'label' => 'My Sections',
                    'icon' => 'fas fa-users',
                    'url' => route('faculty.sections.current'),
                    'color' => 'info'
                ]);
            }
        }

        // Admin quick actions
        if ($this->userHasAnyRole(['admin', 'super-administrator', 'system-administrator'])) {
            if (Route::has('admin.users.index')) {
                $actions->push([
                    'label' => 'Manage Users',
                    'icon' => 'fas fa-users-cog',
                    'url' => route('admin.users.index'),
                    'color' => 'primary'
                ]);
            }
            
            if (Route::has('system.settings.index')) {
                $actions->push([
                    'label' => 'System Settings',
                    'icon' => 'fas fa-cog',
                    'url' => route('system.settings.index'),
                    'color' => 'secondary'
                ]);
            }
        }

        return $actions->take(4); // Limit to 4 quick actions
    }

    /**
     * Enable debug mode for troubleshooting
     */
    public function enableDebug(): void
    {
        $this->debug = true;
    }

    /**
     * Disable debug mode
     */
    public function disableDebug(): void
    {
        $this->debug = false;
    }
}