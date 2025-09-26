{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app')

@section('title', '404 - Page Not Found')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-50 align-items-center py-5">
        <div class="col-md-6 text-center">
            <div class="error-content">
                <h1 class="display-1 text-muted">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="mb-4 text-muted">
                    Sorry, the page you are looking for doesn't exist or has been moved.
                </p>
                
                <div class="error-actions">
                    <a href="{{ url('/') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Go Home
                    </a>
                    
                    @if(url()->previous() != url()->current())
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </a>
                    @endif
                </div>
                
                <div class="mt-5">
                    <p class="text-muted">Here are some helpful links:</p>
                    <div class="list-inline">
                        <a href="{{ route('admissions.index') }}" class="list-inline-item">Admissions</a>
                        <span class="list-inline-item">•</span>
                        <a href="{{ route('admissions.portal.index') }}" class="list-inline-item">Apply Online</a>
                        <span class="list-inline-item">•</span>
                        <a href="{{ route('exams.information') }}" class="list-inline-item">Entrance Exams</a>
                        @auth
                            <span class="list-inline-item">•</span>
                            <a href="{{ route('dashboard') }}" class="list-inline-item">Dashboard</a>
                        @else
                            <span class="list-inline-item">•</span>
                            <a href="{{ route('login') }}" class="list-inline-item">Login</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .error-content {
        padding: 2rem;
    }
    .display-1 {
        font-size: 8rem;
        font-weight: 300;
        opacity: 0.5;
    }
    .error-actions {
        margin-top: 2rem;
    }
</style>
@endpush