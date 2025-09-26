@extends('layouts.app')

@section('title', 'Registration Confirmation')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('registration.catalog') }}">Course Catalog</a></li>
            <li class="breadcrumb-item"><a href="{{ route('registration.cart') }}">Shopping Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Confirmation</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <!-- Success Alert -->
            <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <svg class="bi flex-shrink-0 me-3" width="48" height="48" role="img" aria-label="Success:">
                            <circle cx="24" cy="24" r="20" fill="#198754"/>
                            <path d="M16 24l4 4 8-8" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="alert-heading mb-1 fw-bold">Registration Successful!</h4>
                        <p class="mb-0">Your course registration has been processed and confirmed.</p>
                    </div>
                </div>
            </div>

            <!-- Registration Summary Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-clipboard-check me-2"></i>
                            Registration Summary
                        </h5>
                        <span class="badge bg-white text-primary px-3 py-2">
                            {{ $term->name ?? 'Fall 2025' }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Student Information Section -->
                    <div class="bg-light rounded p-3 mb-4">
                        <h6 class="text-muted text-uppercase small mb-3">Student Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Name:</span>
                                    <strong>{{ $student->first_name }} {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Student ID:</span>
                                    <strong>{{ $student->student_id }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Email:</span>
                                    <strong>{{ $student->email }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Program:</span>
                                    <strong>{{ $student->program_name ?? 'Not Specified' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Details -->
                    <div class="bg-light rounded p-3 mb-4">
                        <h6 class="text-muted text-uppercase small mb-3">Registration Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Registration Date:</span>
                                    <strong>{{ now()->format('F d, Y g:i A') }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Confirmation Number:</span>
                                    <strong class="text-primary">REG-{{ $term->id ?? '1' }}-{{ str_pad($student->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Academic Level:</span>
                                    <strong>{{ ucfirst($student->academic_level ?? 'Undergraduate') }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Enrollment Status:</span>
                                    <span class="badge bg-success">Full-Time</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Courses Table -->
                    @if(isset($registered) && count($registered) > 0)
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3 fw-semibold">
                            <i class="fas fa-book-open text-primary me-2"></i>
                            Successfully Registered Courses
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="12%">Course</th>
                                        <th width="25%">Title</th>
                                        <th width="8%" class="text-center">Section</th>
                                        <th width="8%" class="text-center">Credits</th>
                                        <th width="20%">Schedule</th>
                                        <th width="17%">Instructor</th>
                                        <th width="10%" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($registered as $course)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary px-2 py-1">
                                                {{ $course->course_code }}
                                            </span>
                                        </td>
                                        <td>{{ $course->course_title }}</td>
                                        <td class="text-center">{{ $course->section_number }}</td>
                                        <td class="text-center fw-semibold">{{ $course->credits }}</td>
                                        <td>
                                            @if($course->days_of_week && $course->start_time)
                                                <div class="small">
                                                    <div class="fw-semibold">{{ $course->days_of_week }}</div>
                                                    <div class="text-muted">
                                                        {{ \Carbon\Carbon::parse($course->start_time)->format('g:i A') }} - 
                                                        {{ \Carbon\Carbon::parse($course->end_time)->format('g:i A') }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-info-subtle text-info">Online/Async</span>
                                            @endif
                                        </td>
                                        <td>{{ $course->instructor_name ?? 'TBA' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Enrolled
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td colspan="3" class="text-end">Total Credits:</td>
                                        <td class="text-center text-primary fs-5">{{ $totalCredits ?? collect($registered)->sum('credits') }}</td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Waitlisted Courses Table -->
                    @if(isset($waitlisted) && count($waitlisted) > 0)
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3 fw-semibold">
                            <i class="fas fa-clock text-warning me-2"></i>
                            Waitlisted Courses
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course</th>
                                        <th>Title</th>
                                        <th class="text-center">Section</th>
                                        <th class="text-center">Credits</th>
                                        <th class="text-center">Position</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($waitlisted as $course)
                                    <tr>
                                        <td>
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1">
                                                {{ $course->course_code }}
                                            </span>
                                        </td>
                                        <td>{{ $course->course_title }}</td>
                                        <td class="text-center">{{ $course->section_number }}</td>
                                        <td class="text-center">{{ $course->credits }}</td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-secondary">#{{ $course->position }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-hourglass-half me-1"></i>Waitlisted
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-warning border-0 mt-3" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Waitlist Notice:</strong> You will be notified via email if a spot becomes available. 
                            You will have 48 hours to accept the offer before it expires.
                        </div>
                    </div>
                    @endif

                    <!-- Important Information -->
                    <div class="alert alert-info border-0 shadow-sm" role="alert">
                        <h6 class="alert-heading fw-semibold mb-3">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Important Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <ul class="mb-0 small">
                                    <li class="mb-2">
                                        <strong>Confirmation Email:</strong><br>
                                        Sent to {{ $student->email }}
                                    </li>
                                    <li class="mb-2">
                                        <strong>Add/Drop Deadline:</strong><br>
                                        {{ $term->add_drop_deadline ? \Carbon\Carbon::parse($term->add_drop_deadline)->format('F d, Y') : 'September 15, 2025' }}
                                    </li>
                                    <li>
                                        <strong>Withdrawal Deadline:</strong><br>
                                        {{ $term->withdrawal_deadline ? \Carbon\Carbon::parse($term->withdrawal_deadline)->format('F d, Y') : 'November 10, 2025' }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0 small">
                                    <li class="mb-2">
                                        <strong>Payment Due Date:</strong><br>
                                        {{ $term->payment_deadline ?? 'August 25, 2025' }}
                                    </li>
                                    <li class="mb-2">
                                        <strong>Classes Begin:</strong><br>
                                        {{ $term->start_date ? \Carbon\Carbon::parse($term->start_date)->format('F d, Y') : 'September 1, 2025' }}
                                    </li>
                                    <li>
                                        <strong>Financial Aid:</strong><br>
                                        Contact the Financial Aid Office for questions
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-tasks text-success me-2"></i>
                                Next Steps
                            </h6>
                            <ol class="mb-0">
                                <li class="mb-2">Check your email for the registration confirmation</li>
                                <li class="mb-2">Review your class schedule and note important dates</li>
                                <li class="mb-2">Purchase required textbooks and materials</li>
                                <li class="mb-2">Pay tuition and fees by the deadline</li>
                                <li class="mb-2">Attend orientation if you're a new student</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap gap-2 justify-content-between">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('registration.schedule') }}" class="btn btn-primary">
                        <i class="fas fa-calendar-alt me-2"></i>View My Schedule
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print me-2"></i>Print Confirmation
                    </button>
                    <a href="{{ route('registration.catalog') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-plus-circle me-2"></i>Add More Courses
                    </a>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-success">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
    @media print {
        .breadcrumb, .btn, .alert-info, .card-header .badge {
            display: none !important;
        }
        .card {
            border: 1px solid #dee2e6 !important;
        }
        .card-header {
            background-color: #f8f9fa !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table {
            font-size: 12px;
        }
        .badge {
            border: 1px solid #dee2e6;
            padding: 2px 6px !important;
        }
    }
</style>

<!-- Optional: Auto-scroll to top on page load -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.scrollTo(0, 0);
        
        // Optional: Show a toast notification
        @if(session('success'))
        // If you have a toast library, trigger it here
        console.log('Registration completed successfully!');
        @endif
    });
</script>
@endsection