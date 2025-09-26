@extends('layouts.app')

@section('title', 'Grade Scale Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Grade Scale Management</h1>
            <p class="mb-0 text-muted">Configure grading scales for different courses and programs</p>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Create New Scale -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Scale</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.grades.scales.store') }}" method="POST" id="createScaleForm">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Scale Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="scale_type" class="form-label">Scale Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('scale_type') is-invalid @enderror" 
                                    id="scale_type" name="scale_type" required>
                                <option value="">Select Type</option>
                                <option value="letter" {{ old('scale_type') == 'letter' ? 'selected' : '' }}>Letter Grade</option>
                                <option value="percentage" {{ old('scale_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="points" {{ old('scale_type') == 'points' ? 'selected' : '' }}>Points</option>
                                <option value="custom" {{ old('scale_type') == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                            @error('scale_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="2">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1">
                            <label class="form-check-label" for="is_default">Set as Default</label>
                        </div>

                        <h6 class="mb-3">Grade Values</h6>
                        <div id="gradeValuesContainer">
                            <!-- Grade values will be added here -->
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="addGradeValue()">
                            <i class="fas fa-plus me-1"></i>Add Grade Value
                        </button>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Scale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Existing Scales -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Existing Grade Scales</h5>
                </div>
                <div class="card-body">
                    @forelse($gradeScales as $scale)
                    <div class="card mb-3 {{ $scale->is_default ? 'border-primary' : '' }}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">
                                    {{ $scale->name }}
                                    @if($scale->is_default)
                                        <span class="badge bg-primary ms-2">Default</span>
                                    @endif
                                    @if($scale->is_active)
                                        <span class="badge bg-success ms-2">Active</span>
                                    @else
                                        <span class="badge bg-secondary ms-2">Inactive</span>
                                    @endif
                                </h6>
                                <small class="text-muted">{{ $scale->description }}</small>
                            </div>
                            <div>
                                <span class="badge bg-info">{{ $scale->usage_count }} sections</span>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" 
                                        onclick="viewScale({{ $scale->id }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                        onclick="editScale({{ $scale->id }})"
                                        {{ $scale->usage_count > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($scale->usage_count == 0 && !$scale->is_default)
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteScale({{ $scale->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Grade</th>
                                            <th>Min %</th>
                                            <th>Max %</th>
                                            <th>Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($scale->scale_values as $grade => $values)
                                        <tr>
                                            <td><strong>{{ $grade }}</strong></td>
                                            <td>{{ $values['min'] ?? 'N/A' }}</td>
                                            <td>{{ $values['max'] ?? 'N/A' }}</td>
                                            <td>{{ $values['points'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No grade scales configured yet. Create one to get started.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Grade Conversion Tool -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Grade Conversion Tool</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">From Scale</label>
                            <select class="form-select" id="fromScale">
                                <option value="">Select Scale</option>
                                @foreach($gradeScales->where('is_active', true) as $scale)
                                    <option value="{{ $scale->id }}">{{ $scale->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Grade</label>
                            <input type="text" class="form-control" id="fromGrade" placeholder="e.g., B+">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Scale</label>
                            <select class="form-select" id="toScale">
                                <option value="">Select Scale</option>
                                @foreach($gradeScales->where('is_active', true) as $scale)
                                    <option value="{{ $scale->id }}">{{ $scale->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-info w-100" onclick="convertGrade()">
                                Convert
                            </button>
                        </div>
                    </div>
                    <div id="conversionResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Scale Modal -->
<div class="modal fade" id="editScaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Grade Scale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editScaleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Edit form content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let gradeValueIndex = 0;

// Add standard letter grades on load
document.addEventListener('DOMContentLoaded', function() {
    const scaleType = document.getElementById('scale_type');
    if (scaleType) {
        scaleType.addEventListener('change', function() {
            if (this.value === 'letter') {
                loadStandardLetterGrades();
            } else {
                document.getElementById('gradeValuesContainer').innerHTML = '';
                gradeValueIndex = 0;
            }
        });
    }
});

// Load standard letter grades
function loadStandardLetterGrades() {
    const standardGrades = [
        {grade: 'A', min: 93, max: 100, points: 4.0},
        {grade: 'A-', min: 90, max: 92.99, points: 3.7},
        {grade: 'B+', min: 87, max: 89.99, points: 3.3},
        {grade: 'B', min: 83, max: 86.99, points: 3.0},
        {grade: 'B-', min: 80, max: 82.99, points: 2.7},
        {grade: 'C+', min: 77, max: 79.99, points: 2.3},
        {grade: 'C', min: 73, max: 76.99, points: 2.0},
        {grade: 'C-', min: 70, max: 72.99, points: 1.7},
        {grade: 'D+', min: 67, max: 69.99, points: 1.3},
        {grade: 'D', min: 63, max: 66.99, points: 1.0},
        {grade: 'F', min: 0, max: 62.99, points: 0.0}
    ];

    document.getElementById('gradeValuesContainer').innerHTML = '';
    gradeValueIndex = 0;
    
    standardGrades.forEach(g => {
        addGradeValue(g);
    });
}

// Add grade value row
function addGradeValue(presetValues = null) {
    const container = document.getElementById('gradeValuesContainer');
    const div = document.createElement('div');
    div.className = 'grade-value-row mb-2 p-2 border rounded';
    div.innerHTML = `
        <div class="row g-2">
            <div class="col-3">
                <input type="text" class="form-control form-control-sm" 
                       name="scale_values[${gradeValueIndex}][grade]" 
                       placeholder="Grade" 
                       value="${presetValues ? presetValues.grade : ''}" required>
            </div>
            <div class="col-3">
                <input type="number" class="form-control form-control-sm" 
                       name="scale_values[${gradeValueIndex}][min]" 
                       placeholder="Min %" 
                       step="0.01" 
                       value="${presetValues ? presetValues.min : ''}" required>
            </div>
            <div class="col-3">
                <input type="number" class="form-control form-control-sm" 
                       name="scale_values[${gradeValueIndex}][max]" 
                       placeholder="Max %" 
                       step="0.01" 
                       value="${presetValues ? presetValues.max : ''}" required>
            </div>
            <div class="col-2">
                <input type="number" class="form-control form-control-sm" 
                       name="scale_values[${gradeValueIndex}][points]" 
                       placeholder="Points" 
                       step="0.1" 
                       value="${presetValues ? presetValues.points : ''}" required>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeGradeValue(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(div);
    gradeValueIndex++;
}

// Remove grade value row
function removeGradeValue(button) {
    button.closest('.grade-value-row').remove();
}

// View scale details
function viewScale(id) {
    fetch(`/admin/grades/scales/${id}`)
        .then(response => response.json())
        .then(data => {
            // Display scale details
            console.log(data);
        });
}

// Edit scale
function editScale(id) {
    // Load scale data and populate edit form
    document.getElementById('editScaleForm').action = `/admin/grades/scales/${id}`;
    const modal = new bootstrap.Modal(document.getElementById('editScaleModal'));
    modal.show();
}

// Delete scale
function deleteScale(id) {
    if (confirm('Are you sure you want to delete this grade scale?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/grades/scales/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Convert grade
function convertGrade() {
    const fromScale = document.getElementById('fromScale').value;
    const toScale = document.getElementById('toScale').value;
    const grade = document.getElementById('fromGrade').value;
    
    if (!fromScale || !toScale || !grade) {
        alert('Please fill all fields');
        return;
    }
    
    fetch('/admin/grades/scales/convert', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            from_scale_id: fromScale,
            to_scale_id: toScale,
            grade: grade
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('conversionResult').innerHTML = `
            <div class="alert alert-success">
                <strong>${data.original_grade}</strong> in ${data.from_scale} 
                = <strong>${data.converted_grade}</strong> in ${data.to_scale}
                (${data.percentage}%)
            </div>
        `;
    });
}
</script>
@endpush
@endsection