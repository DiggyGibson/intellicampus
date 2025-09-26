{{-- resources/views/student/grades/current.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="h3 font-weight-bold text-gray-800">
                <i class="fas fa-calendar-alt mr-2"></i>Current Term Grades
            </h2>
            @if($currentTerm)
                <p class="text-muted">{{ $currentTerm->name }} - {{ $currentTerm->code }}</p>
            @endif
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('student.grades') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Overview
            </a>
            <a href="{{ route('student.grades.history') }}" class="btn btn-info">
                <i class="fas fa-history mr-1"></i> View History
            </a>
        </div>
    </div>

    @if(!$currentTerm)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            No active academic term found. Please check with the registrar's office.
        </div>
    @elseif($enrollments->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            You are not enrolled in any courses for this term.
        </div>
    @else
        {{-- Current Courses Grid --}}
        <div class="row">
            @foreach($enrollments as $enrollment)
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3 bg-gradient-primary text-white">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 font-weight-bold">
                                        {{ $enrollment->section->course->code }} - Section {{ $enrollment->section->section_number }}
                                    </h6>
                                    <small>{{ $enrollment->section->course->name }}</small>
                                </div>
                                <div class="col-auto">
                                    <span class="badge badge-light px-3 py-2">
                                        {{ $enrollment->section->course->credits }} Credits
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Instructor Info --}}
                            <div class="mb-3">
                                <strong>Instructor:</strong> {{ $enrollment->section->instructor->name ?? 'TBA' }}<br>
                                <strong>Schedule:</strong> 
                                @if($enrollment->section->days_of_week)
                                    {{ $enrollment->section->days_of_week }} 
                                    {{ \Carbon\Carbon::parse($enrollment->section->start_time)->format('g:i A') }} - 
                                    {{ \Carbon\Carbon::parse($enrollment->section->end_time)->format('g:i A') }}
                                @else
                                    Online/Async
                                @endif
                            </div>

                            {{-- Grade Summary --}}
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-primary mb-2">Current Grade</h6>
                                @if(isset($enrollment->calculated_grade))
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-center">
                                                <div class="h1 mb-0 font-weight-bold 
                                                    @if($enrollment->calculated_grade['letter_grade'] == 'A') text-success
                                                    @elseif($enrollment->calculated_grade['letter_grade'] == 'F') text-danger
                                                    @else text-primary
                                                    @endif">
                                                    {{ $enrollment->calculated_grade['letter_grade'] ?? '--' }}
                                                </div>
                                                <small class="text-muted">Letter Grade</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <div class="h1 mb-0 font-weight-bold text-info">
                                                    {{ number_format($enrollment->calculated_grade['percentage'] ?? 0, 1) }}%
                                                </div>
                                                <small class="text-muted">Percentage</small>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-muted">No grades posted yet</p>
                                @endif
                            </div>

                            {{-- Grade Components --}}
                            @if(isset($enrollment->calculated_grade['components']) && count($enrollment->calculated_grade['components']) > 0)
                                <h6 class="font-weight-bold text-primary mb-2">Grade Breakdown</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Component</th>
                                                <th>Weight</th>
                                                <th>Score</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($enrollment->calculated_grade['components'] as $component)
                                                <tr>
                                                    <td>{{ $component['component'] }}</td>
                                                    <td>{{ $component['weight'] }}%</td>
                                                    <td>
                                                        @if($component['points_earned'] !== null)
                                                            {{ $component['points_earned'] }}/{{ $component['max_points'] }}
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($component['percentage'] > 0)
                                                            {{ number_format($component['percentage'], 1) }}%
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- Enrollment Status --}}
                            <div class="mt-3">
                                <span class="badge badge-{{ $enrollment->enrollment_status == 'enrolled' ? 'success' : 'secondary' }} px-3 py-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ ucfirst($enrollment->enrollment_status) }}
                                </span>
                                @if($enrollment->updated_at)
                                    <small class="text-muted ml-2">
                                        Last updated: {{ $enrollment->updated_at->diffForHumans() }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Term Summary --}}
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar mr-2"></i>Term Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="font-weight-bold text-primary">{{ $enrollments->count() }}</h4>
                            <p class="text-muted">Courses Enrolled</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="font-weight-bold text-info">
                                {{ $enrollments->sum(function($e) { return $e->section->course->credits; }) }}
                            </h4>
                            <p class="text-muted">Total Credits</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="font-weight-bold text-success">
                                {{ $enrollments->where('grade', '!=', null)->count() }}
                            </h4>
                            <p class="text-muted">Grades Posted</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="font-weight-bold text-warning">
                                {{ $enrollments->where('grade', null)->count() }}
                            </h4>
                            <p class="text-muted">In Progress</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection