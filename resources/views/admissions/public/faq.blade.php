{{-- resources/views/admissions/public/faq.blade.php --}}
@extends('layouts.guest')

@section('title', 'Frequently Asked Questions - IntelliCampus')

@section('content')
<div class="container-fluid py-4">
    {{-- Hero Section --}}
    <div class="bg-gradient-warning text-dark rounded-lg p-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 font-weight-bold mb-3">Frequently Asked Questions</h1>
                <p class="lead mb-4">Find answers to common questions about admissions, enrollment, and student life</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('admissions.contact') }}" class="btn btn-dark btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="{{ route('admissions.portal.start') }}" class="btn btn-outline-dark btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Start Application
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-question-circle" style="font-size: 150px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-primary text-white">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" id="faqSearch" placeholder="Search for answers...">
                <button class="btn btn-primary" type="button" onclick="searchFAQ()">Search</button>
            </div>
        </div>
    </div>

    {{-- FAQ Categories --}}
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <a href="#admissions" class="text-decoration-none">
                <div class="card text-center border-primary hover-shadow">
                    <div class="card-body">
                        <i class="fas fa-user-graduate text-primary mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Admissions</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <a href="#financial" class="text-decoration-none">
                <div class="card text-center border-success hover-shadow">
                    <div class="card-body">
                        <i class="fas fa-dollar-sign text-success mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Financial Aid</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <a href="#academic" class="text-decoration-none">
                <div class="card text-center border-info hover-shadow">
                    <div class="card-body">
                        <i class="fas fa-book text-info mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Academic</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <a href="#campus" class="text-decoration-none">
                <div class="card text-center border-warning hover-shadow">
                    <div class="card-body">
                        <i class="fas fa-university text-warning mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Campus Life</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Admissions FAQs --}}
    <div class="card shadow-sm mb-4" id="admissions">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Admissions Questions</h4>
        </div>
        <div class="card-body">
            <div class="accordion" id="admissionsAccordion">
                {{-- Question 1 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#admQ1">
                            What are the application deadlines?
                        </button>
                    </h2>
                    <div id="admQ1" class="accordion-collapse collapse show" data-bs-parent="#admissionsAccordion">
                        <div class="accordion-body">
                            <p><strong>For Fall Semester:</strong></p>
                            <ul>
                                <li>Early Decision: November 1</li>
                                <li>Regular Decision: January 15</li>
                                <li>Transfer Students: March 1</li>
                            </ul>
                            <p><strong>For Spring Semester:</strong></p>
                            <ul>
                                <li>All Applications: October 1</li>
                            </ul>
                            <p class="mb-0 text-muted">International students should apply at least 6 months before the intended start date.</p>
                        </div>
                    </div>
                </div>

                {{-- Question 2 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admQ2">
                            What standardized tests are required?
                        </button>
                    </h2>
                    <div id="admQ2" class="accordion-collapse collapse" data-bs-parent="#admissionsAccordion">
                        <div class="accordion-body">
                            <p><strong>Undergraduate Admissions:</strong></p>
                            <ul>
                                <li>SAT (minimum 1200) or ACT (minimum 25)</li>
                                <li>Test-optional for students with GPA above 3.5</li>
                            </ul>
                            <p><strong>Graduate Admissions:</strong></p>
                            <ul>
                                <li>GRE required for most programs</li>
                                <li>GMAT for MBA programs</li>
                            </ul>
                            <p><strong>International Students:</strong></p>
                            <ul>
                                <li>TOEFL (minimum 80) or IELTS (minimum 6.5)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Question 3 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admQ3">
                            How do I track my application status?
                        </button>
                    </h2>
                    <div id="admQ3" class="accordion-collapse collapse" data-bs-parent="#admissionsAccordion">
                        <div class="accordion-body">
                            <p>You can track your application status through multiple channels:</p>
                            <ol>
                                <li><strong>Online Portal:</strong> Log in to your application portal using your email and password</li>
                                <li><strong>Email Updates:</strong> We send automatic updates when your status changes</li>
                                <li><strong>SMS Notifications:</strong> If you opted in, you'll receive text updates</li>
                                <li><strong>Call Us:</strong> Contact admissions office at (555) 123-4567</li>
                            </ol>
                            <p class="mb-0"><a href="{{ route('admissions.portal.index') }}" class="btn btn-sm btn-primary">Check Status Now</a></p>
                        </div>
                    </div>
                </div>

                {{-- Question 4 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admQ4">
                            Can I defer my admission?
                        </button>
                    </h2>
                    <div id="admQ4" class="accordion-collapse collapse" data-bs-parent="#admissionsAccordion">
                        <div class="accordion-body">
                            <p>Yes, admitted students may request to defer their enrollment for up to one year. To defer:</p>
                            <ol>
                                <li>Submit a written deferral request before May 1</li>
                                <li>Pay the enrollment deposit to hold your place</li>
                                <li>Provide a reason for deferral (gap year, military service, etc.)</li>
                                <li>Receive written confirmation of your deferral</li>
                            </ol>
                            <p class="mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Note: Scholarships may not be deferred and will need to be re-evaluated.</p>
                        </div>
                    </div>
                </div>

                {{-- Question 5 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admQ5">
                            What documents are required for international students?
                        </button>
                    </h2>
                    <div id="admQ5" class="accordion-collapse collapse" data-bs-parent="#admissionsAccordion">
                        <div class="accordion-body">
                            <p>International students must submit:</p>
                            <ul>
                                <li>All standard application materials</li>
                                <li>Official transcripts translated to English</li>
                                <li>Credential evaluation from WES or ECE</li>
                                <li>English proficiency test scores (TOEFL/IELTS)</li>
                                <li>Passport copy</li>
                                <li>Financial documentation showing ability to pay</li>
                                <li>Sponsor letter (if applicable)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Aid FAQs --}}
    <div class="card shadow-sm mb-4" id="financial">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Financial Aid Questions</h4>
        </div>
        <div class="card-body">
            <div class="accordion" id="financialAccordion">
                {{-- Question 1 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#finQ1">
                            What types of financial aid are available?
                        </button>
                    </h2>
                    <div id="finQ1" class="accordion-collapse collapse" data-bs-parent="#financialAccordion">
                        <div class="accordion-body">
                            <p>We offer various types of financial aid:</p>
                            <ul>
                                <li><strong>Merit Scholarships:</strong> Based on academic achievement ($2,000 - $20,000/year)</li>
                                <li><strong>Need-Based Grants:</strong> Based on financial need</li>
                                <li><strong>Work-Study Programs:</strong> Part-time campus employment</li>
                                <li><strong>Federal Loans:</strong> Subsidized and unsubsidized loans</li>
                                <li><strong>Athletic Scholarships:</strong> For student-athletes</li>
                                <li><strong>Departmental Awards:</strong> Specific to your major</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Question 2 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#finQ2">
                            When should I apply for financial aid?
                        </button>
                    </h2>
                    <div id="finQ2" class="accordion-collapse collapse" data-bs-parent="#financialAccordion">
                        <div class="accordion-body">
                            <p><strong>Important Deadlines:</strong></p>
                            <ul>
                                <li>FAFSA Opens: October 1</li>
                                <li>Priority Deadline: February 15</li>
                                <li>Final Deadline: May 1</li>
                            </ul>
                            <p>Apply as early as possible for maximum aid consideration. Late applications receive limited funding.</p>
                        </div>
                    </div>
                </div>

                {{-- Question 3 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#finQ3">
                            Are payment plans available?
                        </button>
                    </h2>
                    <div id="finQ3" class="accordion-collapse collapse" data-bs-parent="#financialAccordion">
                        <div class="accordion-body">
                            <p>Yes! We offer several payment plan options:</p>
                            <ul>
                                <li><strong>4-Month Plan:</strong> Split semester costs into 4 monthly payments</li>
                                <li><strong>10-Month Plan:</strong> Spread annual costs over 10 months</li>
                                <li><strong>Deferred Payment:</strong> Pay 60% upfront, 40% mid-semester</li>
                            </ul>
                            <p>All payment plans have a one-time $50 enrollment fee.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Academic FAQs --}}
    <div class="card shadow-sm mb-4" id="academic">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-book me-2"></i>Academic Questions</h4>
        </div>
        <div class="card-body">
            <div class="accordion" id="academicAccordion">
                {{-- Question 1 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acaQ1">
                            Can I change my major after enrollment?
                        </button>
                    </h2>
                    <div id="acaQ1" class="accordion-collapse collapse" data-bs-parent="#academicAccordion">
                        <div class="accordion-body">
                            <p>Yes, you can change your major. The process involves:</p>
                            <ol>
                                <li>Meeting with your academic advisor</li>
                                <li>Completing a change of major form</li>
                                <li>Meeting requirements for the new major</li>
                                <li>Getting approval from the new department</li>
                            </ol>
                            <p>Most students change majors at least once. We recommend deciding by sophomore year to graduate on time.</p>
                        </div>
                    </div>
                </div>

                {{-- Question 2 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acaQ2">
                            What is the average class size?
                        </button>
                    </h2>
                    <div id="acaQ2" class="accordion-collapse collapse" data-bs-parent="#academicAccordion">
                        <div class="accordion-body">
                            <p>Class sizes vary by level and type:</p>
                            <ul>
                                <li><strong>Introductory Courses:</strong> 50-150 students</li>
                                <li><strong>Upper-Level Courses:</strong> 20-35 students</li>
                                <li><strong>Seminars:</strong> 10-15 students</li>
                                <li><strong>Labs:</strong> 15-20 students</li>
                            </ul>
                            <p>Our overall student-to-faculty ratio is 15:1.</p>
                        </div>
                    </div>
                </div>

                {{-- Question 3 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acaQ3">
                            Are online courses available?
                        </button>
                    </h2>
                    <div id="acaQ3" class="accordion-collapse collapse" data-bs-parent="#academicAccordion">
                        <div class="accordion-body">
                            <p>Yes, we offer various online learning options:</p>
                            <ul>
                                <li><strong>Fully Online Programs:</strong> Select degree programs</li>
                                <li><strong>Hybrid Courses:</strong> Mix of online and in-person</li>
                                <li><strong>Online Summer Courses:</strong> Accelerated format</li>
                                <li><strong>MOOCs:</strong> Free online courses for enrichment</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Campus Life FAQs --}}
    <div class="card shadow-sm mb-4" id="campus">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="fas fa-university me-2"></i>Campus Life Questions</h4>
        </div>
        <div class="card-body">
            <div class="accordion" id="campusAccordion">
                {{-- Question 1 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#camQ1">
                            Is on-campus housing guaranteed?
                        </button>
                    </h2>
                    <div id="camQ1" class="accordion-collapse collapse" data-bs-parent="#campusAccordion">
                        <div class="accordion-body">
                            <p>Housing guarantees depend on your status:</p>
                            <ul>
                                <li><strong>Freshmen:</strong> Guaranteed housing (required to live on campus)</li>
                                <li><strong>Sophomores:</strong> Guaranteed if requested by deadline</li>
                                <li><strong>Juniors/Seniors:</strong> Based on availability</li>
                                <li><strong>Transfer Students:</strong> Priority consideration</li>
                                <li><strong>Graduate Students:</strong> Limited on-campus options</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Question 2 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#camQ2">
                            What meal plans are available?
                        </button>
                    </h2>
                    <div id="camQ2" class="accordion-collapse collapse" data-bs-parent="#campusAccordion">
                        <div class="accordion-body">
                            <p>We offer flexible meal plans:</p>
                            <ul>
                                <li><strong>Unlimited:</strong> Unlimited dining hall access + $200 flex dollars</li>
                                <li><strong>14 Meals/Week:</strong> 14 meals + $300 flex dollars</li>
                                <li><strong>10 Meals/Week:</strong> 10 meals + $400 flex dollars</li>
                                <li><strong>Commuter Plan:</strong> 50 meals/semester + $100 flex</li>
                            </ul>
                            <p>All residential students must have a meal plan.</p>
                        </div>
                    </div>
                </div>

                {{-- Question 3 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#camQ3">
                            What student organizations are available?
                        </button>
                    </h2>
                    <div id="camQ3" class="accordion-collapse collapse" data-bs-parent="#campusAccordion">
                        <div class="accordion-body">
                            <p>We have over 200 student organizations including:</p>
                            <ul>
                                <li>Academic and Professional Clubs</li>
                                <li>Cultural and International Organizations</li>
                                <li>Greek Life (20+ fraternities and sororities)</li>
                                <li>Sports and Recreation Clubs</li>
                                <li>Community Service Groups</li>
                                <li>Student Government</li>
                                <li>Special Interest Groups</li>
                            </ul>
                            <p>Can't find what you're looking for? Start your own club!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Still Have Questions? --}}
    <div class="bg-light rounded-lg p-5 text-center">
        <h3 class="mb-4">Still Have Questions?</h3>
        <p class="lead mb-4">Our admissions team is here to help you every step of the way.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="{{ route('admissions.contact') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-envelope me-2"></i>Contact Admissions
            </a>
            <a href="tel:555-123-4567" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-phone me-2"></i>Call (555) 123-4567
            </a>
            <button class="btn btn-outline-success btn-lg" onclick="startLiveChat()">
                <i class="fas fa-comments me-2"></i>Live Chat
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15)!important;
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white !important;
    }
    
    .accordion-button:focus {
        box-shadow: none;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
    // Search functionality
    document.getElementById('faqSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const accordionItems = document.querySelectorAll('.accordion-item');
        
        accordionItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? 'block' : 'none';
        });
    });
    
    function searchFAQ() {
        const searchTerm = document.getElementById('faqSearch').value;
        if (searchTerm) {
            // Highlight search results
            console.log('Searching for:', searchTerm);
        }
    }
    
    function startLiveChat() {
        alert('Live chat feature will be implemented soon!');
    }
</script>
@endpush
@endsection