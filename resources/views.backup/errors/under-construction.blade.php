{{-- resources/views/errors/under-construction.blade.php --}}
@extends('layouts.app')

@section('title', 'Under Construction')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-hard-hat" style="font-size: 4rem; color: #ffc107;"></i>
                    </div>
                    
                    <h2 class="mb-3">{{ $module ?? 'This Module' }} - Under Construction</h2>
                    
                    <p class="text-muted mb-4">
                        The <strong>{{ $module ?? 'requested' }}</strong> module's 
                        <strong>{{ $action ?? 'page' }}</strong> is currently being developed.
                    </p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This is a placeholder page while the actual functionality is being implemented.
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Return to Dashboard
                        </a>
                        
                        <button onclick="window.history.back()" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </button>
                    </div>
                    
                    <hr class="my-4">
                    
                    <p class="text-muted small">
                        If you believe this page should be available, please contact the system administrator.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection