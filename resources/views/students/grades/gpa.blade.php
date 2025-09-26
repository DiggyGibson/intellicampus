{{-- resources/views/student/grades/gpa.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 font-weight-bold text-gray-800">
                <i class="fas fa-chart-line mr-2"></i>GPA Details
            </h2>
            <p class="text-muted">Comprehensive GPA analysis and breakdown</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('student.grades') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Grades
            </a>
            <a href="{{ route('student.gpa.calculator') }}" class="btn btn-primary">
                <i class="fas fa-calculator mr-1"></i> GPA Calculator
            </a>
        </div>
    </div>

    {{-- GPA Summary Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Cumulative GPA
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($cumulativeGPA, 3) }}
                            </div>
                            <small class="text-muted">Overall Academic Performance</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Major GPA
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($majorGPA, 3) }}
                            </div>
                            <small class="text-muted">In-Major Courses Only</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Credits Earned
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $creditSummary->earned_credits ?? 0 }}
                            </div>
                            <small class="text-muted">of {{ $creditSummary->attempted_credits ?? 0 }} Attempted</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Academic Standing
                            </div>
                            <div class="h6 mb-0 font-weight-bold">
                                @if($cumulativeGPA >= 3.5)
                                    <span class="text-success">Excellent</span>
                                @elseif($cumulativeGPA >= 2.0)
                                    <span class="text-primary">Good Standing</span>
                                @elseif($cumulativeGPA >= 1.5)
                                    <span class="text-warning">Probation</span>
                                @else
                                    <span class="text-danger">Academic Warning</span>
                                @endif
                            </div>
                            @if($cumulativeGPA >= 3.5)
                                <small class="text-success"><i class="fas fa-star"></i> Dean's List Eligible</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-award fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Term-by-Term GPA --}}
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-2"></i>Term-by-Term GPA Analysis
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($termGPAs) && count($termGPAs) > 0)
                        <div class="table-responsive mb-4">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Term</th>
                                        <th>GPA</th>
                                        <th>Credits</th>
                                        <th>Quality Points</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $previousGPA = null; @endphp
                                    @foreach($termGPAs as $index => $termGPA)
                                        <tr>
                                            <td>
                                                <strong>{{ $termGPA['term']->name }}</strong><br>
                                                <small class="text-muted">{{ $termGPA['term']->code }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $termGPA['gpa'] >= 3.5 ? 'success' : ($termGPA['gpa'] >= 2.0 ? 'primary' : 'warning') }} px-3 py-2">
                                                    {{ number_format($termGPA['gpa'], 3) }}
                                                </span>
                                            </td>
                                            <td>{{ $termGPA['term']->credits ?? '--' }}</td>
                                            <td>{{ number_format(($termGPA['gpa'] * ($termGPA['term']->credits ?? 0)), 2) }}</td>
                                            <td>
                                                @if($previousGPA !== null)
                                                    @if($termGPA['gpa'] > $previousGPA)
                                                        <i class="fas fa-arrow-up text-success"></i>
                                                    @elseif($termGPA['gpa'] < $previousGPA)
                                                        <i class="fas fa-arrow-down text-danger"></i>
                                                    @else
                                                        <i class="fas fa-minus text-muted"></i>
                                                    @endif
                                                @else
                                                    --
                                                @endif
                                            </td>
                                        </tr>
                                        @php $previousGPA = $termGPA['gpa']; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- GPA Trend Chart --}}
                        <canvas id="gpaChart" height="100"></canvas>
                    @else
                        <p class="text-muted">No term GPA data available yet.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- GPA Information --}}
        <div class="col-lg-4">
            {{-- Grade Scale Reference --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>Grade Scale
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Points</th>
                                <th>Range</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>A</td><td>4.0</td><td>93-100%</td></tr>
                            <tr><td>A-</td><td>3.7</td><td>90-92%</td></tr>
                            <tr><td>B+</td><td>3.3</td><td>87-89%</td></tr>
                            <tr><td>B</td><td>3.0</td><td>83-86%</td></tr>
                            <tr><td>B-</td><td>2.7</td><td>80-82%</td></tr>
                            <tr><td>C+</td><td>2.3</td><td>77-79%</td></tr>
                            <tr><td>C</td><td>2.0</td><td>73-76%</td></tr>
                            <tr><td>C-</td><td>1.7</td><td>70-72%</td></tr>
                            <tr><td>D+</td><td>1.3</td><td>67-69%</td></tr>
                            <tr><td>D</td><td>1.0</td><td>63-66%</td></tr>
                            <tr><td>F</td><td>0.0</td><td>Below 63%</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Academic Honors --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-medal mr-2"></i>Academic Honors
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Dean's List:</strong>
                        <span class="badge badge-{{ $cumulativeGPA >= 3.5 ? 'success' : 'secondary' }}">
                            {{ $cumulativeGPA >= 3.5 ? 'Eligible' : 'Not Eligible' }}
                        </span>
                        <small class="d-block text-muted mt-1">Requires 3.5+ GPA with 12+ credits</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Graduation Honors:</strong>
                        <ul class="small text-muted mt-1">
                            <li>Summa Cum Laude: 3.90-4.00</li>
                            <li>Magna Cum Laude: 3.70-3.89</li>
                            <li>Cum Laude: 3.50-3.69</li>
                        </ul>
                    </div>

                    @if($cumulativeGPA >= 3.9)
                        <div class="alert alert-success">
                            <i class="fas fa-trophy mr-2"></i>
                            On track for <strong>Summa Cum Laude</strong>
                        </div>
                    @elseif($cumulativeGPA >= 3.7)
                        <div class="alert alert-info">
                            <i class="fas fa-trophy mr-2"></i>
                            On track for <strong>Magna Cum Laude</strong>
                        </div>
                    @elseif($cumulativeGPA >= 3.5)
                        <div class="alert alert-info">
                            <i class="fas fa-trophy mr-2"></i>
                            On track for <strong>Cum Laude</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- GPA Calculator Link --}}
    <div class="card shadow">
        <div class="card-body text-center">
            <h5 class="font-weight-bold text-primary mb-3">Want to Calculate Your Future GPA?</h5>
            <p class="text-muted mb-3">Use our GPA calculator to see how your upcoming grades will affect your GPA.</p>
            <a href="{{ route('student.gpa.calculator') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-calculator mr-2"></i>Open GPA Calculator
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(!empty($termGPAs) && count($termGPAs) > 0)
    const ctx = document.getElementById('gpaChart').getContext('2d');
    const gpaChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_map(function($item) { 
                return $item['term']->name; 
            }, $termGPAs)) !!},
            datasets: [{
                label: 'Term GPA',
                data: {!! json_encode(array_map(function($item) { 
                    return $item['gpa']; 
                }, $termGPAs)) !!},
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1,
                fill: true
            }, {
                label: 'Cumulative GPA',
                data: Array({{ count($termGPAs) }}).fill({{ $cumulativeGPA }}),
                borderColor: 'rgb(255, 99, 132)',
                borderDash: [5, 5],
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 4.0,
                    ticks: {
                        stepSize: 0.5
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
@endif
</script>
@endpush