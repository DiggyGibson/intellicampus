<?php
// Path: app/View/Components/DynamicNavigation.php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\NavigationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DynamicNavigation extends Component
{
    /**
     * The navigation type (sidebar, navbar, mobile)
     *
     * @var string
     */
    public $type;
    
    /**
     * The menu items collection
     *
     * @var \Illuminate\Support\Collection
     */
    public $menuItems;
    
    /**
     * The authenticated user
     *
     * @var \App\Models\User|null
     */
    public $user;
    
    /**
     * Quick actions for the user
     *
     * @var \Illuminate\Support\Collection
     */
    public $quickActions;
    
    /**
     * Whether to show section titles
     *
     * @var bool
     */
    public $showSectionTitles;
    
    /**
     * CSS classes for the navigation container
     *
     * @var string
     */
    public $containerClass;
    
    /**
     * Create a new component instance.
     *
     * @param string $type
     * @param bool $showSectionTitles
     * @param string|null $containerClass
     * @return void
     */
    public function __construct(
        $type = 'sidebar',
        $showSectionTitles = true,
        $containerClass = null
    ) {
        $this->type = $type;
        $this->showSectionTitles = $showSectionTitles;
        $this->containerClass = $containerClass ?? $this->getDefaultContainerClass();
        $this->user = Auth::user();
        
        try {
            $navigationService = app(NavigationService::class);
            $this->menuItems = $navigationService->getMenu($type);
            $this->quickActions = $this->user ? $navigationService->getQuickActions() : collect();
        } catch (\Exception $e) {
            Log::error('Failed to load navigation', [
                'type' => $type,
                'user_id' => $this->user?->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to empty collections
            $this->menuItems = collect();
            $this->quickActions = collect();
        }
    }
    
    /**
     * Get default container class based on type
     *
     * @return string
     */
    protected function getDefaultContainerClass(): string
    {
        return match ($this->type) {
            'sidebar' => 'sidebar-navigation',
            'navbar' => 'navbar-navigation',
            'mobile' => 'mobile-navigation',
            default => 'dynamic-navigation'
        };
    }
    
    /**
     * Determine if the navigation has any items
     *
     * @return bool
     */
    public function hasItems(): bool
    {
        return $this->menuItems->isNotEmpty();
    }
    
    /**
     * Get the total count of menu items (including nested)
     *
     * @return int
     */
    public function getItemCount(): int
    {
        $count = 0;
        
        foreach ($this->menuItems as $section) {
            if (isset($section['items'])) {
                $count += count($section['items']);
                
                // Count children
                foreach ($section['items'] as $item) {
                    if (isset($item['children'])) {
                        $count += count($item['children']);
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Check if user has access to at least one admin section
     *
     * @return bool
     */
    public function hasAdminAccess(): bool
    {
        if (!$this->user) {
            return false;
        }
        
        return $this->user->hasAnyRole([
            'admin',
            'super-administrator',
            'system-administrator',
            'academic-administrator',
            'financial-administrator'
        ]);
    }
    
    /**
     * Get the active section name
     *
     * @return string|null
     */
    public function getActiveSection(): ?string
    {
        foreach ($this->menuItems as $section) {
            if (isset($section['items'])) {
                foreach ($section['items'] as $item) {
                    if ($item['active'] ?? false) {
                        return $section['title'] ?? null;
                    }
                    
                    // Check children
                    if (isset($item['children'])) {
                        foreach ($item['children'] as $child) {
                            if ($child['active'] ?? false) {
                                return $section['title'] ?? null;
                            }
                        }
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.dynamic-navigation');
    }
    
    /**
     * Determine if the component should be rendered
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        // Always render for sidebar (might show login prompt for guests)
        if ($this->type === 'sidebar') {
            return true;
        }
        
        // For other types, only render if there are items
        return $this->hasItems();
    }
}