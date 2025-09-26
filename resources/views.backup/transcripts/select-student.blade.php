@extends('layouts.app')

@section('title', 'Select Student for Transcript')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Select Student for Transcript</h4>
                </div>
                <div class="card-body">
                    <!-- Search Box -->
                    <div class="mb-4">
                        <label for="student-search" class="form-label">Search for Student</label>
                        <div class="input-group">
                            <input type="text" 
                                   id="student-search" 
                                   class="form-control" 
                                   placeholder="Enter Student ID, Name, or Email..."
                                   autocomplete="off">
                            <button class="btn btn-primary" type="button" id="search-btn">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                        <small class="form-text text-muted">Start typing to search for a student</small>
                    </div>

                    <!-- Search Results -->
                    <div id="search-results" style="display: none;">
                        <!-- Results will appear here -->
                    </div>

                    <!-- Recent Students (Optional) -->
                    <div class="mt-5">
                        <h5 class="mb-3">Recent Students</h5>
                        <div class="list-group">
                            @foreach($students->take(5) as $student)
                            <a href="{{ route('transcripts.view', $student->id) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $student->user->name ?? 'Unknown' }}</h6>
                                        <p class="mb-1">Student ID: {{ $student->student_id }}</p>
                                        <small class="text-muted">{{ ucfirst($student->enrollment_status) }}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary">View Transcript</span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
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
    
    function performSearch() {
        const query = searchInput.value.trim();
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        searchResults.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Searching...</span></div></div>';
        searchResults.style.display = 'block';
        
        // Fallback search using passed data
        const students = @json($students);
        const query_lower = query.toLowerCase();
        const filtered = students.filter(s => 
            s.student_id.toLowerCase().includes(query_lower) ||
            (s.user?.name && s.user.name.toLowerCase().includes(query_lower)) ||
            (s.user?.email && s.user.email.toLowerCase().includes(query_lower))
        );
        
        if (filtered.length > 0) {
            let html = '<h5 class="mb-3">Search Results</h5><div class="list-group">';
            filtered.slice(0, 10).forEach(student => {
                html += `
                    <a href="/transcripts/view/${student.id}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <div>
                                <h6 class="mb-1">${student.user?.name || 'Unknown'}</h6>
                                <p class="mb-1">ID: ${student.student_id} | Email: ${student.user?.email || 'N/A'}</p>
                                <small class="text-muted">Status: ${student.enrollment_status}</small>
                            </div>
                            <div>
                                <span class="badge bg-primary">View Transcript</span>
                            </div>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            searchResults.innerHTML = html;
        } else {
            searchResults.innerHTML = '<div class="alert alert-info">No students found matching your search.</div>';
        }
    }
    
    searchBtn.addEventListener('click', performSearch);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500);
    });
});
</script>
@endpush
@endsection