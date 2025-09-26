{{-- resources/views/student/grades/history.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 font-weight-bold text-gray-800">
                <i class="fas fa-history mr-2"></i>Grade History
            </h2>
            <p class="text-muted">Complete academic record by term</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('student.grades') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Overview
            </a>
            <a href="{{ route('transcripts.index') }}" class="btn btn-primary">
                <i class="fas fa-file-alt mr-1"></i> View Transcript
            </a>
        </div>
    </div>

    @if(empty($gradeHistory) || count($gradeHistory) == 0)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            No grade history available yet.
        </div>
    @else
        {{-- Grade History by Term --}}
        @foreach($gradeHistory as $termData)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-calendar mr-2"></i>
                                {{ $termData['term']->name }} - {{ $termData['term']->code }}
                            </h6>
                            <small>
                                {{ \Carbon\Carbon::parse($termData['term']->start_date)->format('M d, Y') }} - 
                                {{ \Carbon\Carbon::parse($termData['term']->end_date)->format('M d, Y') }}
                            </small>
                        </div>
                        <div class="col-auto">
                            <span class="badge badge-light px-3 py-2">
                                Term GPA: {{ number_format($termData['term_gpa'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Credits</th>
                                    <th>Grade</th>
                                    <th>Grade Points</th>
                                    <th>Quality Points</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($termData['enrollments'] as $enrollment)
                                    <tr>
                                        <td>
                                            <strong>{{ $enrollment->section->course->code }}</strong>
                                            <br>
                                            <small class="text-muted">Section {{ $enrollment->section->section_number }}</small>
                                        </td>
                                        <td>{{ $enrollment->section->course->name }}</td>
                                        <td>{{ $enrollment->section->course->credits }}</td>
                                        <td>
                                            @if($enrollment->grade)
                                                <span class="badge badge-{{ 
                                                    $enrollment->grade == 'A' ? 'success' : 
                                                    ($enrollment->grade == 'F' ? 'danger' : 
                                                    (in_array($enrollment->grade, ['W', 'I']) ? 'warning' : 'primary')) 
                                                }} px-3 py-1">
                                                    {{ $enrollment->grade }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary px-3 py-1">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!in_array($enrollment->grade, ['W', 'I', 'AU', 'P', 'NP']))
                                                {{ number_format($enrollment->grade_points ?? 0, 2) }}
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>
                                            @if(!in_array($enrollment->grade, ['W', 'I', 'AU', 'P', 'NP']))
                                                {{ number_format(($enrollment->grade_points ?? 0) * $enrollment->section->course->credits, 2) }}
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ 
                                                $enrollment->enrollment_status == 'completed' ? 'success' : 
                                                ($enrollment->enrollment_status == 'withdrawn' ? 'warning' : 'info') 
                                            }}">
                                                {{ ucfirst($enrollment->enrollment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="2"><strong>Term Summary</strong></td>
                                    <td><strong>{{ $termData['enrollments']->sum(function($e) { return $e->section->course->credits; }) }}</strong></td>
                                    <td colspan="2">
                                        <strong>Term GPA: {{ number_format($termData['term_gpa'], 2) }}</strong>
                                    </td>
                                    <td colspan="2">
                                        <strong>Credits Earned: 
                                            {{ $termData['enrollments']->filter(function($e) { 
                                                return !in_array($e->grade, ['F', 'W', 'I', null]); 
                                            })->sum(function($e) { 
                                                return $e->section->course->credits; 
                                            }) }}
                                        </strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Cumulative Summary --}}
        <div class="card shadow">
            <div class="card-header py-3 bg-dark text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-chart-line mr-2"></i>Cumulative Academic Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="font-weight-bold text-primary">
                                {{ number_format($student->cumulative_gpa ?? $student->gpa ?? 0, 2) }}
                            </h3>
                            <p class="text-muted">Cumulative GPA</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="font-weight-bold text-success">
                                {{ $student->total_credits_earned ?? 0 }}
                            </h3>
                            <p class="text-muted">Credits Earned</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="font-weight-bold text-info">
                                {{ $student->total_credits_attempted ?? 0 }}
                            </h3>
                            <p class="text-muted">Credits Attempted</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="font-weight-bold text-warning">
                                {{ count($gradeHistory) }}
                            </h3>
                            <p class="text-muted">Terms Completed</p>
                        </div>
                    </div>
                </div>

                {{-- GPA Trend Chart --}}
                @if(count($gradeHistory) > 1)
                    <div class="mt-4">
                        <h6 class="font-weight-bold text-primary mb-3">GPA Trend</h6>
                        <canvas id="gpaTrendChart" height="100"></canvas>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(count($gradeHistory) > 1)
    const ctx = document.getElementById('gpaTrendChart').getContext('2d');
    const gpaTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_map(function($item) { 
                return $item['term']->name; 
            }, $gradeHistory)) !!},
            datasets: [{
                label: 'Term GPA',
                data: {!! json_encode(array_map(function($item) { 
                    return $item['term_gpa']; 
                }, $gradeHistory)) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 4.0
                }
            }
        }
    });
@endif
</script>
@endpush