@extends('layouts.app')

@section('title', 'Create Transcript Request')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Page Header -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-file-signature me-2"></i>Create Transcript Request (Admin)</h4>
                </div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Request Form -->
                    <form action="{{ route('transcripts.request.submit') }}" method="POST" id="adminTranscriptRequestForm">
                        @csrf
                        
                        <!-- Student Selection -->
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Student Selection</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label for="student-search" class="form-label">Search and Select Student <span class="text-danger">*</span></label>
                                        <div class="input-group mb-2">
                                            <input type="text" 
                                                   id="student-search" 
                                                   class="form-control" 
                                                   placeholder="Search by Student ID, Name, or Email..."
                                                   autocomplete="off">
                                            <button class="btn btn-outline-primary" type="button" id="search-btn">
                                                <i class="fas fa-search"></i> Search
                                            </button>
                                        </div>
                                        <input type="hidden" name="student_id" id="student_id" required>
                                        <div id="selected-student" class="alert alert-info" style="display: none;">
                                            <!-- Selected student will appear here -->
                                        </div>
                                        <div id="search-results" style="display: none;">
                                            <!-- Search results will appear here -->
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Student Information</label>
                                        <div id="student-info" class="text-muted">
                                            <small>Select a student to view their information</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Request Details -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Request Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="type" class="form-label">Transcript Type <span class="text-danger">*</span></label>
                                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="">Select type...</option>
                                            <option value="official" {{ old('type') == 'official' ? 'selected' : '' }}>Official</option>
                                            <option value="unofficial" {{ old('type') == 'unofficial' ? 'selected' : '' }}>Unofficial</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="delivery_method" class="form-label">Delivery Method <span class="text-danger">*</span></label>
                                        <select name="delivery_method" id="delivery_method" class="form-select @error('delivery_method') is-invalid @enderror" required>
                                            <option value="">Select delivery method...</option>
                                            <option value="electronic" {{ old('delivery_method') == 'electronic' ? 'selected' : '' }}>
                                                Electronic (Email)
                                            </option>
                                            <option value="mail" {{ old('delivery_method') == 'mail' ? 'selected' : '' }}>
                                                Mail (Postal)
                                            </option>
                                            <option value="pickup" {{ old('delivery_method') == 'pickup' ? 'selected' : '' }}>
                                                Pickup (In Person)
                                            </option>
                                        </select>
                                        @error('delivery_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="copies" class="form-label">Number of Copies <span class="text-danger">*</span></label>
                                        <input type="number" name="copies" id="copies" 
                                               class="form-control @error('copies') is-invalid @enderror" 
                                               min="1" max="10" value="{{ old('copies', 1) }}" required>
                                        @error('copies')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Recipient Information -->
                                <h6 class="mb-3">Recipient Information</h6>
                                
                                <div class="mb-3">
                                    <label for="recipient_name" class="form-label">Recipient Name/Organization <span class="text-danger">*</span></label>
                                    <input type="text" name="recipient_name" id="recipient_name" 
                                           class="form-control @error('recipient_name') is-invalid @enderror" 
                                           value="{{ old('recipient_name') }}" required>
                                    @error('recipient_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3" id="email-field" style="display: none;">
                                    <label for="recipient_email" class="form-label">Recipient Email <span class="text-danger">*</span></label>
                                    <input type="email" name="recipient_email" id="recipient_email" 
                                           class="form-control @error('recipient_email') is-invalid @enderror" 
                                           value="{{ old('recipient_email') }}">
                                    @error('recipient_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3" id="address-field" style="display: none;">
                                    <label for="mailing_address" class="form-label">Mailing Address <span class="text-danger">*</span></label>
                                    <textarea name="mailing_address" id="mailing_address" 
                                              class="form-control @error('mailing_address') is-invalid @enderror" 
                                              rows="4">{{ old('mailing_address') }}</textarea>
                                    @error('mailing_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="purpose" class="form-label">Purpose of Request <span class="text-danger">*</span></label>
                                    <select name="purpose" id="purpose" class="form-select @error('purpose') is-invalid @enderror" required>
                                        <option value="">Select purpose...</option>
                                        <option value="Employment">Employment</option>
                                        <option value="Graduate School">Graduate School</option>
                                        <option value="Transfer to Another Institution">Transfer to Another Institution</option>
                                        <option value="Professional Licensing">Professional Licensing</option>
                                        <option value="Scholarship Application">Scholarship Application</option>
                                        <option value="Immigration">Immigration</option>
                                        <option value="Verification">Verification</option>
                                        <option value="Administrative">Administrative</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    @error('purpose')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="special_instructions" class="form-label">Special Instructions / Admin Notes</label>
                                    <textarea name="special_instructions" id="special_instructions" 
                                              class="form-control @error('special_instructions') is-invalid @enderror" 
                                              rows="3">{{ old('special_instructions') }}</textarea>
                                    @error('special_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="rush_order" id="rush_order" 
                                           class="form-check-input" value="1" {{ old('rush_order') ? 'checked' : '' }}>
                                    <label for="rush_order" class="form-check-label">
                                        <strong>Rush Order</strong> - Process within 1 business day
                                    </label>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <strong>Admin Note:</strong> Fees will be waived for admin-generated requests. 
                                    The student will not be charged for this transcript request.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('transcripts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Pending Requests -->
            @if($pendingRequests->isNotEmpty())
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Pending Requests</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Recipient</th>
                                    <th>Rush</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests->take(5) as $request)
                                <tr>
                                    <td>{{ $request->request_number }}</td>
                                    <td>
                                        {{ $request->student->student_id }}<br>
                                        <small>{{ $request->student->user->name ?? 'Unknown' }}</small>
                                    </td>
                                    <td>{{ ucfirst($request->type) }}</td>
                                    <td>{{ $request->recipient_name }}</td>
                                    <td>
                                        @if($request->rush_order)
                                            <span class="badge bg-danger">Rush</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Pending</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('transcripts.request.status', $request) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('student-search');
    const searchBtn = document.getElementById('search-btn');
    const searchResults = document.getElementById('search-results');
    const selectedStudent = document.getElementById('selected-student');
    const studentIdInput = document.getElementById('student_id');
    const studentInfo = document.getElementById('student-info');
    const deliveryMethod = document.getElementById('delivery_method');
    const emailField = document.getElementById('email-field');
    const addressField = document.getElementById('address-field');
    
    let searchTimeout;
    let selectedStudentData = null;
    
    // Search function
    function searchStudents() {
        const query = searchInput.value.trim();
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        searchResults.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Searching...</span></div></div>';
        searchResults.style.display = 'block';
        
        // Use the students data passed from controller
        const students = @json($students);
        const query_lower = query.toLowerCase();
        const filtered = students.filter(s => 
            s.student_id.toLowerCase().includes(query_lower) ||
            (s.user?.name && s.user.name.toLowerCase().includes(query_lower)) ||
            (s.user?.email && s.user.email.toLowerCase().includes(query_lower))
        );
        
        if (filtered.length > 0) {
            let html = '<div class="list-group">';
            filtered.slice(0, 10).forEach(student => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action" onclick="selectStudent(${JSON.stringify(student).replace(/"/g, '&quot;')}); return false;">
                        <div class="d-flex w-100 justify-content-between">
                            <div>
                                <h6 class="mb-1">${student.user?.name || 'Unknown'}</h6>
                                <p class="mb-1">ID: ${student.student_id} | Email: ${student.user?.email || 'N/A'}</p>
                                <small class="text-muted">Status: ${student.enrollment_status}</small>
                            </div>
                            <div>
                                <span class="badge bg-primary">Select</span>
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
    
    // Select student function
    window.selectStudent = function(student) {
        selectedStudentData = student;
        studentIdInput.value = student.id;
        
        selectedStudent.innerHTML = `
            <strong>Selected:</strong> ${student.user?.name || 'Unknown'} 
            (ID: ${student.student_id})
            <button type="button" class="btn btn-sm btn-danger float-end" onclick="clearSelection()">
                <i class="fas fa-times"></i> Clear
            </button>
        `;
        selectedStudent.style.display = 'block';
        
        studentInfo.innerHTML = `
            <div class="small">
                <strong>Name:</strong> ${student.user?.name || 'Unknown'}<br>
                <strong>ID:</strong> ${student.student_id}<br>
                <strong>Email:</strong> ${student.user?.email || 'N/A'}<br>
                <strong>Status:</strong> ${student.enrollment_status}<br>
                <strong>Program:</strong> ${student.major || 'Undeclared'}<br>
                <strong>GPA:</strong> ${student.cumulative_gpa || 'N/A'}
            </div>
        `;
        
        searchResults.style.display = 'none';
        searchInput.value = '';
    };
    
    // Clear selection
    window.clearSelection = function() {
        selectedStudentData = null;
        studentIdInput.value = '';
        selectedStudent.style.display = 'none';
        studentInfo.innerHTML = '<small>Select a student to view their information</small>';
    };
    
    // Show/hide fields based on delivery method
    deliveryMethod.addEventListener('change', function() {
        emailField.style.display = 'none';
        addressField.style.display = 'none';
        document.getElementById('recipient_email').required = false;
        document.getElementById('mailing_address').required = false;
        
        if (this.value === 'electronic') {
            emailField.style.display = 'block';
            document.getElementById('recipient_email').required = true;
        } else if (this.value === 'mail') {
            addressField.style.display = 'block';
            document.getElementById('mailing_address').required = true;
        }
    });
    
    // Search event listeners
    searchBtn.addEventListener('click', searchStudents);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchStudents();
        }
    });
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(searchStudents, 500);
    });
    
    // Form validation
    document.getElementById('adminTranscriptRequestForm').addEventListener('submit', function(e) {
        if (!studentIdInput.value) {
            e.preventDefault();
            alert('Please select a student for the transcript request.');
            return false;
        }
    });
});
</script>
@endpush
@endsection