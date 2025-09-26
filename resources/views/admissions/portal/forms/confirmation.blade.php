@extends('layouts.portal')

@section('title', 'Application Submitted - Confirmation')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="text-success">Application Submitted Successfully!</h2>
                        <p class="lead mt-3">Thank you for submitting your application</p>
                    </div>

                    <div class="alert alert-success">
                        <h5 class="alert-heading">
                            <i class="fas fa-file-alt me-2"></i>
                            Application #{{ $application->application_number }}
                        </h5>
                        <hr>
                        <p class="mb-0">
                            <strong>Program:</strong> {{ $application->program->name ?? 'N/A' }}<br>
                            <strong>Term:</strong> {{ $application->term->name ?? 'N/A' }}<br>
                            <strong>Submitted on:</strong> {{ $application->submitted_at->format('F d, Y at g:i A') }}
                        </p>
                    </div>

                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">What Happens Next?</h5>
                            <ol class="mb-0">
                                <li class="mb-2">You will receive a confirmation email at <strong>{{ $application->email }}</strong></li>
                                <li class="mb-2">Our admissions team will review your application</li>
                                <li class="mb-2">We may contact you if additional documents are required</li>
                                <li class="mb-2">You will be notified once a decision has been made</li>
                            </ol>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Important Information:</h5>
                        <ul>
                            <li>Your Application UUID: <code>{{ $application->application_uuid }}</code></li>
                            <li>Keep this for your records</li>
                            <li>Check your email regularly for updates</li>
                            <li>Processing time: 2-4 weeks</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="/application/{{ $application->application_uuid }}/download-receipt" 
                           class="btn btn-primary">
                            <i class="fas fa-download me-2"></i> Download Application Receipt
                        </a>
                        <a href="/application/{{ $application->application_uuid }}/preview" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-eye me-2"></i> View Application
                        </a>
                        <a href="/admissions/portal/status" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-chart-line me-2"></i> Check Application Status
                        </a>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Didn't receive confirmation email?</strong> 
                        Check your spam folder or 
                        <a href="/application/{{ $application->application_uuid }}/resend-confirmation" 
                           class="alert-link">click here to resend</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection