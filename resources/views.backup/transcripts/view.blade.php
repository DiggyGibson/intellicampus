@extends('layouts.app')

@section('title', 'Academic Transcript')

@section('content')
<div class="container">
    <!-- Header with Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Academic Transcript</h1>
        <div>
            <a href="{{ route('transcripts.download.unofficial', $student->id) }}" 
               class="btn btn-primary">
                <i class="fas fa-download"></i> Download Unofficial
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#requestOfficialModal">
                <i class="fas fa-file-pdf"></i> Request Official
            </button>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Transcript Document -->
    <div class="transcript-document bg-white p-5 shadow-sm" id="transcript-content">
        <!-- Institution Header -->
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-1">{{ $transcriptData['institution']['name'] }}</h2>
            <p class="mb-0">{{ $transcriptData['institution']['address'] }}</p>
            <p>{{ $transcriptData['institution']['city'] }}, {{ $transcriptData['institution']['state'] }} {{ $transcriptData['institution']['zip'] }}</p>
            <p class="mb-0">Tel: {{ $transcriptData['institution']['phone'] }}</p>
            <hr class="my-4">
            <h3 class="h4 text-uppercase">{{ $transcriptData['type'] === 'official' ? 'Official' : 'Unofficial' }} Academic Transcript</h3>
        </div>

        <!-- Student Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="fw-bold">Name:</td>
                        <td>{{ $transcriptData['student']['name'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Student ID:</td>
                        <td>{{ $transcriptData['student']['student_id'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Date of Birth:</td>
                        <td>{{ $transcriptData['student']['date_of_birth'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Program:</td>
                        <td>{{ $transcriptData['student']['program'] }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="fw-bold">Major:</td>
                        <td>{{ $transcriptData['student']['major'] ?? 'Undeclared' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Minor:</td>
                        <td>{{ $transcriptData['student']['minor'] ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Enrollment Date:</td>
                        <td>{{ $transcriptData['student']['enrollment_date'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Status:</td>
                        <td>{{ ucfirst($transcriptData['student']['status']) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Academic Record -->
        <div class="academic-record mb-4">
            <h4 class="h5 fw-bold border-bottom pb-2 mb-3">Academic Record</h4>
            
            @foreach($transcriptData['academic_record'] as $term)
            <div class="term-section mb-4">
                <div class="bg-light p-2 mb-2">
                    <strong>{{ $term['term_name'] }} - {{ $term['academic_year'] }}</strong>
                    <span class="float-end">{{ $term['start_date'] }} - {{ $term['end_date'] }}</span>
                </div>
                
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th width="100">Course</th>
                            <th>Title</th>
                            <th width="60" class="text-center">Credits</th>
                            <th width="60" class="text-center">Grade</th>
                            <th width="80" class="text-center">Points</th>
                            <th width="80" class="text-center">Quality Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($term['courses'] as $course)
                        <tr>
                            <td>{{ $course['code'] }}</td>
                            <td>
                                {{ $course['title'] }}
                                @if(isset($course['repeat']) && $course['repeat'])
                                <span class="badge bg-info">R</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $course['credits'] }}</td>
                            <td class="text-center">
                                <strong>{{ $course['grade'] }}</strong>
                            </td>
                            <td class="text-center">{{ number_format($course['grade_points'], 2) }}</td>
                            <td class="text-center">{{ number_format($course['quality_points'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="2" class="text-end"><strong>Term Totals:</strong></td>
                            <td class="text-center"><strong>{{ $term['credits_attempted'] }}</strong></td>
                            <td></td>
                            <td></td>
                            <td class="text-center"><strong>{{ number_format($term['quality_points'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Term GPA:</td>
                            <td colspan="2" class="text-center">
                                <strong>{{ number_format($term['term_gpa'], 2) }}</strong>
                                @if(isset($term['honors']))
                                <span class="badge bg-success ms-2">{{ $term['honors'] }}</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endforeach
        </div>

        <!-- Transfer Credits (if any) -->
        @if($transcriptData['transfer_credits']['total_credits'] > 0)
        <div class="transfer-credits mb-4">
            <h4 class="h5 fw-bold border-bottom pb-2 mb-3">Transfer Credits</h4>
            <p>Total Transfer Credits: <strong>{{ $transcriptData['transfer_credits']['total_credits'] }}</strong></p>
            <p>Transfer Institutions: 
                @foreach($transcriptData['transfer_credits']['institutions'] as $institution)
                    {{ $institution }}@if(!$loop->last), @endif
                @endforeach
            </p>
        </div>
        @endif

        <!-- Cumulative Summary -->
        <div class="cumulative-summary bg-light p-3 mb-4">
            <h4 class="h5 fw-bold mb-3">Cumulative Summary</h4>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Total Credits Attempted:</td>
                            <td class="text-end"><strong>{{ $transcriptData['summary']['total_credits_attempted'] }}</strong></td>
                        </tr>
                        <tr>
                            <td>Total Credits Earned:</td>
                            <td class="text-end"><strong>{{ $transcriptData['summary']['total_credits_earned'] }}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Total Quality Points:</td>
                            <td class="text-end"><strong>{{ number_format($transcriptData['summary']['total_quality_points'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Cumulative GPA:</td>
                            <td class="text-end">
                                <strong class="fs-5">{{ number_format($transcriptData['summary']['cumulative_gpa'], 2) }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Academic Honors -->
        @if(count($transcriptData['honors']) > 0)
        <div class="honors mb-4">
            <h4 class="h5 fw-bold border-bottom pb-2 mb-3">Academic Honors & Awards</h4>
            <ul class="list-unstyled">
                @foreach($transcriptData['honors'] as $honor)
                <li class="mb-2">
                    <strong>{{ $honor['type'] }}</strong>
                    @if(isset($honor['term']))
                    - {{ $honor['term'] }}
                    @endif
                    @if(isset($honor['details']))
                    <br><small class="text-muted">{{ $honor['details'] }}</small>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Degree Information (if graduated) -->
        @if($transcriptData['degree'])
        <div class="degree-info bg-success text-white p-3 mb-4">
            <h4 class="h5 fw-bold mb-2">Degree Awarded</h4>
            <p class="mb-1">{{ $transcriptData['degree']['degree_type'] }} in {{ $transcriptData['degree']['major'] }}</p>
            @if($transcriptData['degree']['minor'])
            <p class="mb-1">Minor: {{ $transcriptData['degree']['minor'] }}</p>
            @endif
            <p class="mb-1">Conferred: {{ $transcriptData['degree']['graduation_date'] }}</p>
            @if($transcriptData['degree']['honors'])
            <p class="mb-0"><em>{{ $transcriptData['degree']['honors'] }}</em></p>
            @endif
        </div>
        @endif

        <!-- Footer -->
        <div class="transcript-footer mt-5 pt-3 border-top">
            <div class="row">
                <div class="col-md-6">
                    <p class="small text-muted mb-1">Generated: {{ $transcriptData['generated_at']->format('F d, Y h:i A') }}</p>
                    @if($transcriptData['type'] === 'unofficial')
                    <p class="small text-danger fw-bold">This is an UNOFFICIAL transcript and should not be considered as an official university document.</p>
                    @endif
                </div>
                <div class="col-md-6 text-end">
                    @if($transcriptData['verification_code'])
                    <p class="small text-muted mb-1">Verification Code: <strong>{{ $transcriptData['verification_code'] }}</strong></p>
                    <p class="small text-muted">Verify at: {{ config('app.url') }}/transcript/verify</p>
                    @endif
                </div>
            </div>
            
            @if($transcriptData['type'] === 'official')
            <div class="signature-section mt-4">
                <div class="row">
                    <div class="col-md-6 offset-md-6 text-center">
                        <div class="signature-line border-bottom mb-2" style="height: 50px;"></div>
                        <p class="mb-0">{{ $transcriptData['institution']['registrar'] }}</p>
                        <p class="small text-muted">{{ $transcriptData['institution']['registrar_title'] }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-4">
        <h5>Legend</h5>
        <div class="row small text-muted">
            <div class="col-md-6">
                <p><strong>Grades:</strong> A (4.0), A- (3.7), B+ (3.3), B (3.0), B- (2.7), C+ (2.3), C (2.0), C- (1.7), D+ (1.3), D (1.0), F (0.0)</p>
                <p><strong>Special Grades:</strong> W (Withdrawal), I (Incomplete), P (Pass), NP (No Pass), AU (Audit), IP (In Progress)</p>
            </div>
            <div class="col-md-6">
                <p><strong>R:</strong> Repeated Course</p>
                <p><strong>Quality Points:</strong> Grade Points × Credits</p>
                <p><strong>GPA:</strong> Total Quality Points ÷ Total Credits Attempted</p>
            </div>
        </div>
    </div>
</div>

<!-- Request Official Transcript Modal -->
<div class="modal fade" id="requestOfficialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('transcripts.request.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title">Request Official Transcript</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Note:</strong> Official transcripts require payment and processing time.
                        Regular processing: 3-5 business days. Rush processing: 1 business day.
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="delivery_method" class="form-label">Delivery Method *</label>
                            <select name="delivery_method" id="delivery_method" class="form-select" required>
                                <option value="">Select...</option>
                                <option value="electronic">Electronic (Email)</option>
                                <option value="mail">Mail (Postal)</option>
                                <option value="pickup">Pickup (In Person)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="copies" class="form-label">Number of Copies *</label>
                            <input type="number" name="copies" id="copies" class="form-control" 
                                   min="1" max="10" value="1" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="recipient_name" class="form-label">Recipient Name *</label>
                        <input type="text" name="recipient_name" id="recipient_name" 
                               class="form-control" required>
                    </div>
                    
                    <div class="mb-3" id="email-field" style="display: none;">
                        <label for="recipient_email" class="form-label">Recipient Email *</label>
                        <input type="email" name="recipient_email" id="recipient_email" 
                               class="form-control">
                    </div>
                    
                    <div class="mb-3" id="address-field" style="display: none;">
                        <label for="mailing_address" class="form-label">Mailing Address *</label>
                        <textarea name="mailing_address" id="mailing_address" 
                                  class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose of Request *</label>
                        <textarea name="purpose" id="purpose" class="form-control" 
                                  rows="2" required></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" name="rush_order" id="rush_order" 
                               class="form-check-input" value="1">
                        <label for="rush_order" class="form-check-label">
                            Rush Order (Additional $25 fee)
                        </label>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>Fees:</strong>
                        <ul class="mb-0">
                            <li>Regular Processing: $10 per copy</li>
                            <li>Rush Processing: $10 per copy + $25 rush fee</li>
                        </ul>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .transcript-document {
            margin: 0;
            padding: 20px !important;
            box-shadow: none !important;
        }
        
        .btn, .modal, .legend {
            display: none !important;
        }
        
        body {
            background: white !important;
        }
    }
    
    .transcript-document {
        max-width: 850px;
        margin: 0 auto;
        font-size: 14px;
    }
    
    .signature-line {
        width: 250px;
        margin: 0 auto;
    }
</style>
@endpush

@push('scripts')
<script>
    // Show/hide fields based on delivery method
    document.getElementById('delivery_method').addEventListener('change', function() {
        const emailField = document.getElementById('email-field');
        const addressField = document.getElementById('address-field');
        
        emailField.style.display = 'none';
        addressField.style.display = 'none';
        
        if (this.value === 'electronic') {
            emailField.style.display = 'block';
            document.getElementById('recipient_email').required = true;
            document.getElementById('mailing_address').required = false;
        } else if (this.value === 'mail') {
            addressField.style.display = 'block';
            document.getElementById('mailing_address').required = true;
            document.getElementById('recipient_email').required = false;
        }
    });
</script>
@endpush
@endsection