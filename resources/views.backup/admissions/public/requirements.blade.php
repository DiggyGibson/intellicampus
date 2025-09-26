{{-- resources/views/admissions/public/requirements.blade.php --}}
@extends('layouts.app')

@section('title', 'Admission Requirements - IntelliCampus')

@section('content')
<div class="container-fluid py-4">
    {{-- Hero Section --}}
    <div class="bg-gradient-primary text-white rounded-lg p-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 font-weight-bold mb-3">Admission Requirements</h1>
                <p class="lead mb-4">Everything you need to know about applying to IntelliCampus University</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('admissions.portal.start') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Start Application
                    </a>
                    <a href="{{ route('admissions.programs') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-graduation-cap me-2"></i>View Programs
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-clipboard-check" style="font-size: 150px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-3">
                            <a href="#freshman" class="text-decoration-none">
                                <div class="p-3 border rounded hover-shadow">
                                    <i class="fas fa-user-graduate text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="mb-0">Freshman</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="#transfer" class="text-decoration-none">
                                <div class="p-3 border rounded hover-shadow">
                                    <i class="fas fa-exchange-alt text-success mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="mb-0">Transfer</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="#graduate" class="text-decoration-none">
                                <div class="p-3 border rounded hover-shadow">
                                    <i class="fas fa-user-tie text-info mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="mb-0">Graduate</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="#international" class="text-decoration-none">
                                <div class="p-3 border rounded hover-shadow">
                                    <i class="fas fa-globe text-warning mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="mb-0">International</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Freshman Requirements --}}
    <div id="freshman" class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Freshman Admission Requirements</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="text-primary mb-3">Academic Requirements</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            High school diploma or equivalent
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Minimum GPA of 3.0 on a 4.0 scale
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Completion of college preparatory curriculum:
                            <ul class="mt-2">
                                <li>4 years of English</li>
                                <li>3 years of Mathematics (including Algebra II)</li>
                                <li>3 years of Science (including 2 lab sciences)</li>
                                <li>3 years of Social Studies</li>
                                <li>2 years of Foreign Language</li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h5 class="text-primary mb-3">Required Documents</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Official high school transcript
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            SAT (minimum 1200) or ACT (minimum 25) scores
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Personal statement (500-650 words)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Two letters of recommendation
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Application fee: $75 (non-refundable)
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Transfer Requirements --}}
    <div id="transfer" class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Transfer Student Requirements</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="text-success mb-3">Academic Requirements</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Minimum 24 transferable credit hours
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Minimum cumulative GPA of 2.5
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Good academic standing at previous institution(s)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Completion of English Composition I & II
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            College-level Mathematics course
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h5 class="text-success mb-3">Required Documents</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Official transcripts from all colleges attended
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            High school transcript (if less than 60 credits)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Personal statement explaining transfer reasons
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            One academic recommendation letter
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Course syllabi for credit evaluation
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Graduate Requirements --}}
    <div id="graduate" class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h3 class="mb-0"><i class="fas fa-user-tie me-2"></i>Graduate Admission Requirements</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="text-info mb-3">Academic Requirements</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Bachelor's degree from accredited institution
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Minimum undergraduate GPA of 3.0
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            GRE General Test scores (varies by program)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Prerequisite coursework as required by program
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Relevant work experience (preferred)
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h5 class="text-info mb-3">Required Documents</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Official transcripts from all universities
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Statement of Purpose (750-1000 words)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Three letters of recommendation
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Current resume/CV
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Writing sample (program-specific)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Application fee: $100
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- International Requirements --}}
    <div id="international" class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h3 class="mb-0"><i class="fas fa-globe me-2"></i>International Student Requirements</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="text-warning mb-3">Additional Requirements</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            English Proficiency:
                            <ul class="mt-2">
                                <li>TOEFL: Minimum 80 (iBT)</li>
                                <li>IELTS: Minimum 6.5</li>
                                <li>Duolingo: Minimum 105</li>
                            </ul>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Financial documentation for visa
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Credential evaluation (WES or ECE)
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h5 class="text-warning mb-3">Additional Documents</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Passport copy
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Bank statement or sponsor letter
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Translated & certified transcripts
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            English proficiency test scores
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            International student supplement form
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Important Dates --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Important Dates</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-primary">Fall Semester</h5>
                    <table class="table table-sm">
                        <tr>
                            <td>Early Decision Deadline:</td>
                            <td><strong>November 1</strong></td>
                        </tr>
                        <tr>
                            <td>Regular Decision Deadline:</td>
                            <td><strong>January 15</strong></td>
                        </tr>
                        <tr>
                            <td>Decision Notification:</td>
                            <td><strong>March 31</strong></td>
                        </tr>
                        <tr>
                            <td>Enrollment Deposit Due:</td>
                            <td><strong>May 1</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 class="text-primary">Spring Semester</h5>
                    <table class="table table-sm">
                        <tr>
                            <td>Application Deadline:</td>
                            <td><strong>October 1</strong></td>
                        </tr>
                        <tr>
                            <td>Decision Notification:</td>
                            <td><strong>November 15</strong></td>
                        </tr>
                        <tr>
                            <td>Enrollment Deposit Due:</td>
                            <td><strong>December 1</strong></td>
                        </tr>
                        <tr>
                            <td>Classes Begin:</td>
                            <td><strong>January</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Call to Action --}}
    <div class="text-center py-4">
        <h3 class="mb-4">Ready to Apply?</h3>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="{{ route('admissions.portal.start') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-rocket me-2"></i>Start Your Application
            </a>
            <a href="{{ route('admissions.contact') }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-phone me-2"></i>Contact Admissions
            </a>
            <a href="{{ route('admissions.faq') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-question-circle me-2"></i>View FAQs
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>
@endpush
@endsection