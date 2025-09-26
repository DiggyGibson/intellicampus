@extends('layouts.app')

@section('title', 'Transcript Verification - Valid')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <!-- Success Message -->
                    <h1 class="h3 mb-3 text-success">Transcript Verified Successfully</h1>
                    
                    <p class="lead mb-4">
                        This is a valid {{ ucfirst($verification->type) }} transcript issued by 
                        {{ config('app.institution_name', 'IntelliCampus University') }}.
                    </p>
                    
                    <!-- Verification Details -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Verification Details</h5>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-end fw-bold" width="40%">Verification Code:</td>
                                    <td class="text-start">
                                        <span class="badge bg-primary">{{ $verification->verification_code }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">Student Name:</td>
                                    <td class="text-start">{{ $student->user->name ?? 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">Student ID:</td>
                                    <td class="text-start">{{ $student->student_id }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">Program:</td>
                                    <td class="text-start">{{ $student->program->name ?? $student->major ?? 'Undeclared' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">Transcript Type:</td>
                                    <td class="text-start">
                                        <span class="badge bg-{{ $verification->type == 'official' ? 'primary' : 'secondary' }}">
                                            {{ ucfirst($verification->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">Generated Date:</td>
                                    <td class="text-start">{{ $verification->generated_at->format('F d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">Valid Until:</td>
                                    <td class="text-start">{{ $verification->expires_at->format('F d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold">Verification Count:</td>
                                    <td class="text-start">{{ $verification->verification_count }} time(s)</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="alert alert-info text-start">
                        <h6 class="alert-heading"><i class="fas fa-shield-alt me-2"></i>Security Notice</h6>
                        <p class="mb-0">
                            This verification confirms the authenticity of the transcript. The transcript content 
                            should match the information displayed above. If you have any concerns about the 
                            validity of this document, please contact the Registrar's Office at 
                            {{ config('app.institution_phone', '(555) 123-4567') }}.
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="mt-4">
                        @auth
                            @if(Auth::user()->hasRole(['super-administrator', 'admin', 'registrar']))
                                <a href="{{ route('transcripts.view', $student->id) }}" class="btn btn-primary me-2">
                                    <i class="fas fa-file-alt me-2"></i>View Full Transcript
                                </a>
                            @endif
                        @endauth
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="fas fa-print me-2"></i>Print Verification
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Institution Footer -->
            <div class="text-center mt-4 text-muted">
                <p>
                    {{ config('app.institution_name', 'IntelliCampus University') }}<br>
                    {{ config('app.institution_address', '123 University Ave') }}<br>
                    {{ config('app.institution_city', 'City') }}, {{ config('app.institution_state', 'State') }} {{ config('app.institution_zip', '12345') }}<br>
                    {{ config('app.institution_phone', '(555) 123-4567') }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn {
            display: none !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
    }
</style>
@endpush
@endsection