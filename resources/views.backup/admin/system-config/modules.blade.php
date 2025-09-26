{{-- File: resources/views/admin/system-config/modules.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Module Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.system-config.index') }}">System Configuration</a></li>
                    <li class="breadcrumb-item active">Modules</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Modules Grid --}}
    <div class="row">
        @forelse($modules as $module)
            <div class="col-md-4 mb-4">
                <div class="card shadow {{ $module->is_enabled ? '' : 'bg-light' }}">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-{{ $module->icon ?? 'cube' }}"></i> {{ $module->name }}
                            </h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input module-toggle" 
                                       type="checkbox" 
                                       data-module-id="{{ $module->id }}"
                                       {{ $module->is_enabled ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $module->description ?? 'No description available' }}</p>
                        <div class="mt-2">
                            <small class="text-muted">
                                Code: {{ $module->code }}<br>
                                Version: {{ $module->version ?? '1.0.0' }}<br>
                                Status: <span class="badge bg-{{ $module->is_enabled ? 'success' : 'secondary' }}">
                                    {{ $module->is_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No modules found. Modules will be populated from the database.
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.module-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const moduleId = this.dataset.moduleId;
        const isEnabled = this.checked;
        
        fetch(`{{ route('admin.system-config.modules.toggle', '') }}/${moduleId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const card = this.closest('.card');
                if (isEnabled) {
                    card.classList.remove('bg-light');
                } else {
                    card.classList.add('bg-light');
                }
                
                // Update status badge
                const badge = card.querySelector('.badge');
                badge.className = `badge bg-${isEnabled ? 'success' : 'secondary'}`;
                badge.textContent = isEnabled ? 'Enabled' : 'Disabled';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.checked = !this.checked; // Revert on error
        });
    });
});
</script>
@endpush
@endsection