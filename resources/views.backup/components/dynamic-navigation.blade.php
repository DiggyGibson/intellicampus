{{-- Dynamic Navigation Component View --}}
{{-- Path: resources/views/components/dynamic-navigation.blade.php --}}

<nav class="{{ $containerClass }} {{ $type }}-navigation" data-navigation-type="{{ $type }}">
    @if($hasItems())
        @foreach($menuItems as $section)
            {{-- Section Container --}}
            <div class="nav-section {{ $loop->first ? 'first-section' : '' }} {{ $loop->last ? 'last-section' : '' }}">
                {{-- Section Title (optional) --}}
                @if($showSectionTitles && isset($section['title']) && $section['title'])
                    <div class="nav-section-title">
                        @if(isset($section['icon']))
                            <i class="{{ $section['icon'] }} me-1"></i>
                        @endif
                        {{ $section['title'] }}
                    </div>
                @endif
                
                {{-- Section Items --}}
                <ul class="nav-list">
                    @foreach($section['items'] as $item)
                        @include('components.navigation.nav-item', ['item' => $item])
                    @endforeach
                </ul>
            </div>
            
            {{-- Section Separator (except for last) --}}
            @if(!$loop->last && $type === 'sidebar')
                <div class="nav-section-separator"></div>
            @endif
        @endforeach
        
        {{-- Quick Actions (Sidebar only) --}}
        @if($type === 'sidebar' && $quickActions->isNotEmpty())
            <div class="nav-section quick-actions-section">
                <div class="nav-section-title">
                    <i class="fas fa-bolt me-1"></i>
                    Quick Actions
                </div>
                <div class="quick-actions-grid">
                    @foreach($quickActions->take(6) as $action)
                        <a href="{{ $action['url'] ?? '#' }}" 
                           class="quick-action-item" 
                           title="{{ $action['label'] }}">
                            <i class="{{ $action['icon'] ?? 'fas fa-circle' }}"></i>
                            <span>{{ Str::limit($action['label'], 10) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="nav-empty-state">
            @if(!$user)
                {{-- Guest Message --}}
                <div class="text-center py-4">
                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Please log in to access the menu</p>
                    @if(Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Login
                        </a>
                    @endif
                </div>
            @else
                {{-- No Items Message --}}
                <div class="text-center py-4">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No menu items available</p>
                    <small class="text-muted">Contact administrator if you believe this is an error</small>
                </div>
            @endif
        </div>
    @endif
</nav>

{{-- Dynamic Navigation Styles --}}
<style>
    .dynamic-navigation {
        width: 100%;
    }
    
    /* Section Styles */
    .nav-section {
        margin-bottom: 0.5rem;
    }
    
    .nav-section-title {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 0.25rem;
    }
    
    .nav-section-separator {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 0.75rem 1rem;
    }
    
    /* Nav List */
    .nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    /* Quick Actions Grid */
    .quick-actions-section {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        padding: 0.5rem 1rem;
    }
    
    .quick-action-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 0.375rem;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 0.75rem;
        text-align: center;
    }
    
    .quick-action-item:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        transform: translateY(-2px);
    }
    
    .quick-action-item i {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }
    
    .quick-action-item span {
        display: block;
        font-size: 0.65rem;
    }
    
    /* Empty State */
    .nav-empty-state {
        padding: 2rem 1rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    /* Mobile Navigation Specific */
    .mobile-navigation {
        background: white;
        color: #333;
    }
    
    .mobile-navigation .nav-section-title {
        color: #6c757d;
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        margin: 0;
    }
    
    .mobile-navigation .nav-section-separator {
        background: #dee2e6;
    }
    
    .mobile-navigation .nav-empty-state {
        color: #6c757d;
    }
    
    /* Navbar Navigation Specific */
    .navbar-navigation {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .navbar-navigation .nav-section {
        display: flex;
        margin-bottom: 0;
    }
    
    .navbar-navigation .nav-list {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .navbar-navigation .nav-section-title {
        display: none;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .sidebar-navigation {
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            z-index: 1050;
            transition: left 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-navigation.active {
            left: 0;
        }
    }
</style>

{{-- Dynamic Navigation JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize navigation based on type
    const navigation = document.querySelector('[data-navigation-type]');
    if (!navigation) return;
    
    const type = navigation.dataset.navigationType;
    
    // Handle mobile sidebar toggle
    if (type === 'sidebar') {
        // Check if we're on mobile
        if (window.innerWidth <= 576) {
            navigation.classList.add('mobile-sidebar');
        }
        
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 576) {
                navigation.classList.add('mobile-sidebar');
            } else {
                navigation.classList.remove('mobile-sidebar', 'active');
            }
        });
    }
    
    // Handle expandable menu items
    const expandableItems = navigation.querySelectorAll('.has-children > .nav-link');
    expandableItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (item.getAttribute('href') === '#') {
                e.preventDefault();
                const parent = item.parentElement;
                parent.classList.toggle('expanded');
                
                // Update aria-expanded
                const isExpanded = parent.classList.contains('expanded');
                item.setAttribute('aria-expanded', isExpanded);
                
                // Animate submenu
                const submenu = parent.querySelector('.nav-submenu');
                if (submenu) {
                    if (isExpanded) {
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    } else {
                        submenu.style.maxHeight = '0';
                    }
                }
            }
        });
    });
    
    // Highlight active section on scroll (for single-page navigation)
    if (type === 'navbar') {
        const sections = document.querySelectorAll('section[id]');
        const navItems = navigation.querySelectorAll('.nav-link');
        
        function highlightNavOnScroll() {
            const scrollY = window.pageYOffset;
            
            sections.forEach(section => {
                const sectionHeight = section.offsetHeight;
                const sectionTop = section.offsetTop - 100;
                const sectionId = section.getAttribute('id');
                
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    navItems.forEach(item => {
                        item.classList.remove('active');
                        if (item.getAttribute('href') === '#' + sectionId) {
                            item.classList.add('active');
                        }
                    });
                }
            });
        }
        
        window.addEventListener('scroll', highlightNavOnScroll);
    }
});

// Global function to toggle mobile sidebar
function toggleMobileSidebar() {
    const sidebar = document.querySelector('.sidebar-navigation');
    if (sidebar) {
        sidebar.classList.toggle('active');
        
        // Also toggle overlay if it exists
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.classList.toggle('active');
        }
    }
}
</script>