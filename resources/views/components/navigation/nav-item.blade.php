{{-- Fixed Navigation Item Component --}}
{{-- Path: resources/views/components/navigation/nav-item.blade.php --}}

@if(isset($item) && is_array($item))
    @if(isset($item['has_children']) && $item['has_children'] && !empty($item['children']))
        {{-- Item with children (dropdown) - FIXED VERSION --}}
        <li class="nav-item has-children {{ $item['active'] ? 'active' : '' }}">
            <a href="#" 
               class="nav-link {{ $item['active'] ? 'active' : '' }}"
               onclick="toggleSubmenu(event, '{{ Str::slug($item['label']) }}')"
               aria-expanded="{{ $item['active'] ? 'true' : 'false' }}">
                <i class="{{ $item['icon'] }} nav-icon"></i>
                <span class="nav-text">{{ $item['label'] }}</span>
                @if(isset($item['badge']))
                <span class="nav-badge {{ $item['badge']['class'] }}">
                    {{ $item['badge']['value'] }}
                </span>
                @endif
                <i class="fas fa-chevron-down nav-arrow" id="arrow-{{ Str::slug($item['label']) }}"></i>
            </a>
            
            {{-- Children submenu - FIXED with proper display logic --}}
            <ul class="nav-submenu" 
                id="nav-children-{{ Str::slug($item['label']) }}"
                style="display: {{ $item['active'] ? 'block' : 'none' }};">
                @foreach($item['children'] as $child)
                    <li class="nav-subitem">
                        <a href="{{ $child['url'] ?? '#' }}" 
                           class="nav-sublink {{ $child['active'] ?? false ? 'active' : '' }}">
                            <i class="fas fa-circle nav-subicon"></i>
                            <span class="nav-subtext">{{ $child['label'] }}</span>
                            @if(isset($child['badge']))
                            <span class="nav-badge {{ $child['badge']['class'] }}">
                                {{ $child['badge']['value'] }}
                            </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @else
        {{-- Regular item without children --}}
        <li class="nav-item">
            <a href="{{ $item['url'] ?? '#' }}" 
               class="nav-link {{ $item['active'] ?? false ? 'active' : '' }}">
                <i class="{{ $item['icon'] ?? 'fas fa-circle' }} nav-icon"></i>
                <span class="nav-text">{{ $item['label'] ?? 'Untitled' }}</span>
                @if(isset($item['badge']))
                <span class="nav-badge {{ $item['badge']['class'] }}">
                    {{ $item['badge']['value'] }}
                </span>
                @endif
            </a>
        </li>
    @endif
@else
    {{-- Debug output if item is not properly formatted --}}
    @if(config('app.debug'))
        <li class="nav-item">
            <span class="nav-link text-danger">
                <i class="fas fa-exclamation-triangle nav-icon"></i>
                <span class="nav-text">Invalid menu item format</span>
            </span>
        </li>
    @endif
@endif

{{-- Add this JavaScript at the end of the sidebar.blade.php or in layouts/app.blade.php --}}
@once
@push('scripts')
<script>
function toggleSubmenu(event, slug) {
    event.preventDefault();
    
    const submenu = document.getElementById('nav-children-' + slug);
    const arrow = document.getElementById('arrow-' + slug);
    const parentLink = event.currentTarget;
    
    if (submenu) {
        if (submenu.style.display === 'none' || submenu.style.display === '') {
            // Show submenu
            submenu.style.display = 'block';
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            parentLink.setAttribute('aria-expanded', 'true');
            if (arrow) {
                arrow.style.transform = 'rotate(180deg)';
            }
        } else {
            // Hide submenu
            submenu.style.display = 'none';
            submenu.style.maxHeight = '0';
            parentLink.setAttribute('aria-expanded', 'false');
            if (arrow) {
                arrow.style.transform = 'rotate(0deg)';
            }
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial state for all submenus
    const submenus = document.querySelectorAll('.nav-submenu');
    submenus.forEach(submenu => {
        const isActive = submenu.querySelector('.nav-sublink.active');
        if (!isActive && submenu.style.display !== 'block') {
            submenu.style.display = 'none';
        }
    });
});
</script>
@endpush

@push('styles')
<style>
/* Fix for dropdown visibility */
.nav-submenu {
    transition: max-height 0.3s ease-out;
    overflow: hidden;
    background: rgba(0,0,0,0.1);
}

.nav-arrow {
    transition: transform 0.3s ease;
    margin-left: auto;
}

.has-children .nav-link {
    cursor: pointer;
}

/* Ensure submenu stays visible */
.nav-submenu[style*="block"] {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}
</style>
@endpush
@endonce