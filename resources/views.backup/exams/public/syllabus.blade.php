{{-- resources/views/exams/public/syllabus.blade.php --}}
@extends('layouts.app')

@section('title', 'Exam Syllabus - ' . ($exam->exam_name ?? 'Entrance Exam'))

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.information') }}">Entrance Exams</a></li>
                    <li class="breadcrumb-item active">Syllabus</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Exam Title Card --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h2 mb-3 text-primary">
                                <i class="fas fa-book me-2"></i>
                                {{ $exam->exam_name ?? 'Entrance Examination' }}
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-2"></i>
                                Exam Date: {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('F d, Y') : 'To be announced' }}
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-clock me-2"></i>
                                Duration: {{ $exam->duration_minutes ?? 180 }} minutes
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="#download-syllabus" class="btn btn-primary">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </a>
                            <a href="{{ route('exams.sample-papers', $exam->id ?? 1) }}" class="btn btn-outline-primary">
                                <i class="fas fa-file-alt me-2"></i>Sample Papers
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column - Syllabus Content --}}
        <div class="col-lg-8">
            {{-- Exam Pattern Section --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">
                        <i class="fas fa-list-alt me-2"></i>Exam Pattern
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Section</th>
                                    <th>Number of Questions</th>
                                    <th>Marks per Question</th>
                                    <th>Total Marks</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($exam->sections) && is_array($exam->sections))
                                    @foreach($exam->sections as $section)
                                    <tr>
                                        <td><strong>{{ $section['name'] ?? 'Section' }}</strong></td>
                                        <td>{{ $section['questions'] ?? '30' }}</td>
                                        <td>{{ $section['marks_per_question'] ?? '1' }}</td>
                                        <td>{{ $section['marks'] ?? '30' }}</td>
                                        <td>{{ $section['duration'] ?? '30' }} min</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td><strong>Mathematics</strong></td>
                                        <td>30</td>
                                        <td>4</td>
                                        <td>120</td>
                                        <td>60 min</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Physics</strong></td>
                                        <td>25</td>
                                        <td>4</td>
                                        <td>100</td>
                                        <td>50 min</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Chemistry</strong></td>
                                        <td>25</td>
                                        <td>4</td>
                                        <td>100</td>
                                        <td>50 min</td>
                                    </tr>
                                    <tr>
                                        <td><strong>English & General Knowledge</strong></td>
                                        <td>20</td>
                                        <td>2</td>
                                        <td>40</td>
                                        <td>20 min</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <th>{{ $exam->total_questions ?? 100 }}</th>
                                    <th>-</th>
                                    <th>{{ $exam->total_marks ?? 360 }}</th>
                                    <th>{{ $exam->duration_minutes ?? 180 }} min</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($exam->negative_marking ?? false)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Negative Marking:</strong> {{ $exam->negative_mark_value ?? 0.25 }} marks will be deducted for each wrong answer.
                    </div>
                    @endif
                </div>
            </div>

            {{-- Detailed Syllabus Section --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">
                        <i class="fas fa-book-open me-2"></i>Detailed Syllabus
                    </h3>
                </div>
                <div class="card-body">
                    {{-- Mathematics Section --}}
                    <div class="syllabus-section mb-4">
                        <h4 class="h5 text-primary mb-3">
                            <i class="fas fa-calculator me-2"></i>Mathematics
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Algebra</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Complex Numbers</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Quadratic Equations</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Sequences and Series</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Permutations and Combinations</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Binomial Theorem</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Matrices and Determinants</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Calculus</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Limits and Continuity</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Differentiation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Applications of Derivatives</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Integration</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Definite Integrals</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Differential Equations</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Physics Section --}}
                    <div class="syllabus-section mb-4">
                        <h4 class="h5 text-primary mb-3">
                            <i class="fas fa-atom me-2"></i>Physics
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Mechanics</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Kinematics</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Laws of Motion</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Work, Energy and Power</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Rotational Motion</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Gravitation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Properties of Matter</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Electricity & Magnetism</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Electrostatics</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Current Electricity</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Magnetic Effects of Current</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Electromagnetic Induction</li>
                                    <li><i class="fas fa-check text-success me-2"></i>AC Circuits</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Electromagnetic Waves</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Chemistry Section --}}
                    <div class="syllabus-section mb-4">
                        <h4 class="h5 text-primary mb-3">
                            <i class="fas fa-flask me-2"></i>Chemistry
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Physical Chemistry</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Atomic Structure</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Chemical Bonding</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Thermodynamics</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Chemical Equilibrium</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Electrochemistry</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Chemical Kinetics</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Organic Chemistry</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Basic Concepts</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Hydrocarbons</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Alcohols and Phenols</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Aldehydes and Ketones</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Carboxylic Acids</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Biomolecules</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- English Section --}}
                    <div class="syllabus-section">
                        <h4 class="h5 text-primary mb-3">
                            <i class="fas fa-language me-2"></i>English & General Knowledge
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">English</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Grammar and Usage</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Vocabulary</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Reading Comprehension</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Verbal Reasoning</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">General Knowledge</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Current Affairs</li>
                                    <li><i class="fas fa-check text-success me-2"></i>General Science</li>
                                    <li><i class="fas fa-check text-success me-2"></i>History & Geography</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Basic Computer Awareness</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Sidebar --}}
        <div class="col-lg-4">
            {{-- Important Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h4 class="h6 mb-0">
                        <i class="fas fa-info-circle me-2"></i>Important Information
                    </h4>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Total Questions:</strong> {{ $exam->total_questions ?? 100 }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Total Marks:</strong> {{ $exam->total_marks ?? 360 }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Duration:</strong> {{ $exam->duration_minutes ?? 180 }} minutes
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Passing Marks:</strong> {{ $exam->passing_marks ?? 120 }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Mode:</strong> {{ ucfirst(str_replace('_', ' ', $exam->delivery_mode ?? 'computer_based')) }}
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Preparation Tips --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="h6 mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Preparation Tips
                    </h4>
                </div>
                <div class="card-body">
                    <ol class="ps-3 mb-0">
                        <li class="mb-2">Understand the exam pattern thoroughly</li>
                        <li class="mb-2">Focus on high-weightage topics first</li>
                        <li class="mb-2">Practice previous year questions</li>
                        <li class="mb-2">Take mock tests regularly</li>
                        <li class="mb-2">Manage time effectively during exam</li>
                        <li class="mb-2">Revise formulas and concepts daily</li>
                    </ol>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h4 class="h6 mb-0">
                        <i class="fas fa-link me-2"></i>Quick Links
                    </h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('exams.sample-papers', $exam->id ?? 1) }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt me-2"></i>Sample Papers
                        </a>
                        <a href="{{ route('exams.portal.register', $exam->id ?? 1) }}" class="btn btn-outline-success">
                            <i class="fas fa-user-plus me-2"></i>Register for Exam
                        </a>
                        <a href="{{ route('exams.information') }}" class="btn btn-outline-info">
                            <i class="fas fa-info me-2"></i>Exam Information
                        </a>
                        <a href="{{ route('admissions.faq') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-question-circle me-2"></i>FAQs
                        </a>
                    </div>
                </div>
            </div>

            {{-- Download Section --}}
            <div class="card shadow-sm" id="download-syllabus">
                <div class="card-header bg-warning">
                    <h4 class="h6 mb-0">
                        <i class="fas fa-download me-2"></i>Downloads
                    </h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-file-pdf me-2"></i>Download Syllabus PDF
                        </button>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-book me-2"></i>Study Material
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .breadcrumb, .btn, .card-header, .col-lg-4 {
            display: none !important;
        }
        .col-lg-8 {
            width: 100% !important;
            max-width: 100% !important;
        }
    }
    
    .syllabus-section h6 {
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .list-unstyled li {
        padding: 0.25rem 0;
    }
</style>
@endpush
@endsection