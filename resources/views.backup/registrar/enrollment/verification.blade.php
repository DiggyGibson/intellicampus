{{-- ============================================================ --}}
{{-- 1. resources/views/registrar/enrollment/verification.blade.php --}}
{{-- ============================================================ --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Enrollment Verification</h1>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Generate Enrollment Verification</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('registrar.enrollment.verification.search') }}" method="GET">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student ID</label>
                                    <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter student ID">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="semester" class="form-label">Semester</label>
                                    <select class="form-control" id="semester" name="semester">
                                        <option value="">Select semester</option>
                                        <option value="current">Current Semester</option>
                                        <option value="fall2024">Fall 2024</option>
                                        <option value="spring2024">Spring 2024</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Search Student</button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Verifications</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">No recent verifications found.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection