@extends('layouts.portal')

@section('title', 'Check Application Status')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-search me-2"></i>Check Application Status
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Enter your email and application reference to check your application status.</p>
                    
                    <form action="{{ route('admissions.portal.status.check') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="reference" class="form-label">Application Reference <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('reference') is-invalid @enderror" 
                                   id="reference" 
                                   name="reference" 
                                   placeholder="e.g., APP-2025-000001 or UUID"
                                   value="{{ old('reference') }}"
                                   required>
                            <small class="form-text text-muted">
                                Enter your application number or UUID provided when you started your application.
                            </small>
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Check Status
                            </button>
                            <a href="{{ route('admissions.portal.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Portal
                            </a>
                        </div>
                    </form>
                </div>
                
                @if(session('error'))
                    <div class="card-footer bg-danger text-white">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif
            </div>
            
            <div class="text-center mt-3">
                <p class="text-muted">
                    Don't have an application yet? 
                    <a href="{{ route('admissions.portal.start') }}">Start your application</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection