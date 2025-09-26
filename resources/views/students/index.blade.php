@extends('layouts.app')

@section('title', 'Students Management')

@section('breadcrumb')
    <span>Students</span>
@endsection

@section('page-actions')
    <div class="page-actions-group">
        <a href="{{ route('students.import-export') }}" class="btn btn-secondary">
            <i class="fas fa-exchange-alt"></i>
            Import/Export
        </a>
        <a href="{{ route('students.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add New Student
        </a>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Main Content Card -->
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Search and Filter Section -->
                <div class="search-filter-section mb-4">
                    <form method="GET" action="{{ route('students.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Search by name, ID, email, or program..." 
                                       class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                <option value="withdrawn" {{ request('status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="level" class="form-select">
                                <option value="">All Levels</option>
                                <option value="freshman" {{ request('level') == 'freshman' ? 'selected' : '' }}>Freshman</option>
                                <option value="sophomore" {{ request('level') == 'sophomore' ? 'selected' : '' }}>Sophomore</option>
                                <option value="junior" {{ request('level') == 'junior' ? 'selected' : '' }}>Junior</option>
                                <option value="senior" {{ request('level') == 'senior' ? 'selected' : '' }}>Senior</option>
                                <option value="graduate" {{ request('level') == 'graduate' ? 'selected' : '' }}>Graduate</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="department" class="form-select">
                                <option value="">All Departments</option>
                                <option value="Computer Science" {{ request('department') == 'Computer Science' ? 'selected' : '' }}>Computer Science</option>
                                <option value="Business" {{ request('department') == 'Business' ? 'selected' : '' }}>Business</option>
                                <option value="Engineering" {{ request('department') == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                                <option value="Medical Sciences" {{ request('department') == 'Medical Sciences' ? 'selected' : '' }}>Medical Sciences</option>
                                <option value="Law" {{ request('department') == 'Law' ? 'selected' : '' }}>Law</option>
                                <option value="Liberal Arts" {{ request('department') == 'Liberal Arts' ? 'selected' : '' }}>Liberal Arts</option>
                                <option value="Education" {{ request('department') == 'Education' ? 'selected' : '' }}>Education</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                @if(request('search') || request('status') || request('level') || request('department'))
                                    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Bulk Actions Section -->
                <div id="bulkActionsSection" class="bulk-actions-panel" style="display: none;">
                    <form id="bulkActionForm" method="POST" action="{{ route('students.bulk-action') }}">
                        @csrf
                        <input type="hidden" name="student_ids" id="selectedStudentIds">
                        
                        <div class="d-flex align-items-center gap-3">
                            <span class="bulk-count">
                                <span id="selectedCount">0</span> selected
                            </span>
                            
                            <select id="bulkAction" name="action" required class="form-select form-select-sm" style="width: auto;">
                                <option value="">Select Action</option>
                                <option value="update_status">Update Status</option>
                                <option value="update_level">Update Level</option>
                                <option value="update_standing">Update Standing</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            
                            <select id="bulkValue" name="value" class="form-select form-select-sm" style="display: none; width: auto;">
                                <!-- Options populated dynamically -->
                            </select>
                            
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-check"></i> Apply
                            </button>
                            
                            <button type="button" onclick="cancelBulkSelection()" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table Header with Count and Select All -->
                <div class="table-header d-flex justify-content-between align-items-center mb-3">
                    <div class="table-info">
                        <span class="text-muted">
                            Showing <strong>{{ $students->firstItem() ?? 0 }}</strong> to 
                            <strong>{{ $students->lastItem() ?? 0 }}</strong> of 
                            <strong>{{ $students->total() }}</strong> students
                        </span>
                    </div>
                    
                    <div class="table-actions">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll()">
                            <label class="form-check-label" for="selectAllCheckbox">
                                Select All
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="select-column" style="width: 40px;">
                                    <span class="text-muted">#</span>
                                </th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Program</th>
                                <th>Level</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">GPA</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                <tr>
                                    <td class="select-column">
                                        <input type="checkbox" class="form-check-input student-checkbox" 
                                               value="{{ $student->id }}" onchange="updateBulkSelection()">
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $student->student_id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="student-avatar me-2">
                                                @if($student->has_profile_photo && isset($student->profile_photo_url))
                                                    <img src="{{ $student->profile_photo_url }}" alt="{{ $student->display_name }}" 
                                                         class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-placeholder">
                                                        {{ strtoupper(substr($student->first_name ?? '', 0, 1) . substr($student->last_name ?? '', 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('students.show', $student) }}" class="text-decoration-none fw-medium">
                                                    {{ $student->display_name }}
                                                </a>
                                                @if($student->preferred_name)
                                                    <small class="text-muted d-block">Prefers: {{ $student->preferred_name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $student->email }}" class="text-decoration-none">
                                            {{ $student->email }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="{{ $student->program_name }}">
                                            {{ $student->program_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($student->academic_level ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @switch($student->enrollment_status)
                                            @case('active')
                                                <span class="badge bg-success">Active</span>
                                                @break
                                            @case('graduated')
                                                <span class="badge bg-info">Graduated</span>
                                                @break
                                            @case('suspended')
                                                <span class="badge bg-danger">Suspended</span>
                                                @break
                                            @case('withdrawn')
                                                <span class="badge bg-warning text-dark">Withdrawn</span>
                                                @break
                                            @case('inactive')
                                                <span class="badge bg-secondary">Inactive</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ ucfirst($student->enrollment_status) }}</span>
                                        @endswitch
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ $student->current_gpa >= 3.5 ? 'text-success' : ($student->current_gpa < 2.0 ? 'text-danger' : '') }}">
                                            {{ number_format($student->current_gpa ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('students.show', $student) }}" 
                                               class="btn btn-outline-secondary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('students.edit', $student) }}" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($student->enrollment_status === 'active')
                                                <a href="{{ route('students.enrollment.manage', $student) }}"
                                                   class="btn btn-outline-success" title="Enrollment">
                                                    <i class="fas fa-graduation-cap"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <h5>No Students Found</h5>
                                            <p class="text-muted">Get started by adding your first student or importing from a CSV file.</p>
                                            <div class="mt-3">
                                                <a href="{{ route('students.create') }}" class="btn btn-primary me-2">
                                                    <i class="fas fa-plus"></i> Add Student
                                                </a>
                                                <a href="{{ route('students.import-export') }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-file-import"></i> Import CSV
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($students->hasPages())
                    <div class="mt-4">
                        {{ $students->links('custom.pagination') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats Dashboard -->
        <div class="row g-3 mt-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-value">{{ $students->total() ?? 0 }}</h3>
                            <p class="stat-label">Total Students</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="fas fa-user-check fa-2x text-success"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-value text-success">
                                {{ \App\Models\Student::where('enrollment_status', 'active')->count() }}
                            </h3>
                            <p class="stat-label">Active Students</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info bg-opacity-10">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-value text-info">
                                {{ number_format(\App\Models\Student::avg('current_gpa') ?? 0, 2) }}
                            </h3>
                            <p class="stat-label">Average GPA</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-secondary bg-opacity-10">
                            <i class="fas fa-user-graduate fa-2x text-secondary"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-value">
                                {{ \App\Models\Student::where('enrollment_status', 'graduated')->count() }}
                            </h3>
                            <p class="stat-label">Graduated</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .search-filter-section {
            background: #f8f9fa;
            padding: 1.25rem;
            border-radius: 0.5rem;
            margin: -0.5rem -0.5rem 1.5rem -0.5rem;
        }

        .bulk-actions-panel {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .bulk-count {
            font-weight: 600;
            color: #856404;
        }

        .table-header {
            padding: 0.75rem 0;
            border-bottom: 2px solid #e9ecef;
        }

        .avatar-placeholder {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 600;
        }

        .stat-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card-body {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }

        .stat-details {
            flex: 1;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            margin: 0.25rem 0 0 0;
        }

        .empty-state {
            padding: 3rem 1rem;
        }

        .page-actions-group {
            display: flex;
            gap: 0.5rem;
        }

        /* Enhanced table styling */
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
        }

        .table > thead {
            border-bottom: 2px solid #dee2e6;
        }

        .table-hover > tbody > tr:hover {
            background-color: rgba(0,0,0,0.02);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .search-filter-section .row {
                gap: 0.5rem;
            }
            
            .stat-card-body {
                padding: 1rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
        }
    </style>

    <!-- JavaScript -->
    <script>
        // Track selected students
        let selectedStudents = [];

        function updateBulkSelection() {
            selectedStudents = [];
            document.querySelectorAll('.student-checkbox:checked').forEach(checkbox => {
                selectedStudents.push(checkbox.value);
            });

            document.getElementById('selectedCount').textContent = selectedStudents.length;
            document.getElementById('selectedStudentIds').value = selectedStudents.join(',');
            
            // Show/hide bulk actions
            document.getElementById('bulkActionsSection').style.display = 
                selectedStudents.length > 0 ? 'block' : 'none';

            // Update select all checkbox
            const allCheckboxes = document.querySelectorAll('.student-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            selectAllCheckbox.checked = selectedStudents.length === allCheckboxes.length && allCheckboxes.length > 0;
        }

        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkSelection();
        }

        function cancelBulkSelection() {
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
            updateBulkSelection();
        }

        // Handle bulk action selection
        document.getElementById('bulkAction')?.addEventListener('change', function() {
            const bulkValue = document.getElementById('bulkValue');
            bulkValue.innerHTML = '';
            bulkValue.style.display = 'none';
            
            if (this.value === 'update_status') {
                bulkValue.style.display = 'inline-block';
                bulkValue.innerHTML = `
                    <option value="">Select Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                    <option value="graduated">Graduated</option>
                    <option value="withdrawn">Withdrawn</option>
                `;
                bulkValue.required = true;
            } else if (this.value === 'update_level') {
                bulkValue.style.display = 'inline-block';
                bulkValue.innerHTML = `
                    <option value="">Select Level</option>
                    <option value="freshman">Freshman</option>
                    <option value="sophomore">Sophomore</option>
                    <option value="junior">Junior</option>
                    <option value="senior">Senior</option>
                    <option value="graduate">Graduate</option>
                `;
                bulkValue.required = true;
            } else if (this.value === 'update_standing') {
                bulkValue.style.display = 'inline-block';
                bulkValue.innerHTML = `
                    <option value="">Select Standing</option>
                    <option value="good">Good Standing</option>
                    <option value="probation">Probation</option>
                    <option value="suspension">Suspension</option>
                    <option value="dismissal">Dismissal</option>
                `;
                bulkValue.required = true;
            } else {
                bulkValue.required = false;
            }
        });

        // Handle form submission
        document.getElementById('bulkActionForm')?.addEventListener('submit', function(e) {
            if (selectedStudents.length === 0) {
                e.preventDefault();
                alert('Please select at least one student.');
                return false;
            }
            
            const action = document.getElementById('bulkAction').value;
            if (!action) {
                e.preventDefault();
                alert('Please select an action.');
                return false;
            }
            
            if (action === 'delete') {
                if (!confirm(`Are you sure you want to delete ${selectedStudents.length} student(s)? This action cannot be undone.`)) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            cancelBulkSelection();
            
            // Auto-dismiss alerts
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(alert => {
                    if (!alert.classList.contains('alert-warning')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });
    </script>
@endsection