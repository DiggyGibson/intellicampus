{{-- resources/views/components/navigation/sidebar.blade.php --}}
@php
    use App\Services\NavigationService;
    
    // Safely get navigation service
    try {
        $navigationService = app(NavigationService::class);
        $menuSections = $navigationService->getMenu('sidebar');
    } catch (\Exception $e) {
        // Fallback if NavigationService doesn't exist
        $menuSections = [];
    }
@endphp


<aside id="sidebar" class="sidebar">
    {{-- Logo Section --}}
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}" alt="IntelliCampus" class="brand-logo">
            @else
                <div class="brand-logo-placeholder">IC</div>
            @endif
            <span class="brand-text">IntelliCampus</span>
        </a>
    </div>

    {{-- User Info Section --}}
    @auth
    <div class="sidebar-user">
        <div class="user-panel">
            <div class="user-avatar">
                @if(Auth::user()->profile_photo_url ?? false)
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                @else
                    <div class="avatar-placeholder">
                        {{ substr(Auth::user()->first_name ?? Auth::user()->name, 0, 1) }}{{ substr(Auth::user()->last_name ?? '', 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">
                    @php
                        $primaryRole = Auth::user()->roles->first();
                    @endphp
                    <span class="badge badge-role">{{ $primaryRole ? $primaryRole->name : 'User' }}</span>
                </div>
            </div>
            
            {{-- Role Switcher for Multiple Roles --}}
            @if(Auth::user()->roles->count() > 1 && Route::has('switch-role'))
            <div class="role-switcher">
                <button class="btn-role-switch" data-bs-toggle="dropdown">
                    <i class="fas fa-exchange-alt"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <h6 class="dropdown-header">Switch Role</h6>
                    @foreach(Auth::user()->roles as $role)
                    <form method="POST" action="{{ route('switch-role', $role->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item {{ session('active_role') == $role->slug ? 'active' : '' }}">
                            <i class="fas fa-user-tag me-2"></i>{{ $role->name }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endauth

    {{-- Navigation Menu --}}
    <nav class="sidebar-nav">
        <ul class="nav-list">
            {{-- Static Dashboard Link (Always Show) --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            {{-- Dynamic Menu Sections --}}
            @if(!empty($menuSections))
                @foreach($menuSections as $section)
                    {{-- Section Title --}}
                    @if(isset($section['title']) && $section['title'])
                    <li class="nav-section-title">
                        {{ $section['title'] }}
                    </li>
                    @endif

                    {{-- Section Items --}}
                    @if(isset($section['items']) && is_array($section['items']))
                        @foreach($section['items'] as $item)
                            @include('components.navigation.nav-item', ['item' => $item])
                        @endforeach
                    @endif
                @endforeach
            @else
                {{-- Fallback Static Menu if NavigationService fails --}}
                <li class="nav-section-title">Main Menu</li>
                
                @if(Auth::user()->hasRole(['admin', 'super-administrator']))
                <li class="nav-item">
                    <a href="{{ route('system.index') }}" class="nav-link {{ request()->routeIs('system.*') ? 'active' : '' }}">
                        <i class="fas fa-cogs nav-icon"></i>
                        <span class="nav-text">System Settings</span>
                    </a>
                </li>
                @endif
                
                @if(Auth::user()->hasRole('student'))
                <li class="nav-item">
                    @if(Route::has('student.dashboard'))
                    <a href="{{ route('student.dashboard') }}" class="nav-link">
                        <i class="fas fa-graduation-cap nav-icon"></i>
                        <span class="nav-text">Student Portal</span>
                    </a>
                    @endif
                </li>
                @endif
                
                @if(Auth::user()->hasRole('faculty'))
                <li class="nav-item">
                    @if(Route::has('faculty.dashboard'))
                    <a href="{{ route('faculty.dashboard') }}" class="nav-link">
                        <i class="fas fa-chalkboard-teacher nav-icon"></i>
                        <span class="nav-text">Faculty Portal</span>
                    </a>
                    @endif
                </li>
                @endif
            @endif
        </ul>
    </nav>

    {{-- Quick Actions (Bottom of Sidebar) --}}
    <div class="sidebar-footer">
        {{-- Quick Actions --}}
        @if(isset($navigationService))
            @php
                try {
                    $quickActions = $navigationService->getQuickActions();
                } catch (\Exception $e) {
                    $quickActions = collect([]);
                }
            @endphp
            @if($quickActions->isNotEmpty())
            <div class="quick-actions">
                <div class="quick-actions-title">Quick Actions</div>
                <div class="quick-actions-grid">
                    @foreach($quickActions->take(3) as $action)
                    <a href="{{ $action['url'] ?? '#' }}" class="quick-action-btn" title="{{ $action['label'] ?? '' }}">
                        <i class="{{ $action['icon'] ?? 'fas fa-link' }}"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        @endif

        {{-- Settings & Logout --}}
        <div class="sidebar-controls">
            @if(Route::has('profile.edit'))
            <a href="{{ route('profile.edit') }}" class="control-btn" title="Profile Settings">
                <i class="fas fa-user-cog"></i>
            </a>
            @endif
            
            @if(Route::has('notifications.index'))
            <a href="{{ route('notifications.index') }}" class="control-btn" title="Notifications">
                <i class="fas fa-bell"></i>
                @if(Auth::user()->unreadNotifications->count() > 0)
                    <span class="notification-badge">{{ Auth::user()->unreadNotifications->count() }}</span>
                @endif
            </a>
            @endif
            
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="control-btn text-danger" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Sidebar CSS Styles --}}
<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    z-index: 1000;
    transition: all 0.3s ease;
}

.sidebar-brand {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.1);
}

.brand-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: white;
}

.brand-logo {
    width: 40px;
    height: 40px;
    margin-right: 10px;
}

.brand-logo-placeholder {
    width: 40px;
    height: 40px;
    margin-right: 10px;
    background: white;
    color: #1e3c72;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border-radius: 5px;
}

.brand-text {
    font-size: 1.25rem;
    font-weight: bold;
}

.sidebar-user {
    padding: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.05);
}

.user-panel {
    display: flex;
    align-items: center;
    position: relative;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    margin-right: 10px;
    overflow: hidden;
    background: white;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #6c757d;
    color: white;
    font-weight: bold;
}

.user-info {
    flex: 1;
}

.user-name {
    color: white;
    font-weight: 500;
    font-size: 0.9rem;
}

.user-role {
    margin-top: 2px;
}

.badge-role {
    background: rgba(255,255,255,0.2);
    color: white;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 10px;
}

.role-switcher {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
}

.btn-role-switch {
    background: rgba(255,255,255,0.1);
    border: none;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-role-switch:hover {
    background: rgba(255,255,255,0.2);
}

.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-section-title {
    padding: 0.5rem 1.5rem;
    color: rgba(255,255,255,0.6);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
}

.nav-item {
    margin: 2px 0;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    transition: all 0.3s;
    position: relative;
}

.nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}

.nav-link.active {
    background: rgba(255,255,255,0.15);
    color: white;
    border-left: 3px solid #ffc107;
}

.nav-icon {
    width: 20px;
    margin-right: 10px;
    text-align: center;
}

.nav-text {
    flex: 1;
}

.nav-badge {
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.7rem;
    margin-left: 5px;
}

.nav-badge.bg-danger {
    background: #dc3545;
    color: white;
}

.nav-badge.bg-warning {
    background: #ffc107;
    color: #000;
}

.nav-arrow {
    margin-left: auto;
    transition: transform 0.3s;
}

.nav-link[aria-expanded="true"] .nav-arrow {
    transform: rotate(180deg);
}

.nav-submenu {
    list-style: none;
    padding: 0;
    margin: 0;
    background: rgba(0,0,0,0.1);
}

.nav-subitem {
    margin: 0;
}

.nav-sublink {
    display: flex;
    align-items: center;
    padding: 0.5rem 1.5rem 0.5rem 3rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.nav-sublink:hover {
    background: rgba(255,255,255,0.05);
    color: white;
}

.nav-sublink.active {
    color: #ffc107;
}

.nav-subicon {
    width: 15px;
    margin-right: 10px;
    font-size: 0.5rem;
}

.sidebar-footer {
    margin-top: auto;
    border-top: 1px solid rgba(255,255,255,0.1);
    padding: 1rem;
}

.quick-actions {
    margin-bottom: 1rem;
}

.quick-actions-title {
    color: rgba(255,255,255,0.6);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.quick-actions-grid {
    display: flex;
    gap: 0.5rem;
}

.quick-action-btn {
    flex: 1;
    padding: 0.5rem;
    background: rgba(255,255,255,0.1);
    color: white;
    text-align: center;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
}

.quick-action-btn:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

.sidebar-controls {
    display: flex;
    justify-content: space-around;
    padding-top: 0.5rem;
}

.control-btn {
    background: none;
    border: none;
    color: rgba(255,255,255,0.8);
    font-size: 1.1rem;
    padding: 0.5rem;
    cursor: pointer;
    position: relative;
    transition: color 0.3s;
}

.control-btn:hover {
    color: white;
}

.control-btn.text-danger {
    color: #ff6b6b;
}

.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #dc3545;
    color: white;
    font-size: 0.6rem;
    padding: 2px 4px;
    border-radius: 50%;
    min-width: 15px;
    text-align: center;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
}

/* Custom Scrollbar */
.sidebar-nav::-webkit-scrollbar {
    width: 5px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 5px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.3);
}
</style>