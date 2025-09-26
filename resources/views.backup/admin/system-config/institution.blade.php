{{-- File: resources/views/admin/system-config/institution.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Institution Settings</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.system-config.index') }}">System Configuration</a></li>
                            <li class="breadcrumb-item active">Institution Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> Please correct the errors below:
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.system-config.institution.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            {{-- Basic Information --}}
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="institution_name" class="form-label">Institution Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('institution_name') is-invalid @enderror" 
                                   id="institution_name" name="institution_name" 
                                   value="{{ old('institution_name', $institution->institution_name) }}" required>
                            @error('institution_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="institution_code" class="form-label">Institution Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('institution_code') is-invalid @enderror" 
                                           id="institution_code" name="institution_code" 
                                           value="{{ old('institution_code', $institution->institution_code) }}" required>
                                    @error('institution_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="institution_type" class="form-label">Institution Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('institution_type') is-invalid @enderror" 
                                            id="institution_type" name="institution_type" required>
                                        <option value="">Select Type</option>
                                        <option value="university" {{ old('institution_type', $institution->institution_type) == 'university' ? 'selected' : '' }}>University</option>
                                        <option value="college" {{ old('institution_type', $institution->institution_type) == 'college' ? 'selected' : '' }}>College</option>
                                        <option value="institute" {{ old('institution_type', $institution->institution_type) == 'institute' ? 'selected' : '' }}>Institute</option>
                                        <option value="school" {{ old('institution_type', $institution->institution_type) == 'school' ? 'selected' : '' }}>School</option>
                                    </select>
                                    @error('institution_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                   id="website" name="website" 
                                   value="{{ old('website', $institution->website) }}"
                                   placeholder="https://www.example.edu">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Institution Logo</label>
                            @if($institution->logo_path)
                                <div class="mb-2">
                                    <img src="{{ asset($institution->logo_path) }}" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" name="logo" accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF. Max size: 2MB</small>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2" required>{{ old('address', $institution->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" 
                                           value="{{ old('city', $institution->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State/Province <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" 
                                           value="{{ old('state', $institution->state) }}" required>
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                           id="country" name="country" 
                                           value="{{ old('country', $institution->country) }}" required>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" 
                                           value="{{ old('postal_code', $institution->postal_code) }}" required>
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" 
                                           value="{{ old('phone', $institution->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" 
                                           value="{{ old('email', $institution->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Regional Settings --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-globe"></i> Regional Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                                    <select class="form-select @error('timezone') is-invalid @enderror" 
                                            id="timezone" name="timezone" required>
                                        <option value="">Select Timezone</option>
                                        @foreach(timezone_identifiers_list() as $tz)
                                            <option value="{{ $tz }}" {{ old('timezone', $institution->timezone) == $tz ? 'selected' : '' }}>
                                                {{ $tz }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('timezone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency_code" class="form-label">Currency <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('currency_code') is-invalid @enderror" 
                                               id="currency_code" name="currency_code" 
                                               value="{{ old('currency_code', $institution->currency_code) }}" 
                                               maxlength="3" required>
                                        <input type="text" class="form-control @error('currency_symbol') is-invalid @enderror" 
                                               id="currency_symbol" name="currency_symbol" 
                                               value="{{ old('currency_symbol', $institution->currency_symbol) }}" 
                                               maxlength="3" required>
                                    </div>
                                    <small class="text-muted">Code (USD) and Symbol ($)</small>
                                    @error('currency_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_format" class="form-label">Date Format <span class="text-danger">*</span></label>
                                    <select class="form-select @error('date_format') is-invalid @enderror" 
                                            id="date_format" name="date_format" required>
                                        <option value="Y-m-d" {{ old('date_format', $institution->date_format) == 'Y-m-d' ? 'selected' : '' }}>2024-01-15</option>
                                        <option value="d/m/Y" {{ old('date_format', $institution->date_format) == 'd/m/Y' ? 'selected' : '' }}>15/01/2024</option>
                                        <option value="m/d/Y" {{ old('date_format', $institution->date_format) == 'm/d/Y' ? 'selected' : '' }}>01/15/2024</option>
                                        <option value="d-m-Y" {{ old('date_format', $institution->date_format) == 'd-m-Y' ? 'selected' : '' }}>15-01-2024</option>
                                    </select>
                                    @error('date_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_format" class="form-label">Time Format <span class="text-danger">*</span></label>
                                    <select class="form-select @error('time_format') is-invalid @enderror" 
                                            id="time_format" name="time_format" required>
                                        <option value="H:i" {{ old('time_format', $institution->time_format) == 'H:i' ? 'selected' : '' }}>24-hour (14:30)</option>
                                        <option value="h:i A" {{ old('time_format', $institution->time_format) == 'h:i A' ? 'selected' : '' }}>12-hour (2:30 PM)</option>
                                    </select>
                                    @error('time_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Social Media --}}
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-share-alt"></i> Social Media (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="facebook" class="form-label"><i class="fab fa-facebook"></i> Facebook</label>
                            <input type="url" class="form-control" id="facebook" name="social_media[facebook]" 
                                   value="{{ old('social_media.facebook', $institution->social_media['facebook'] ?? '') }}"
                                   placeholder="https://facebook.com/yourinstitution">
                        </div>

                        <div class="mb-3">
                            <label for="twitter" class="form-label"><i class="fab fa-twitter"></i> Twitter</label>
                            <input type="url" class="form-control" id="twitter" name="social_media[twitter]" 
                                   value="{{ old('social_media.twitter', $institution->social_media['twitter'] ?? '') }}"
                                   placeholder="https://twitter.com/yourinstitution">
                        </div>

                        <div class="mb-3">
                            <label for="linkedin" class="form-label"><i class="fab fa-linkedin"></i> LinkedIn</label>
                            <input type="url" class="form-control" id="linkedin" name="social_media[linkedin]" 
                                   value="{{ old('social_media.linkedin', $institution->social_media['linkedin'] ?? '') }}"
                                   placeholder="https://linkedin.com/company/yourinstitution">
                        </div>

                        <div class="mb-3">
                            <label for="instagram" class="form-label"><i class="fab fa-instagram"></i> Instagram</label>
                            <input type="url" class="form-control" id="instagram" name="social_media[instagram]" 
                                   value="{{ old('social_media.instagram', $institution->social_media['instagram'] ?? '') }}"
                                   placeholder="https://instagram.com/yourinstitution">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Institution Settings
                        </button>
                        <a href="{{ route('admin.system-config.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Preview logo before upload
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // You could add preview functionality here
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
@endsection