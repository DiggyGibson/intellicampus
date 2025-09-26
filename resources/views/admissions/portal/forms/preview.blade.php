{{-- resources/views/admissions/portal/forms/preview.blade.php --}}
@extends('layouts.portal')

@section('title', 'Preview Application - ' . $application->application_number)

@section('styles')
<style>
    .preview-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .preview-field {
        margin-bottom: 15px;
    }
    
    .preview-label {
        font-weight: 600;
        color: #495057;
        display: block;
        margin-bottom: 5px;
    }
    
    .preview-value {
        color: #212529;
    }
    
    .empty-value {
        color: #6c757d;
        font-style: italic;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        
        .preview-section {
            page-break-inside: avoid;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h2>Application Preview</h2>
                <div>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn btn-primary" onclick="window.close()">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
            
            {{-- Application Header --}}
            <div class="preview-section">
                <h4>Application Information</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Application Number:</span>
                            <span class="preview-value">{{ $application->application_number }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Application Type:</span>
                            <span class="preview-value">{{ ucfirst($application->application_type) }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Status:</span>
                            <span class="preview-value">{{ ucfirst($application->status) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Personal Information --}}
            <div class="preview-section">
                <h4>Personal Information</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Full Name:</span>
                            <span class="preview-value">
                                {{ $application->first_name }} {{ $application->middle_name }} {{ $application->last_name }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Date of Birth:</span>
                            <span class="preview-value">
                                {{ $application->date_of_birth ? $application->date_of_birth->format('F d, Y') : 'Not provided' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Gender:</span>
                            <span class="preview-value">{{ $application->gender ?? 'Not specified' }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Email:</span>
                            <span class="preview-value">{{ $application->email }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Phone:</span>
                            <span class="preview-value">{{ $application->phone_primary }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="preview-field">
                            <span class="preview-label">Nationality:</span>
                            <span class="preview-value">{{ $application->nationality }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Academic Information --}}
            <div class="preview-section">
                <h4>Academic Information</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="preview-field">
                            <span class="preview-label">Previous Institution:</span>
                            <span class="preview-value">{{ $application->previous_institution ?? 'Not provided' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="preview-field">
                            <span class="preview-label">GPA:</span>
                            <span class="preview-value">
                                {{ $application->previous_gpa ?? 'N/A' }} / {{ $application->gpa_scale ?? '4.0' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="preview-field">
                            <span class="preview-label">Class Rank:</span>
                            <span class="preview-value">
                                @if($application->class_rank && $application->class_size)
                                    {{ $application->class_rank }} of {{ $application->class_size }}
                                @else
                                    Not provided
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Test Scores --}}
            @if($application->test_scores)
            <div class="preview-section">
                <h4>Test Scores</h4>
                <div class="row">
                    @foreach($application->test_scores as $test => $scores)
                        <div class="col-md-4">
                            <div class="preview-field">
                                <span class="preview-label">{{ $test }}:</span>
                                <span class="preview-value">
                                    @if(is_array($scores))
                                        @foreach($scores as $key => $value)
                                            @if($key !== 'test_date')
                                                {{ ucfirst($key) }}: {{ $value }}<br>
                                            @endif
                                        @endforeach
                                    @else
                                        {{ $scores }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Essays --}}
            @if($application->personal_statement || $application->statement_of_purpose)
            <div class="preview-section">
                <h4>Essays</h4>
                @if($application->personal_statement)
                    <div class="preview-field">
                        <span class="preview-label">Personal Statement:</span>
                        <div class="preview-value">
                            {{ Str::limit($application->personal_statement, 200) }}
                            @if(strlen($application->personal_statement) > 200)
                                <br><small class="text-muted">[Truncated for preview]</small>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            @endif
            
            {{-- Activities --}}
            @if($application->extracurricular_activities)
            <div class="preview-section">
                <h4>Activities & Awards</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Activities:</h6>
                        @foreach($application->extracurricular_activities as $activity)
                            <div class="mb-2">
                                <strong>{{ $activity['name'] ?? '' }}</strong>
                                @if($activity['position'] ?? false)
                                    - {{ $activity['position'] }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($application->awards_honors)
                    <div class="col-md-6">
                        <h6>Awards:</h6>
                        @foreach($application->awards_honors as $award)
                            <div class="mb-2">
                                {{ $award['name'] ?? '' }}
                                @if($award['year'] ?? false)
                                    ({{ $award['year'] }})
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            {{-- References --}}
            @if($application->references)
            <div class="preview-section">
                <h4>References</h4>
                <div class="row">
                    @foreach($application->references as $index => $reference)
                        <div class="col-md-4">
                            <div class="preview-field">
                                <span class="preview-label">Reference {{ $index + 1 }}:</span>
                                <span class="preview-value">
                                    {{ $reference['name'] ?? 'Not provided' }}<br>
                                    <small>{{ $reference['email'] ?? '' }}</small>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-close after print
window.onafterprint = function() {
    setTimeout(function() {
        window.close();
    }, 500);
};
</script>
@endsection