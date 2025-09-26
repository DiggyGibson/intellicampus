{{-- resources/views/admissions/portal/application-created.blade.php --}}
@extends('layouts.portal')

@section('title', 'Application Created - IntelliCampus')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Application Created Successfully!</h3>
                    
                    <div class="alert alert-warning mt-4">
                        <h5>⚠️ Important: Save Your Application Details</h5>
                    </div>
                    
                    <div class="bg-light p-3 rounded mt-3">
                        <p class="mb-2"><strong>Application Number:</strong></p>
                        <h4 class="text-primary">{{ $application->application_number }}</h4>
                    </div>
                    
                    <div class="bg-light p-3 rounded mt-3">
                        <p class="mb-2"><strong>Application UUID:</strong></p>
                        <div class="input-group">
                            <input type="text" class="form-control text-center" 
                                   value="{{ $application->application_uuid }}" 
                                   id="uuid-field" readonly>
                            <button class="btn btn-primary" onclick="copyUUID()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p>You can continue your application anytime using:</p>
                        <code>{{ url('/admissions/portal/continue') }}/{{ $application->application_uuid }}</code>
                    </div>
                    
                    <hr class="my-4">
                    
                    @if(!$save_and_exit)
                    <div class="d-grid gap-2">
                        <a href="{{ route('apply.form.personal', ['uuid' => $application->application_uuid]) }}"
                           class="btn btn-success btn-lg">
                            Continue Application Now
                        </a>
                        <a href="{{ route('admissions.portal.index') }}" class="btn btn-outline-secondary">
                            Exit (I've saved my details)
                        </a>
                    </div>
                    @else
                    <div class="d-grid gap-2">
                        <a href="{{ route('admissions.portal.index') }}" class="btn btn-primary btn-lg">
                            Return to Portal Home
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyUUID() {
    const field = document.getElementById('uuid-field');
    field.select();
    document.execCommand('copy');
    
    // Show feedback
    toastr.success('UUID copied to clipboard!');
}
</script>
@endsection