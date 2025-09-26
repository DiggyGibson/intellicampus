{{-- Role Switcher Component --}}
{{-- Path: resources/views/components/navigation/role-switcher.blade.php --}}

@if(Auth::check() && Auth::user()->roles->count() > 1)
<div class="role-switcher">
    <div class="text-muted small mb-2">Switch Role:</div>
    
    @php
        $currentRole = session('active_role');
        $primaryRole = Auth::user()->getPrimaryRole();
        
        // If no active role in session, use primary role
        if (!$currentRole && $primaryRole) {
            $currentRole = $primaryRole->slug;
        }
    @endphp
    
    @foreach(Auth::user()->roles()->orderBy('priority')->get() as $role)
        <form method="POST" action="{{ route('switch-role', $role->id) }}" class="role-switch-form mb-1">
            @csrf
            <button type="submit" 
                    class="btn btn-sm w-100 text-start {{ $currentRole === $role->slug ? 'btn-primary' : 'btn-outline-secondary' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        {{-- Role Icon --}}
                        @php
                            $roleIcon = 'fas fa-user';
                            if (str_contains($role->slug, 'admin')) {
                                $roleIcon = 'fas fa-user-shield';
                            } elseif (str_contains($role->slug, 'faculty') || str_contains($role->slug, 'instructor')) {
                                $roleIcon = 'fas fa-chalkboard-teacher';
                            } elseif (str_contains($role->slug, 'student')) {
                                $roleIcon = 'fas fa-user-graduate';
                            } elseif (str_contains($role->slug, 'registrar')) {
                                $roleIcon = 'fas fa-university';
                            } elseif (str_contains($role->slug, 'advisor')) {
                                $roleIcon = 'fas fa-user-tie';
                            } elseif (str_contains($role->slug, 'department')) {
                                $roleIcon = 'fas fa-building';
                            } elseif (str_contains($role->slug, 'applicant')) {
                                $roleIcon = 'fas fa-door-open';
                            }
                        @endphp
                        <i class="{{ $roleIcon }} me-2" style="width: 16px;"></i>
                        
                        {{-- Role Name --}}
                        <span>{{ $role->name }}</span>
                    </div>
                    
                    <div class="d-flex align-items-center gap-1">
                        {{-- Primary Badge --}}
                        @if($role->pivot->is_primary)
                            <span class="badge bg-info" style="font-size: 0.65rem;">Primary</span>
                        @endif
                        
                        {{-- Active Indicator --}}
                        @if($currentRole === $role->slug)
                            <i class="fas fa-check text-white"></i>
                        @endif
                    </div>
                </div>
            </button>
        </form>
    @endforeach
    
    {{-- Current Active Role Display --}}
    <div class="mt-2 pt-2 border-top">
        <div class="text-muted" style="font-size: 0.75rem;">
            Currently viewing as:
            <strong>{{ Auth::user()->roles->where('slug', $currentRole)->first()->name ?? 'Default Role' }}</strong>
        </div>
    </div>
</div>

<style>
    .role-switch-form {
        margin: 0;
    }
    
    .role-switcher .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }
    
    .role-switcher .btn:hover:not(.btn-primary) {
        background-color: var(--bs-gray-100);
        border-color: var(--bs-primary);
        color: var(--bs-primary);
    }
    
    .role-switcher .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
</style>
@endif