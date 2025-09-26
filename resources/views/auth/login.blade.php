@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="login-container">
                <div class="card shadow-sm border-0">
                    <div class="card-header text-center py-4">
                        <div class="logo-section">
                            <i class="fas fa-graduation-cap logo-icon"></i>
                            <h3 class="mb-0">IntelliCampus</h3>
                            <p class="mb-0 mt-2 text-muted">Sign in to continue</p>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- Session Status --}}
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Error Messages --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            {{-- Email/Username --}}
                            <div class="mb-3">
                                <label for="email" class="form-label">Email or Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input id="email" 
                                           type="text" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           required 
                                           autofocus
                                           placeholder="Enter email or username">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input id="password" 
                                           type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password" 
                                           required 
                                           autocomplete="current-password"
                                           placeholder="Enter password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Remember Me --}}
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember" 
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            {{-- Submit Button --}}
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted">
                            <i class="fas fa-lock me-1"></i>
                            Secure Login Portal
                        </small>
                    </div>
                </div>

                {{-- Bottom Links --}}
                <div class="bottom-links text-center mt-4">
                    <div class="mb-3">
                        <a href="/admissions/portal" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Apply for Admission
                        </a>
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/" class="text-muted text-decoration-none small">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                        <span class="text-muted">•</span>
                        <a href="/contact" class="text-muted text-decoration-none small">
                            <i class="fas fa-phone me-1"></i> Support
                        </a>
                        <span class="text-muted">•</span>
                        <a href="/help" class="text-muted text-decoration-none small">
                            <i class="fas fa-question-circle me-1"></i> Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background: #f5f7fa;
        min-height: 100vh;
    }
    
    .login-container {
        margin-top: 50px;
        margin-bottom: 50px;
    }
    
    .card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    
    .card-header {
        background: white;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .logo-section {
        color: #1e3c72;
    }
    
    .logo-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #1e3c72;
    }
    
    .logo-section h3 {
        color: #1e3c72;
        font-weight: 600;
    }
    
    .form-label {
        font-weight: 500;
        color: #374151;
        font-size: 0.95rem;
    }
    
    .input-group-text {
        border-right: 0;
        border-color: #d1d5db;
    }
    
    .form-control {
        border-left: 0;
        border-color: #d1d5db;
    }
    
    .form-control:focus {
        box-shadow: none;
        border-color: #1e3c72;
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #1e3c72;
        color: #1e3c72;
    }
    
    .btn-primary {
        background: #1e3c72;
        border: none;
        padding: 0.75rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-primary:hover {
        background: #162c54;
        transform: translateY(-1px);
    }
    
    .btn-outline-success {
        border-width: 2px;
        font-weight: 500;
    }
    
    .form-check-input:checked {
        background-color: #1e3c72;
        border-color: #1e3c72;
    }
    
    .alert {
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    
    a {
        color: #1e3c72;
    }
    
    a:hover {
        color: #162c54;
    }
    
    .bottom-links {
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .bottom-links:hover {
        opacity: 1;
    }
    
    @media (max-width: 576px) {
        .login-container {
            margin-top: 20px;
        }
        
        .card-body {
            padding: 1.5rem !important;
        }
    }
</style>
@endsection