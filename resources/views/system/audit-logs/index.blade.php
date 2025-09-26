{{-- ============================================================ --}}
{{-- 2. resources/views/system/audit-logs/index.blade.php --}}
{{-- ============================================================ --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>System Audit Logs</h1>
                <div>
                    <a href="{{ route('system.audit-logs.export') }}" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Export Logs
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <form action="{{ route('system.audit-logs.search') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="user" placeholder="User">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="action">
                                <option value="">All Actions</option>
                                <option value="create">Create</option>
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                                <option value="login">Login</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" name="date">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No audit logs available
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection