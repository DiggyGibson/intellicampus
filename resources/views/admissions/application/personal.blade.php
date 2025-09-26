@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Personal Information</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admissions.portal.application.personal.save', $application->application_uuid) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" value="{{ $application->first_name }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="{{ $application->last_name }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ $application->email }}" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save and Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection