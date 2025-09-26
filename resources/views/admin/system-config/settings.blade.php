{{-- File: resources/views/admin/system-config/settings.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">System Settings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.system-config.index') }}">System Configuration</a></li>
                    <li class="breadcrumb-item active">System Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Settings Categories --}}
    <div class="row">
        @foreach($settings as $category => $categorySettings)
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-{{ $category == 'general' ? 'primary' : ($category == 'academic' ? 'success' : 'info') }} text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-{{ $category == 'general' ? 'cog' : ($category == 'academic' ? 'graduation-cap' : 'dollar-sign') }}"></i>
                            {{ ucfirst($category) }} Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($categorySettings as $setting)
                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $setting->description ?? $setting->key }}
                                    @if(!$setting->is_editable)
                                        <span class="badge bg-secondary">Read Only</span>
                                    @endif
                                </label>
                                
                                @if($setting->type == 'boolean')
                                    <div class="form-check form-switch">
                                        <input class="form-check-input setting-toggle" 
                                               type="checkbox" 
                                               id="setting_{{ $setting->id }}"
                                               data-setting-id="{{ $setting->id }}"
                                               {{ $setting->value == 'true' ? 'checked' : '' }}
                                               {{ !$setting->is_editable ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="setting_{{ $setting->id }}">
                                            {{ $setting->value == 'true' ? 'Enabled' : 'Disabled' }}
                                        </label>
                                    </div>
                                @elseif($setting->type == 'number')
                                    <input type="number" 
                                           class="form-control setting-input" 
                                           data-setting-id="{{ $setting->id }}"
                                           value="{{ $setting->value }}"
                                           {{ !$setting->is_editable ? 'readonly' : '' }}>
                                @elseif($setting->type == 'text')
                                    <input type="text" 
                                           class="form-control setting-input" 
                                           data-setting-id="{{ $setting->id }}"
                                           value="{{ $setting->value }}"
                                           {{ !$setting->is_editable ? 'readonly' : '' }}>
                                @elseif($setting->type == 'json' && $setting->options)
                                    <select class="form-select setting-select" 
                                            data-setting-id="{{ $setting->id }}"
                                            {{ !$setting->is_editable ? 'disabled' : '' }}>
                                        @foreach(json_decode($setting->options, true) as $key => $label)
                                            <option value="{{ $key }}" {{ $setting->value == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <textarea class="form-control setting-textarea" 
                                              data-setting-id="{{ $setting->id }}"
                                              rows="2"
                                              {{ !$setting->is_editable ? 'readonly' : '' }}>{{ $setting->value }}</textarea>
                                @endif
                                
                                <small class="text-muted">Key: {{ $setting->key }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Save Button --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <button type="button" class="btn btn-primary" onclick="saveAllSettings()">
                        <i class="fas fa-save"></i> Save All Settings
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="location.reload()">
                        <i class="fas fa-undo"></i> Reset Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let changedSettings = {};

// Track changes
document.querySelectorAll('.setting-input, .setting-textarea, .setting-select').forEach(element => {
    element.addEventListener('change', function() {
        changedSettings[this.dataset.settingId] = this.value;
    });
});

document.querySelectorAll('.setting-toggle').forEach(element => {
    element.addEventListener('change', function() {
        changedSettings[this.dataset.settingId] = this.checked ? 'true' : 'false';
        // Update label
        const label = this.nextElementSibling;
        label.textContent = this.checked ? 'Enabled' : 'Disabled';
    });
});

function saveAllSettings() {
    if (Object.keys(changedSettings).length === 0) {
        alert('No changes to save');
        return;
    }

    const promises = Object.entries(changedSettings).map(([id, value]) => {
        return fetch(`{{ route('admin.system-config.settings.update', '') }}/${id}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ value: value })
        });
    });

    Promise.all(promises)
        .then(responses => {
            alert('Settings saved successfully');
            changedSettings = {};
            location.reload();
        })
        .catch(error => {
            alert('Error saving settings: ' + error);
        });
}
</script>
@endpush
@endsection