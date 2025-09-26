{{-- Top Navigation Bar Component --}}
{{-- Path: resources/views/components/navigation/navbar.blade.php --}}
@php
    use App\Services\NavigationService;
    
    // Get navigation service instance
    try {
        $navigationService = app(NavigationService::class);
        $breadcrumbs = $navigationService->getBreadcrumbs();
    } catch (\Exception $e) {
        $breadcrumbs = [];
    }
@endphp

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
    <div class="container-fluid px-3">
        {{-- Left Side --}}
        <div class="d-flex align-items-center flex-grow-1">
            {{-- Mobile Sidebar Toggle --}}
            <button type="button" class="btn btn-link text-dark d-lg-none me-3 p-0" onclick="toggleMobileSidebar()">
                <i class="fas fa-bars fs-5"></i>
            </button>

            {{-- Breadcrumbs --}}
            @if(!empty($breadcrumbs))
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0 bg-transparent">
                    @foreach($breadcrumbs as $crumb)
                        @if(isset($crumb['active']) && $crumb['active'])
                            <li class="breadcrumb-item active" aria-current="page">
                                @if(isset($crumb['icon']))
                                    <i class="{{ $crumb['icon'] }} me-1 text-muted"></i>
                                @endif
                                {{ $crumb['label'] }}
                            </li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="{{ $crumb['url'] }}" class="text-decoration-none">
                                    @if(isset($crumb['icon']))
                                        <i class="{{ $crumb['icon'] }} me-1"></i>
                                    @endif
                                    {{ $crumb['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
            @endif
        </div>

        {{-- Right Side Items --}}
        <div class="d-flex align-items-center gap-2">
            {{-- Search (Desktop) --}}
            @if(Route::has('search'))
            <form class="d-none d-lg-flex me-2" action="{{ route('search') }}" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Search..." 
                           name="q" 
                           value="{{ request('q') }}"
                           style="min-width: 200px;">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            @endif

            {{-- Quick Actions --}}
            @auth
                @php
                    $quickActions = $navigationService->getQuickActions() ?? collect();
                @endphp
                @if($quickActions->isNotEmpty())
                <div class="dropdown d-none d-md-block">
                    <button class="btn btn-link text-dark p-2" data-bs-toggle="dropdown" title="Quick Actions">
                        <i class="fas fa-bolt"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Quick Actions</h6></li>
                        @foreach($quickActions->take(5) as $action)
                            <li>
                                <a class="dropdown-item" href="{{ $action['url'] ?? '#' }}">
                                    <i class="{{ $action['icon'] ?? 'fas fa-circle' }} me-2 text-primary"></i>
                                    {{ $action['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            @endauth

            {{-- Notifications --}}
            @auth
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative p-2" 
                            data-bs-toggle="dropdown" 
                            title="Notifications">
                        <i class="fas fa-bell fs-5"></i>
                        @php
                            $unreadCount = Auth::user()->unreadNotifications->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <span>Notifications</span>
                            @if($unreadCount > 0)
                                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">
                                        Mark all as read
                                    </button>
                                </form>
                            @endif
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        @forelse(Auth::user()->unreadNotifications->take(5) as $notification)
                            <li>
                                <a class="dropdown-item py-2" href="#">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            @php
                                                $iconClass = 'fas fa-info-circle text-info';
                                                if(str_contains($notification->type, 'Grade')) {
                                                    $iconClass = 'fas fa-graduation-cap text-success';
                                                } elseif(str_contains($notification->type, 'Payment')) {
                                                    $iconClass = 'fas fa-dollar-sign text-warning';
                                                } elseif(str_contains($notification->type, 'Assignment')) {
                                                    $iconClass = 'fas fa-tasks text-primary';
                                                }
                                            @endphp
                                            <i class="{{ $iconClass }} me-2"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small">
                                                {{ $notification->data['message'] ?? 'New notification' }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="text-center py-3">
                                <i class="fas fa-inbox text-muted mb-2 d-block fs-3"></i>
                                <span class="text-muted">No new notifications</span>
                            </li>
                        @endforelse
                        
                        @if(Route::has('notifications.index') && $unreadCount > 5)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center small" href="{{ route('notifications.index') }}">
                                    View all notifications
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endauth

            {{-- User Menu --}}
            @auth
                <div class="dropdown">
                    <button class="btn btn-link text-dark d-flex align-items-center p-2" 
                            data-bs-toggle="dropdown">
                        {{-- User Avatar --}}
                        <div class="me-2">
                            @if(Auth::user()->profile_photo_url ?? false)
                                <img src="{{ Auth::user()->profile_photo_url }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="rounded-circle"
                                     style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <div class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px; font-size: 0.875rem; font-weight: 500;">
                                    {{ Auth::user()->initials ?? substr(Auth::user()->name, 0, 2) }}
                                </div>
                            @endif
                        </div>
                        {{-- User Name (Desktop only) --}}
                        <span class="d-none d-md-inline me-1">
                            {{ Auth::user()->first_name ?? Auth::user()->name }}
                        </span>
                        <i class="fas fa-chevron-down small"></i>
                    </button>
                    
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 200px;">
                        {{-- User Info Header --}}
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-semibold">{{ Auth::user()->full_name ?? Auth::user()->name }}</div>
                            <div class="text-muted small">{{ Auth::user()->email }}</div>
                            @php
                                $primaryRole = Auth::user()->getPrimaryRole();
                            @endphp
                            @if($primaryRole)
                                <div class="mt-1">
                                    <span class="badge bg-primary">{{ $primaryRole->name }}</span>
                                </div>
                            @endif
                        </li>
                        
                        {{-- Profile & Settings --}}
                        @if(Route::has('profile.edit'))
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-circle me-2 text-muted"></i>
                                    My Profile
                                </a>
                            </li>
                        @endif
                        
                        @if(Route::has('profile.security'))
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('profile.security') }}">
                                    <i class="fas fa-shield-alt me-2 text-muted"></i>
                                    Security Settings
                                </a>
                            </li>
                        @endif
                        
                        {{-- Role Switcher (if multiple roles) --}}
                        @if(Auth::user()->roles->count() > 1)
                            <li><hr class="dropdown-divider"></li>
                            <li class="px-3 py-2">
                                <x-navigation.role-switcher />
                            </li>
                        @endif
                        
                        {{-- My Portal Link --}}
                        @if(Route::has('my-portal'))
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('my-portal') }}">
                                    <i class="fas fa-home me-2 text-muted"></i>
                                    My Portal
                                </a>
                            </li>
                        @endif
                        
                        {{-- Logout --}}
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                {{-- Guest Login Button --}}
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-sign-in-alt me-1"></i>
                        Login
                    </a>
                @endif
            @endauth
        </div>
    </div>
</nav>

{{-- Mobile Search Bar (shown below navbar on mobile) --}}
@if(Route::has('search'))
<div class="bg-light border-bottom d-lg-none px-3 py-2">
    <form action="{{ route('search') }}" method="GET">
        <div class="input-group input-group-sm">
            <input type="text" 
                   class="form-control" 
                   placeholder="Search..." 
                   name="q" 
                   value="{{ request('q') }}">
            <button class="btn btn-outline-secondary" type="submit">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>
@endif