// Update resources/views/financial/financial-aid-apply.blade.php

@extends('layouts.app')

@section('title', 'Apply for Financial Aid')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-hands-helping me-2"></i>Apply for Financial Aid
            </h1>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('financial.financial-aid.submit') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Aid Type</label>
                    <select class="form-select" name="type" required>
                        <option value="">Select Aid Type</option>
                        @if(is_array($aidTypes))
                            @foreach($aidTypes as $type)
                                <option value="{{ $type['type'] }}">{{ $type['name'] }}</option>
                            @endforeach
                        @else
                            <option value="grant">Need-Based Grant</option>
                            <option value="scholarship">Merit Scholarship</option>
                            <option value="work_study">Work Study</option>
                            <option value="emergency">Emergency Aid</option>
                        @endif
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Aid Name/Title</label>
                    <input type="text" class="form-control" name="aid_name" required 
                           placeholder="e.g., Academic Excellence Scholarship">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Amount Requested</label>
                    <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Academic Term</label>
                    <select class="form-select" name="term_id" required>
                        <option value="">Select Term</option>
                        <option value="1">Spring 2025</option>
                        <option value="2">Fall 2025</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Reason for Request</label>
                    <textarea class="form-control" name="conditions" rows="4" required 
                              placeholder="Please explain why you need this financial aid..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Application</button>
                <a href="{{ route('financial.financial-aid') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection