{{-- resources/views/exams/public/information.blade.php --}}
@extends('layouts.app')

@section('title', 'Entrance Examinations - IntelliCampus')

@section('content')
<div class="container-fluid py-4">
    {{-- Hero Section --}}
    <div class="bg-gradient-purple text-white rounded-lg p-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 font-weight-bold mb-3">Entrance Examinations</h1>
                <p class="lead mb-4">Comprehensive assessment for admission to IntelliCampus University</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('exams.portal.available') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-clipboard-list me-2"></i>Register for Exam
                    </a>
                    <a href="#exam-schedule" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-calendar me-2"></i>View Schedule
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-pencil-ruler" style="font-size: 150px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Exam Types --}}
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-user-graduate text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Undergraduate</h5>
                    <p class="text-muted small">SAT/ACT Alternative</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-user-tie text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Graduate</h5>
                    <p class="text-muted small">GRE/GMAT Alternative</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-globe text-info" style="font-size: 3rem;"></i>
                    </div>
                    <h5>International</h5>
                    <p class="text-muted small">English Proficiency</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card text-center border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-award text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Scholarship</h5>
                    <p class="text-muted small">Merit-based Awards</p>
                </div>
            </div>
        </div>
    </div>

    {{-- About the Exam --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><i class="fas fa-info-circle me-2"></i>About IntelliCampus Entrance Examination</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="text-primary">Purpose</h5>
                    <p>The IntelliCampus Entrance Examination (ICE) is designed to assess academic readiness and potential for success at our university. It serves as an alternative or supplement to standardized tests.</p>
                    
                    <h5 class="text-primary mt-4">Who Should Take the Exam?</h5>
                    <ul>
                        <li>Students without SAT/ACT scores</li>
                        <li>International students seeking admission</li>
                        <li>Transfer students from non-accredited institutions</li>
                        <li>Scholarship applicants</li>
                        <li>Students seeking placement in honors programs</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h5 class="text-primary">Exam Benefits</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success me-2"></i>No additional cost for applicants</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Multiple test dates available</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Immediate score reporting</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Scholarship consideration</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Course placement guidance</li>
                    </ul>
                    
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Note:</strong> The entrance exam is optional for students with qualifying SAT/ACT scores but may still be taken for scholarship consideration.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Exam Format --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-laptop me-2"></i>Computer-Based Test (CBT)</h4>
                </div>
                <div class="card-body">
                    <h6 class="text-success">Format Details</h6>
                    <ul>
                        <li>Duration: 3 hours</li>
                        <li>Total Questions: 120</li>
                        <li>Question Types: Multiple choice, short answer</li>
                        <li>Adaptive testing for personalized assessment</li>
                    </ul>
                    
                    <h6 class="text-success mt-3">Test Sections</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Questions</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>English Language</td>
                                <td>40</td>
                                <td>60 min</td>
                            </tr>
                            <tr>
                                <td>Mathematics</td>
                                <td>40</td>
                                <td>60 min</td>
                            </tr>
                            <tr>
                                <td>Logical Reasoning</td>
                                <td>20</td>
                                <td>30 min</td>
                            </tr>
                            <tr>
                                <td>General Knowledge</td>
                                <td>20</td>
                                <td>30 min</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h6 class="text-success mt-3">Available At</h6>
                    <ul>
                        <li>Campus Testing Center</li>
                        <li>Authorized Test Centers (50+ locations)</li>
                        <li>Online Proctored (select programs)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>Paper-Based Test (PBT)</h4>
                </div>
                <div class="card-body">
                    <h6 class="text-info">Format Details</h6>
                    <ul>
                        <li>Duration: 3.5 hours</li>
                        <li>Total Questions: 150</li>
                        <li>Question Types: Multiple choice, essay</li>
                        <li>Traditional pen-and-paper format</li>
                    </ul>
                    
                    <h6 class="text-info mt-3">Test Sections</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Questions</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>English & Essay</td>
                                <td>45 + Essay</td>
                                <td>75 min</td>
                            </tr>
                            <tr>
                                <td>Mathematics</td>
                                <td>45</td>
                                <td>60 min</td>
                            </tr>
                            <tr>
                                <td>Science Reasoning</td>
                                <td>30</td>
                                <td>45 min</td>
                            </tr>
                            <tr>
                                <td>Social Studies</td>
                                <td>30</td>
                                <td>30 min</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h6 class="text-info mt-3">Available At</h6>
                    <ul>
                        <li>Main Campus Only</li>
                        <li>Special accommodation venues</li>
                        <li>International test centers (limited)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Exam Schedule --}}
    <div class="card shadow-sm mb-4" id="exam-schedule">
        <div class="card-header bg-warning text-dark">
            <h3 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>2024-2025 Exam Schedule</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Exam Date</th>
                            <th>Registration Deadline</th>
                            <th>Late Registration</th>
                            <th>Format</th>
                            <th>Purpose</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Oct 15, 2024</strong></td>
                            <td>Sep 30, 2024</td>
                            <td>Oct 7, 2024</td>
                            <td><span class="badge bg-success">CBT</span></td>
                            <td>Spring 2025 Admission</td>
                            <td><a href="{{ route('exams.portal.register', 1) }}" class="btn btn-sm btn-primary">Register</a></td>
                        </tr>
                        <tr>
                            <td><strong>Nov 18, 2024</strong></td>
                            <td>Nov 3, 2024</td>
                            <td>Nov 10, 2024</td>
                            <td><span class="badge bg-info">PBT</span></td>
                            <td>Spring 2025 Admission</td>
                            <td><a href="{{ route('exams.portal.register', 2) }}" class="btn btn-sm btn-primary">Register</a></td>
                        </tr>
                        <tr>
                            <td><strong>Dec 2, 2024</strong></td>
                            <td>Nov 17, 2024</td>
                            <td>Nov 24, 2024</td>
                            <td><span class="badge bg-success">CBT</span></td>
                            <td>Fall 2025 Early Decision</td>
                            <td><a href="{{ route('exams.portal.register', 3) }}" class="btn btn-sm btn-primary">Register</a></td>
                        </tr>
                        <tr>
                            <td><strong>Jan 20, 2025</strong></td>
                            <td>Jan 5, 2025</td>
                            <td>Jan 12, 2025</td>
                            <td><span class="badge bg-success">CBT</span></td>
                            <td>Fall 2025 Regular Decision</td>
                            <td><a href="{{ route('exams.portal.register', 4) }}" class="btn btn-sm btn-primary">Register</a></td>
                        </tr>
                        <tr>
                            <td><strong>Feb 15, 2025</strong></td>
                            <td>Jan 31, 2025</td>
                            <td>Feb 7, 2025</td>
                            <td><span class="badge bg-warning text-dark">Both</span></td>
                            <td>Fall 2025 Regular Decision</td>
                            <td><a href="{{ route('exams.portal.register', 5) }}" class="btn btn-sm btn-primary">Register</a></td>
                        </tr>
                        <tr>
                            <td><strong>Mar 22, 2025</strong></td>
                            <td>Mar 7, 2025</td>
                            <td>Mar 14, 2025</td>
                            <td><span class="badge bg-success">CBT</span></td>
                            <td>Fall 2025 Late/Transfer</td>
                            <td><a href="{{ route('exams.portal.register', 6) }}" class="btn btn-sm btn-primary">Register</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-3">
                <i class="fas fa-info-circle me-1"></i>
                Late registration incurs an additional fee of $25. Walk-in registration subject to availability.
            </p>
        </div>
    </div>

    {{-- Preparation Resources --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0"><i class="fas fa-book-open me-2"></i>Exam Preparation</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-download text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Study Guide</h6>
                            <p class="mb-0 text-muted small">Comprehensive preparation material</p>
                            <a href="#" class="btn btn-sm btn-outline-primary mt-2">Download PDF</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-laptop-code text-success" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Practice Tests</h6>
                            <p class="mb-0 text-muted small">Online mock examinations</p>
                            <a href="#" class="btn btn-sm btn-outline-success mt-2">Start Practice</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-video text-info" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Video Tutorials</h6>
                            <p class="mb-0 text-muted small">Expert-led preparation videos</p>
                            <a href="#" class="btn btn-sm btn-outline-info mt-2">Watch Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Important Information --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Important Guidelines</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-danger">What to Bring</h6>
                    <ul>
                        <li>Valid photo ID (passport, driver's license)</li>
                        <li>Admission ticket (printed or digital)</li>
                        <li>2 HB pencils and eraser (for PBT)</li>
                        <li>Approved calculator (for math section)</li>
                        <li>Water bottle (clear, no label)</li>
                    </ul>
                    
                    <h6 class="text-danger mt-3">What NOT to Bring</h6>
                    <ul>
                        <li>Mobile phones or smart devices</li>
                        <li>Books, notes, or study materials</li>
                        <li>Bags or backpacks in test room</li>
                        <li>Food items (except medical necessity)</li>
                        <li>Electronic devices (except approved calculator)</li>
                    </ul>
                    
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-ban me-2"></i>
                        Any violation of exam rules may result in disqualification
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Scoring & Results</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-success">Score Range</h6>
                    <ul>
                        <li>Total Score: 200-800</li>
                        <li>Section Scores: 50-200 each</li>
                        <li>Percentile Ranking provided</li>
                        <li>No negative marking</li>
                    </ul>
                    
                    <h6 class="text-success mt-3">Result Timeline</h6>
                    <ul>
                        <li><strong>CBT:</strong> Immediate preliminary scores</li>
                        <li><strong>PBT:</strong> 7-10 business days</li>
                        <li>Official score report: Within 2 weeks</li>
                        <li>Sent directly to admissions office</li>
                    </ul>
                    
                    <h6 class="text-success mt-3">Score Validity</h6>
                    <p>Entrance exam scores are valid for:</p>
                    <ul>
                        <li>2 years from test date</li>
                        <li>Maximum 3 attempts per year</li>
                        <li>Best score considered for admission</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Special Accommodations --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-universal-access me-2"></i>Special Accommodations</h4>
        </div>
        <div class="card-body">
            <p>We are committed to providing equal access to all candidates. Special accommodations are available for students with documented disabilities.</p>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6 class="text-info">Available Accommodations</h6>
                    <ul>
                        <li>Extended time (50% or 100% additional)</li>
                        <li>Large print or Braille test booklets</li>
                        <li>Screen reader compatibility</li>
                        <li>Separate testing room</li>
                        <li>Additional breaks</li>
                        <li>Sign language interpreter</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-info">How to Request</h6>
                    <ol>
                        <li>Submit accommodation request form</li>
                        <li>Provide disability documentation</li>
                        <li>Submit at least 30 days before exam</li>
                        <li>Receive confirmation within 10 days</li>
                        <li>Contact us for any questions</li>
                    </ol>
                    <a href="#" class="btn btn-info mt-3">
                        <i class="fas fa-download me-2"></i>Download Accommodation Form
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Call to Action --}}
    <div class="bg-light rounded-lg p-5 text-center">
        <h2 class="mb-4">Ready to Take the Next Step?</h2>
        <p class="lead mb-4">Register for your entrance exam today and begin your journey to IntelliCampus University</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="{{ route('exams.portal.available') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-clipboard-check me-2"></i>Register for Exam
            </a>
            <a href="{{ route('exams.sample-papers', 1) }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-file-alt me-2"></i>Sample Papers
            </a>
            <a href="{{ route('admissions.contact') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-headset me-2"></i>Contact Support
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-gradient-purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
    // Smooth scroll to sections
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endpush
@endsection