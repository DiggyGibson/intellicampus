{{-- resources/views/admissions/public/calendar.blade.php --}}
@extends('layouts.guest')

@section('title', 'Academic Calendar - IntelliCampus')

@section('content')
<div class="container-fluid py-4">
    {{-- Hero Section --}}
    <div class="bg-gradient-success text-white rounded-lg p-5 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 font-weight-bold mb-3">Academic Calendar</h1>
                <p class="lead mb-4">Important dates and deadlines for prospective and current students</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('admissions.portal.start') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Apply Now
                    </a>
                    <a href="#" onclick="window.print()" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-print me-2"></i>Print Calendar
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-calendar-alt" style="font-size: 150px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-2">
                    <a href="#admissions" class="btn btn-outline-primary w-100">
                        <i class="fas fa-user-plus me-2"></i>Admissions
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <a href="#registration" class="btn btn-outline-success w-100">
                        <i class="fas fa-clipboard-list me-2"></i>Registration
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <a href="#academic" class="btn btn-outline-info w-100">
                        <i class="fas fa-book me-2"></i>Academic
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <a href="#exams" class="btn btn-outline-warning w-100">
                        <i class="fas fa-pencil-alt me-2"></i>Exams
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Current Academic Year --}}
    <div class="text-center mb-4">
        <h2 class="text-primary">Academic Year 2024-2025</h2>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary active">2024-2025</button>
            <button type="button" class="btn btn-outline-secondary">2025-2026</button>
        </div>
    </div>

    <div class="row">
        {{-- Fall Semester 2024 --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-leaf me-2"></i>Fall Semester 2024</h4>
                </div>
                <div class="card-body">
                    {{-- Admissions Dates --}}
                    <h6 class="text-primary border-bottom pb-2" id="admissions">
                        <i class="fas fa-user-plus me-2"></i>Admissions Deadlines
                    </h6>
                    <table class="table table-sm table-hover mb-4">
                        <tbody>
                            <tr>
                                <td>Early Decision Application Deadline</td>
                                <td class="text-end"><strong>Nov 1, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Early Decision Notification</td>
                                <td class="text-end"><strong>Dec 15, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Regular Decision Application Deadline</td>
                                <td class="text-end"><strong>Jan 15, 2025</strong></td>
                            </tr>
                            <tr>
                                <td>Regular Decision Notification</td>
                                <td class="text-end"><strong>Mar 31, 2025</strong></td>
                            </tr>
                            <tr>
                                <td>Enrollment Deposit Deadline</td>
                                <td class="text-end"><strong>May 1, 2025</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Registration Dates --}}
                    <h6 class="text-success border-bottom pb-2" id="registration">
                        <i class="fas fa-clipboard-list me-2"></i>Registration Dates
                    </h6>
                    <table class="table table-sm table-hover mb-4">
                        <tbody>
                            <tr>
                                <td>Registration Opens (Seniors)</td>
                                <td class="text-end"><strong>Apr 15, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Registration Opens (Juniors)</td>
                                <td class="text-end"><strong>Apr 18, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Registration Opens (Sophomores)</td>
                                <td class="text-end"><strong>Apr 22, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Registration Opens (Freshmen)</td>
                                <td class="text-end"><strong>Apr 25, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Late Registration Period</td>
                                <td class="text-end"><strong>Aug 26-30, 2024</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Academic Dates --}}
                    <h6 class="text-info border-bottom pb-2" id="academic">
                        <i class="fas fa-book me-2"></i>Academic Dates
                    </h6>
                    <table class="table table-sm table-hover mb-4">
                        <tbody>
                            <tr class="table-warning">
                                <td><strong>Classes Begin</strong></td>
                                <td class="text-end"><strong>Aug 26, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Add/Drop Period Ends</td>
                                <td class="text-end"><strong>Sep 2, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Labor Day (No Classes)</td>
                                <td class="text-end"><strong>Sep 2, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Withdrawal Deadline</td>
                                <td class="text-end"><strong>Oct 15, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Fall Break</td>
                                <td class="text-end"><strong>Oct 14-15, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Thanksgiving Break</td>
                                <td class="text-end"><strong>Nov 27-29, 2024</strong></td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Last Day of Classes</strong></td>
                                <td class="text-end"><strong>Dec 6, 2024</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Exam Dates --}}
                    <h6 class="text-warning border-bottom pb-2" id="exams">
                        <i class="fas fa-pencil-alt me-2"></i>Examination Period
                    </h6>
                    <table class="table table-sm table-hover">
                        <tbody>
                            <tr>
                                <td>Reading Day</td>
                                <td class="text-end"><strong>Dec 7, 2024</strong></td>
                            </tr>
                            <tr class="table-danger">
                                <td><strong>Final Exams</strong></td>
                                <td class="text-end"><strong>Dec 9-13, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Grades Due</td>
                                <td class="text-end"><strong>Dec 17, 2024</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Spring Semester 2025 --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-seedling me-2"></i>Spring Semester 2025</h4>
                </div>
                <div class="card-body">
                    {{-- Admissions Dates --}}
                    <h6 class="text-primary border-bottom pb-2">
                        <i class="fas fa-user-plus me-2"></i>Admissions Deadlines
                    </h6>
                    <table class="table table-sm table-hover mb-4">
                        <tbody>
                            <tr>
                                <td>Spring Application Deadline</td>
                                <td class="text-end"><strong>Oct 1, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Spring Admission Notification</td>
                                <td class="text-end"><strong>Nov 15, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Enrollment Deposit Deadline</td>
                                <td class="text-end"><strong>Dec 1, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Transfer Application Deadline</td>
                                <td class="text-end"><strong>Nov 1, 2024</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Registration Dates --}}
                    <h6 class="text-success border-bottom pb-2">
                        <i class="fas fa-clipboard-list me-2"></i>Registration Dates
                    </h6>
                    <table class="table table-sm table-hover mb-4">
                        <tbody>
                            <tr>
                                <td>Registration Opens (Continuing)</td>
                                <td class="text-end"><strong>Nov 1, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Registration Opens (New Students)</td>
                                <td class="text-end"><strong>Dec 1, 2024</strong></td>
                            </tr>
                            <tr>
                                <td>Late Registration Period</td>
                                <td class="text-end"><strong>Jan 13-17, 2025</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Academic Dates --}}
                    <h6 class="text-info border-bottom pb-2">
                        <i class="fas fa-book me-2"></i>Academic Dates
                    </h6>
                    <table class="table table-sm table-hover mb-4">
                        <tbody>
                            <tr class="table-warning">
                                <td><strong>Classes Begin</strong></td>
                                <td class="text-end"><strong>Jan 13, 2025</strong></td>
                            </tr>
                            <tr>
                                <td>MLK Day (No Classes)</td>
                                <td class="text-end"><strong>Jan 20, 2025</strong></td>
                            </tr>
                            <tr>
                                <td>Add/Drop Period Ends</td>
                                <td class="text-end"><strong>Jan 21, 2025</strong></td>
                            </tr>
                            <tr>
                                <td>Presidents Day (No Classes)</td>
                                <td class="text-end"><strong>Feb 17, 2025</strong></td>
                            </tr>
                            <tr>
                                <td>Spring Break</td>
                                <td class="text-end"><strong>Mar 10-14, 2025</strong></td>
                            </tr>
                            <tr>
                                <td>Withdrawal Deadline</td>
                                <td class="text-end"><strong>Mar 28, 2025</strong></td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Last Day of Classes</strong></td>
                                <td class="text-end"><strong>May 2, 2025</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Exam Dates --}}
                    <h6 class="text-warning border-bottom pb-2">
                        <i class="fas fa-pencil-alt me-2"></i>Examination Period
                    </h6>
                    <table class="table table-sm table-hover mb-3">
                        <tbody>
                            <tr>
                                <td>Reading Day</td>
                                <td class="text-end"><strong>May 3, 2025</strong></td>
                            </tr>
                            <tr class="table-danger">
                                <td><strong>Final Exams</strong></td>
                                <td class="text-end"><strong>May 5-9, 2025</strong></td>
                            </tr>
                            <tr>
                                <td>Grades Due</td>
                                <td class="text-end"><strong>May 13, 2025</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Commencement --}}
                    <h6 class="text-danger border-bottom pb-2">
                        <i class="fas fa-graduation-cap me-2"></i>Commencement
                    </h6>
                    <table class="table table-sm table-hover">
                        <tbody>
                            <tr class="table-success">
                                <td><strong>Commencement Ceremony</strong></td>
                                <td class="text-end"><strong>May 17, 2025</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Summer Session --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="fas fa-sun me-2"></i>Summer Sessions 2025</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-primary">Summer Session I</h6>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Registration Opens</td>
                                <td class="text-end"><strong>Mar 1</strong></td>
                            </tr>
                            <tr>
                                <td>Classes Begin</td>
                                <td class="text-end"><strong>May 19</strong></td>
                            </tr>
                            <tr>
                                <td>Classes End</td>
                                <td class="text-end"><strong>Jun 27</strong></td>
                            </tr>
                            <tr>
                                <td>Final Exams</td>
                                <td class="text-end"><strong>Jun 30</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-4">
                    <h6 class="text-success">Summer Session II</h6>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Registration Opens</td>
                                <td class="text-end"><strong>Apr 1</strong></td>
                            </tr>
                            <tr>
                                <td>Classes Begin</td>
                                <td class="text-end"><strong>Jul 7</strong></td>
                            </tr>
                            <tr>
                                <td>Classes End</td>
                                <td class="text-end"><strong>Aug 15</strong></td>
                            </tr>
                            <tr>
                                <td>Final Exams</td>
                                <td class="text-end"><strong>Aug 18</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-4">
                    <h6 class="text-info">Full Summer Session</h6>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Registration Opens</td>
                                <td class="text-end"><strong>Mar 1</strong></td>
                            </tr>
                            <tr>
                                <td>Classes Begin</td>
                                <td class="text-end"><strong>May 19</strong></td>
                            </tr>
                            <tr>
                                <td>Classes End</td>
                                <td class="text-end"><strong>Aug 15</strong></td>
                            </tr>
                            <tr>
                                <td>Final Exams</td>
                                <td class="text-end"><strong>Aug 18-19</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Important Notes --}}
    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle me-2"></i>Important Notes</h5>
        <ul class="mb-0">
            <li>Dates are subject to change. Please check the official university website for the most current information.</li>
            <li>Financial aid deadlines may differ from admission deadlines. Check with the Financial Aid Office.</li>
            <li>International students should apply at least 6 months before the intended enrollment date.</li>
            <li>Some programs may have earlier deadlines. Check with specific departments.</li>
        </ul>
    </div>

    {{-- Download Options --}}
    <div class="text-center py-4">
        <h4 class="mb-4">Download Calendar</h4>
        <div class="d-flex justify-content-center gap-3">
            <button class="btn btn-outline-primary" onclick="downloadCalendar('pdf')">
                <i class="fas fa-file-pdf me-2"></i>Download PDF
            </button>
            <button class="btn btn-outline-success" onclick="downloadCalendar('ics')">
                <i class="fas fa-calendar-plus me-2"></i>Add to Calendar
            </button>
            <button class="btn btn-outline-info" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Calendar
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-gradient-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    @media print {
        .btn, .btn-group, .hero-section {
            display: none !important;
        }
        
        .card {
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function downloadCalendar(format) {
        alert(`Calendar download in ${format} format will be implemented`);
    }
</script>
@endpush
@endsection