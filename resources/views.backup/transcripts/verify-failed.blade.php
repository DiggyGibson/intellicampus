@extends('layouts.app')

@section('title', 'Transcript Verification - Invalid')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <!-- Error Icon -->
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
                    </div>
                    
                    <!-- Error Message -->
                    <h1 class="h3 mb-3 text-danger">Verification Failed</h1>
                    
                    <p class="lead mb-4">
                        The verification code provided is invalid or has expired.
                    </p>
                    
                    <!-- Possible Reasons -->
                    <div class="card bg-light mb-4">
                        <div class="card-body text-start">
                            <h5 class="card-title">Possible Reasons:</h5>
                            <ul class="mb-0">
                                <li>The verification code is incorrect or has been mistyped</li>
                                <li>The transcript verification has expired (valid for 90 days from generation)</li>
                                <li>The transcript was not issued by {{ config('app.institution_name', 'IntelliCampus University') }}</li>
                                <li>The verification link has been tampered with or is incomplete</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- What to Do -->
                    <div class="alert alert-warning text-start">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>What You Can Do</h6>
                        <ol class="mb-0">
                            <li>Double-check the verification code on the transcript document</li>
                            <li>Ensure you're using the complete verification URL or code</li>
                            <li>Contact the student to request a new official transcript if this one has expired</li>
                            <li>Contact the Registrar's Office for assistance</li>
                        </ol>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Contact Registrar's Office</h5>
                            <p class="mb-2">
                                <i class="fas fa-phone me-2"></i>
                                {{ config('app.institution_phone', '(555) 123-4567') }}
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-envelope me-2"></i>
                                {{ config('app.registrar_email', 'registrar@university.edu') }}
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Monday - Friday, 8:00 AM - 5:00 PM
                            </p>
                        </div>
                    </div>
                    
                    <!-- Try Again -->
                    <div class="mt-4">
                        <form action="{{ url('/verify-transcript') }}" method="GET" class="d-inline-block">
                            <div class="input-group">
                                <input type="text" 
                                       name="code" 
                                       class="form-control" 
                                       placeholder="Enter verification code..."
                                       required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search me-2"></i>Verify Again
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Return Home -->
                    <div class="mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-home me-2"></i>Return to Home
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Institution Footer -->
            <div class="text-center mt-4 text-muted">
                <p>
                    {{ config('app.institution_name', 'IntelliCampus University') }}<br>
                    {{ config('app.institution_address', '123 University Ave') }}<br>
                    {{ config('app.institution_city', 'City') }}, {{ config('app.institution_state', 'State') }} {{ config('app.institution_zip', '12345') }}<br>
                    Office of the Registrar
                </p>
            </div>
        </div>
    </div>
</div>
@endsection