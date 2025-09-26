<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IntelliCampus') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    @stack('styles')
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Main Layout Styles */
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        
        .main-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
        }
        
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: margin-left 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .main-content.sidebar-collapsed {
            margin-left: 60px;
        }
        
        .content-wrapper {
            flex: 1;
            padding: 20px;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }
        
        .breadcrumb-item a:hover {
            color: #1e3c72;
        }
        
        .breadcrumb-item.active {
            color: #1e3c72;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        
        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-right: 1rem;
        }
        
        .stat-icon.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.bg-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); }
        .stat-icon.bg-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.bg-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        
        .stat-content h3 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
            color: #2c3e50;
        }
        
        .stat-content p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
        }
        
        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        /* Footer */
        footer {
            background: white;
            padding: 1rem;
            margin-top: auto;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        {{-- Include Sidebar Component --}}
        @include('components.navigation.sidebar')
        
        {{-- Mobile Sidebar Overlay --}}
        <div class="sidebar-overlay" onclick="toggleMobileSidebar()"></div>
        
        {{-- Main Content Area --}}
        <div class="main-content" id="mainContent">
            {{-- Include Navbar Component (Top Navigation) --}}
            @include('components.navigation.navbar')
            
            {{-- Flash Messages --}}
            <div class="container-fluid px-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-times-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
            
            {{-- Page Content --}}
            <div class="content-wrapper">
                {{-- Page Header (if title is set) --}}
                @if(isset($pageTitle) || View::hasSection('title'))
                    <div class="page-header">
                        <h1 class="page-title">
                            @yield('title', $pageTitle ?? 'Dashboard')
                        </h1>
                        @if(View::hasSection('subtitle'))
                            <p class="text-muted mb-0">@yield('subtitle')</p>
                        @endif
                    </div>
                @endif
                
                {{-- Main Content --}}
                @yield('content')
            </div>
            
            {{-- Footer --}}
            <footer class="text-center text-muted">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6 text-md-start">
                            <small>&copy; {{ date('Y') }} {{ config('app.name', 'IntelliCampus') }}. All rights reserved.</small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small>Version 1.0.0 | 
                                @if(Route::has('help'))
                                    <a href="{{ route('help') }}" class="text-muted">Help</a> |
                                @endif
                                @if(Route::has('privacy'))
                                    <a href="{{ route('privacy') }}" class="text-muted">Privacy</a> |
                                @endif
                                @if(Route::has('terms'))
                                    <a href="{{ route('terms') }}" class="text-muted">Terms</a>
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle sidebar on mobile
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
            if (overlay) {
                overlay.classList.toggle('active');
            }
        }
        
        // Toggle sidebar collapse on desktop
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth > 768) {
                // Desktop: collapse sidebar
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('sidebar-collapsed');
                
                // Save preference
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            } else {
                // Mobile: show/hide sidebar
                toggleMobileSidebar();
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Restore sidebar state from localStorage
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed && window.innerWidth > 768) {
                document.getElementById('sidebar')?.classList.add('collapsed');
                document.getElementById('mainContent')?.classList.add('sidebar-collapsed');
            }
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert:not(.alert-permanent)').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Update last activity time (for session management)
            let lastActivity = Date.now();
            const activityInterval = 5 * 60 * 1000; // 5 minutes
            
            document.addEventListener('mousemove', function() {
                if (Date.now() - lastActivity > activityInterval) {
                    lastActivity = Date.now();
                    // Send activity ping to server
                    if (typeof fetch !== 'undefined') {
                        fetch('/api/refresh-session', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                    }
                }
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth > 768) {
                // Remove mobile classes when switching to desktop
                sidebar?.classList.remove('active');
                document.querySelector('.sidebar-overlay')?.classList.remove('active');
            } else {
                // Remove desktop collapse when switching to mobile
                sidebar?.classList.remove('collapsed');
                mainContent?.classList.remove('sidebar-collapsed');
            }
        });
        
        // Global search functionality
        function performSearch(query) {
            if (!query) return;
            
            // You can implement AJAX search here
            window.location.href = '/search?q=' + encodeURIComponent(query);
        }
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt+S for search focus
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                document.querySelector('input[name="q"]')?.focus();
            }
            
            // Alt+M for menu toggle
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                toggleSidebar();
            }
            
            // Escape to close modals/dropdowns
            if (e.key === 'Escape') {
                // Close any open dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>