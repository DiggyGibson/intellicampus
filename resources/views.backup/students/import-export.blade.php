@extends('layouts.app')

@section('title', 'Import/Export Students')

@section('breadcrumb')
    <a href="{{ route('students.index') }}">Students</a>
    <i class="fas fa-chevron-right"></i>
    <span>Import/Export</span>
@endsection

@section('content')
    <!-- Page Container -->
    <div class="container-fluid px-4">
        <!-- Page Header with Actions -->
        <div class="page-header-box">
            <div class="page-header-flex">
                <div>
                    <h1 class="page-header-title">
                        <i class="fas fa-exchange-alt text-primary"></i>
                        Import/Export Students
                    </h1>
                    <p class="page-header-subtitle">Bulk manage student data through CSV files</p>
                </div>
                <div class="page-header-actions">
                    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Students
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> {{ session('warning') }}
                @if(session('import_errors'))
                    <div class="mt-2">
                        <strong>Import Errors:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i>
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Content Grid -->
        <div class="row g-4">
            <!-- Import Section -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary bg-gradient text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-file-upload me-2"></i>
                            Import Students
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <p class="text-muted">
                                Import multiple students from a CSV file. Download the template to see the required format.
                            </p>
                            
                            <!-- Template Download -->
                            <div class="d-grid gap-2 mb-4">
                                <a href="{{ route('students.download-sample') }}" 
                                   class="btn btn-info">
                                    <i class="fas fa-download me-2"></i>
                                    Download CSV Template
                                </a>
                            </div>
                        </div>

                        <!-- Import Form -->
                        <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="importFile" class="form-label">
                                    <i class="fas fa-file-csv me-1"></i>
                                    Select CSV File
                                </label>
                                <input type="file" 
                                       class="form-control @error('file') is-invalid @enderror" 
                                       id="importFile" 
                                       name="file" 
                                       accept=".csv" 
                                       required>
                                <small class="form-text text-muted">
                                    Maximum file size: 10MB. Supported format: CSV
                                </small>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="updateExisting" 
                                           name="update_existing" 
                                           value="1">
                                    <label class="form-check-label" for="updateExisting">
                                        Update existing students (based on email match)
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        If unchecked, students with existing emails will be skipped
                                    </small>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload me-2"></i>
                                    Import Students
                                </button>
                            </div>
                        </form>

                        <!-- Import Instructions -->
                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-1"></i>
                                Import Instructions:
                            </h6>
                            <ul class="mb-0 small">
                                <li>Download the template and fill in student data</li>
                                <li>Required fields: first_name, last_name, email, date_of_birth, gender, program_name, department, academic_level, enrollment_status, admission_date</li>
                                <li>Date format: YYYY-MM-DD (e.g., 2000-01-15)</li>
                                <li>Gender values: male, female, other</li>
                                <li>Academic level: freshman, sophomore, junior, senior, graduate</li>
                                <li>Enrollment status: active, inactive, suspended, graduated, withdrawn</li>
                                <li>Student IDs will be auto-generated</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Section -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success bg-gradient text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-file-download me-2"></i>
                            Export Students
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Export student data to CSV format with optional filters.
                        </p>

                        <form action="{{ route('students.export') }}" method="POST">
                            @csrf
                            <input type="hidden" name="format" value="csv">
                            
                            <!-- Filters -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">
                                    <i class="fas fa-filter me-1"></i>
                                    Filters (Optional)
                                </h5>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="filterStatus" class="form-label">Enrollment Status</label>
                                        <select name="filters[enrollment_status]" id="filterStatus" class="form-select">
                                            <option value="">All</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="suspended">Suspended</option>
                                            <option value="graduated">Graduated</option>
                                            <option value="withdrawn">Withdrawn</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="filterLevel" class="form-label">Academic Level</label>
                                        <select name="filters[academic_level]" id="filterLevel" class="form-select">
                                            <option value="">All</option>
                                            <option value="freshman">Freshman</option>
                                            <option value="sophomore">Sophomore</option>
                                            <option value="junior">Junior</option>
                                            <option value="senior">Senior</option>
                                            <option value="graduate">Graduate</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="filterDepartment" class="form-label">Department</label>
                                        <select name="filters[department]" id="filterDepartment" class="form-select">
                                            <option value="">All</option>
                                            <option value="Computer Science">Computer Science</option>
                                            <option value="Business">Business</option>
                                            <option value="Engineering">Engineering</option>
                                            <option value="Medical Sciences">Medical Sciences</option>
                                            <option value="Law">Law</option>
                                            <option value="Liberal Arts">Liberal Arts</option>
                                            <option value="Education">Education</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="filterDateFrom" class="form-label">Date Range (From)</label>
                                        <input type="date" name="filters[date_from]" id="filterDateFrom" class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Column Selection -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">
                                    <i class="fas fa-columns me-1"></i>
                                    Columns to Export
                                </h5>
                                <div class="column-selection-box">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="student_id" id="col-student_id" checked>
                                                <label class="form-check-label" for="col-student_id">Student ID</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="first_name" id="col-first_name" checked>
                                                <label class="form-check-label" for="col-first_name">First Name</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="last_name" id="col-last_name" checked>
                                                <label class="form-check-label" for="col-last_name">Last Name</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="email" id="col-email" checked>
                                                <label class="form-check-label" for="col-email">Email</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="phone" id="col-phone" checked>
                                                <label class="form-check-label" for="col-phone">Phone</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="date_of_birth" id="col-dob">
                                                <label class="form-check-label" for="col-dob">Date of Birth</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="current_gpa" id="col-gpa">
                                                <label class="form-check-label" for="col-gpa">Current GPA</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="program_name" id="col-program" checked>
                                                <label class="form-check-label" for="col-program">Program</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="department" id="col-department" checked>
                                                <label class="form-check-label" for="col-department">Department</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="academic_level" id="col-level" checked>
                                                <label class="form-check-label" for="col-level">Academic Level</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="enrollment_status" id="col-status" checked>
                                                <label class="form-check-label" for="col-status">Status</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="nationality" id="col-nationality">
                                                <label class="form-check-label" for="col-nationality">Nationality</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="address" id="col-address">
                                                <label class="form-check-label" for="col-address">Address</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="columns[]" value="admission_date" id="col-admission">
                                                <label class="form-check-label" for="col-admission">Admission Date</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-link" onclick="selectAllColumns()">
                                            Select All
                                        </button>
                                        |
                                        <button type="button" class="btn btn-sm btn-link" onclick="deselectAllColumns()">
                                            Deselect All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-download me-2"></i>
                                    Export to CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Import History -->
        @if(isset($recentImports) && count($recentImports) > 0)
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Import History
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>File</th>
                                <th>Total Rows</th>
                                <th>Success</th>
                                <th>Errors</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentImports as $import)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($import->created_at)->format('M d, Y H:i') }}</td>
                                <td>{{ $import->filename }}</td>
                                <td>{{ $import->total_rows }}</td>
                                <td class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $import->success_count }}
                                </td>
                                <td class="text-danger">
                                    @if($import->error_count > 0)
                                        <i class="fas fa-times-circle me-1"></i>
                                    @endif
                                    {{ $import->error_count }}
                                </td>
                                <td>
                                    @if($import->error_count == 0)
                                        <span class="badge bg-success">Success</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Partial</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No import history available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Stats Dashboard -->
        <div class="row g-3 mt-4">
            <div class="col-md-3">
                <div class="card stat-card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Students</h6>
                                <h2 class="card-title mb-0">{{ \App\Models\Student::count() }}</h2>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-gradient-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Active Students</h6>
                                <h2 class="card-title mb-0">{{ \App\Models\Student::where('enrollment_status', 'active')->count() }}</h2>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-user-check fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-gradient-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Added Today</h6>
                                <h2 class="card-title mb-0">{{ \App\Models\Student::whereDate('created_at', today())->count() }}</h2>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-gradient-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Added This Week</h6>
                                <h2 class="card-title mb-0">{{ \App\Models\Student::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</h2>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-calendar-week fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .page-header-box {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .page-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1a202c;
            margin: 0;
        }

        .page-header-subtitle {
            color: #6b7280;
            margin-top: 0.25rem;
            margin-bottom: 0;
        }

        .page-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .page-breadcrumb a {
            color: #2563eb;
            text-decoration: none;
        }

        .page-breadcrumb a:hover {
            text-decoration: underline;
        }

        .column-selection-box {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            background: #f8f9fa;
        }

        .stat-card {
            border: none;
            border-radius: 0.5rem;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #5bc0de 0%, #46b8da 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f0ad4e 0%, #ec971f 100%);
        }

        .stat-icon {
            opacity: 0.3;
        }

        .card-header.bg-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        }

        .card-header.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header-flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .column-selection-box .row {
                flex-direction: column;
            }
        }
    </style>

    <!-- JavaScript -->
    <script>
        function selectAllColumns() {
            document.querySelectorAll('input[name="columns[]"]').forEach(checkbox => {
                checkbox.checked = true;
            });
        }
        
        function deselectAllColumns() {
            document.querySelectorAll('input[name="columns[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-info)');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        // File size validation
        document.getElementById('importFile')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            if (file && file.size > maxSize) {
                alert('File size exceeds 10MB limit. Please choose a smaller file.');
                this.value = '';
            }
        });
    </script>
@endsection