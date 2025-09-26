@extends('layouts.app')

@section('title', 'Transcript Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-alt me-2"></i>Transcript Management
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Transcript Management</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="student-select" class="form-label">Select Student for Transcript</label>
                            <select id="student-select" class="form-select" onchange="if(this.value) window.location.href='{{ url('transcripts/view') }}/' + this.value">
                                <option value="">-- Select a Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->student_id }} - {{ $student->user->name ?? 'Unknown' }}
                                        ({{ ucfirst($student->enrollment_status) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Administrative Actions</label>
                            <a href="{{ route('transcripts.request') }}" class="btn btn-success me-2">
                                <i class="fas fa-plus-circle me-1"></i>Create Request
                            </a>
                            <a href="{{ route('transcripts.requests.pending') }}" class="btn btn-warning me-2">
                                <i class="fas fa-clock me-1"></i>Pending Requests
                            </a>
                            <a href="{{ route('transcripts.logs') }}" class="btn btn-info">
                                <i class="fas fa-history me-1"></i>View Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Requests
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $pendingRequests->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Processing Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $recentRequests->where('status', 'processing')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed This Week
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $recentRequests->where('status', 'completed')
                                    ->where('completed_at', '>=', now()->subWeek())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Rush Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $pendingRequests->where('rush_order', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Table -->
    @if($pendingRequests->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Transcript Requests</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Delivery</th>
                                    <th>Recipient</th>
                                    <th>Rush</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $request)
                                <tr>
                                    <td>{{ $request->request_number }}</td>
                                    <td>
                                        {{ $request->student->student_id }}<br>
                                        <small class="text-muted">{{ $request->student->user->name ?? 'Unknown' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->type == 'official' ? 'primary' : 'secondary' }}">
                                            {{ ucfirst($request->type) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($request->delivery_method) }}</td>
                                    <td>{{ $request->recipient_name }}</td>
                                    <td>
                                        @if($request->rush_order)
                                            <span class="badge bg-danger">RUSH</span>
                                        @else
                                            <span class="badge bg-light text-dark">Normal</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->created_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('transcripts.request.status', $request) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('transcripts.process', $request) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Process">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('transcripts.generate-pdf', $request->student_id) }}?type={{ $request->type }}" 
                                               class="btn btn-sm btn-outline-primary" title="Generate PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Recent Transcript Requests</h6>
                </div>
                <div class="card-body">
                    @if($recentRequests->isEmpty())
                        <p class="text-muted text-center my-4">No transcript requests found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request #</th>
                                        <th>Student</th>
                                        <th>Type</th>
                                        <th>Delivery</th>
                                        <th>Recipient</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequests as $request)
                                    <tr>
                                        <td>{{ $request->request_number }}</td>
                                        <td>
                                            {{ $request->student->student_id }}<br>
                                            <small class="text-muted">{{ $request->student->user->name ?? 'Unknown' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $request->type == 'official' ? 'primary' : 'secondary' }}">
                                                {{ ucfirst($request->type) }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst($request->delivery_method) }}</td>
                                        <td>{{ $request->recipient_name }}</td>
                                        <td>
                                            @switch($request->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-info">Processing</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Completed</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $request->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('transcripts.request.status', $request) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('student-search');
        const searchBtn = document.getElementById('search-btn');
        const searchResults = document.getElementById('search-results');
        let searchTimeout;
        
        // Function to perform search
        function performSearch() {
            const query = searchInput.value.trim();
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            // Show loading
            searchResults.innerHTML = '<div class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
            searchResults.style.display = 'block';
            
            // Perform AJAX search
            fetch(`{{ route('api.students.search') }}?query=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.students && data.students.length > 0) {
                    searchResults.innerHTML = '';
                    data.students.forEach(student => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${student.student_id}</strong> - ${student.name}
                                    <br>
                                    <small class="text-muted">
                                        ${student.email} | ${student.program || 'No Program'} | 
                                        <span class="badge bg-${student.enrollment_status === 'active' ? 'success' : 'secondary'}">
                                            ${student.enrollment_status}
                                        </span>
                                    </small>
                                </div>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTranscript(${student.id}); return false;">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="generateTranscript(${student.id}); return false;">
                                        <i class="fas fa-file-pdf"></i> Generate
                                    </button>
                                </div>
                            </div>
                        `;
                        searchResults.appendChild(item);
                    });
                } else {
                    searchResults.innerHTML = '<div class="list-group-item text-muted">No students found</div>';
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                // Fallback to client-side search if API fails
                performClientSideSearch(query);
            });
        }
        
        // Fallback client-side search
        function performClientSideSearch(query) {
            const students = @json($students ?? []);
            const filtered = students.filter(student => {
                const searchString = `${student.student_id} ${student.user?.name || ''} ${student.user?.email || ''}`.toLowerCase();
                return searchString.includes(query.toLowerCase());
            });
            
            if (filtered.length > 0) {
                searchResults.innerHTML = '';
                filtered.slice(0, 10).forEach(student => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${student.student_id}</strong> - ${student.user?.name || 'Unknown'}
                                <br>
                                <small class="text-muted">
                                    ${student.user?.email || ''} | ${student.major || 'No Program'} | 
                                    <span class="badge bg-${student.enrollment_status === 'active' ? 'success' : 'secondary'}">
                                        ${student.enrollment_status}
                                    </span>
                                </small>
                            </div>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewTranscript(${student.id}); return false;">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="generateTranscript(${student.id}); return false;">
                                    <i class="fas fa-file-pdf"></i> Generate
                                </button>
                            </div>
                        </div>
                    `;
                    searchResults.appendChild(item);
                });
            } else {
                searchResults.innerHTML = '<div class="list-group-item text-muted">No students found</div>';
            }
        }
        
        // Event listeners
        searchBtn.addEventListener('click', performSearch);
        
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            } else {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 500); // Debounce
            }
        });
        
        // Click outside to close results
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
        
        // Global functions for buttons
        window.viewTranscript = function(studentId) {
            window.location.href = `{{ url('transcripts/view') }}/${studentId}`;
        };
        
        window.generateTranscript = function(studentId) {
            const type = confirm('Generate Official Transcript?\n\nOK = Official\nCancel = Unofficial') ? 'official' : 'unofficial';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('transcripts/generate-pdf') }}/${studentId}`;
            form.innerHTML = `
                @csrf
                <input type="hidden" name="type" value="${type}">
            `;
            document.body.appendChild(form);
            form.submit();
        };
    });
</script>
@endpush
@endsection