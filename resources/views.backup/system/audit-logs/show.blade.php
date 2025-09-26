{{-- ============================================================ --}}
{{-- 3. resources/views/system/audit-logs/show.blade.php --}}
{{-- ============================================================ --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Audit Log Details</h1>
                <a href="{{ route('system.audit-logs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Logs
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">Log details will be displayed here.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection