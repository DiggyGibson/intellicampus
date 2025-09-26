@extends('layouts.app')

@section('title', 'Grade Components - ' . $section->course->code)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('grades.index') }}">Grade Management</a></li>
                    <li class="breadcrumb-item active">Grade Components</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Grade Components Setup</h1>
            <p class="text-muted">
                {{ $section->course->code }} - {{ $section->course->title }} | 
                Section {{ $section->section_code }}
            </p>
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

    <!-- Weight Summary Card -->
    <div class="card mb-4 {{ $totalWeight != 100 ? 'border-danger' : 'border-success' }}">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-0">Total Weight: 
                        <span class="{{ $totalWeight != 100 ? 'text-danger' : 'text-success' }}">
                            {{ $totalWeight }}%
                        </span>
                    </h5>
                    @if($totalWeight != 100)
                        <p class="text-danger mb-0 mt-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Warning: Total weight must equal 100%. Currently {{ $totalWeight > 100 ? 'over' : 'under' }} by {{ abs(100 - $totalWeight) }}%
                        </p>
                    @else
                        <p class="text-success mb-0 mt-2">
                            <i class="fas fa-check-circle me-1"></i>
                            Perfect! Your grade components total exactly 100%.
                        </p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-primary" onclick="addComponent()">
                        <i class="fas fa-plus me-2"></i>Add Component
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Components Form -->
    <form action="{{ route('grades.components.save', $section->id) }}" method="POST" id="componentsForm">
        @csrf
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Grade Components</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="componentsTable">
                        <thead>
                            <tr>
                                <th width="30">#</th>
                                <th>Component Name</th>
                                <th width="150">Type</th>
                                <th width="100">Weight (%)</th>
                                <th width="120">Max Points</th>
                                <th width="150">Due Date</th>
                                <th width="100">Extra Credit</th>
                                <th width="80">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="componentsList">
                            @forelse($components as $index => $component)
                            <tr data-index="{{ $index }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <input type="text" class="form-control" 
                                           name="components[{{ $index }}][name]" 
                                           value="{{ $component->name }}" required>
                                </td>
                                <td>
                                    <select class="form-select" name="components[{{ $index }}][type]" required>
                                        <option value="exam" {{ $component->type == 'exam' ? 'selected' : '' }}>Exam</option>
                                        <option value="quiz" {{ $component->type == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                        <option value="assignment" {{ $component->type == 'assignment' ? 'selected' : '' }}>Assignment</option>
                                        <option value="project" {{ $component->type == 'project' ? 'selected' : '' }}>Project</option>
                                        <option value="participation" {{ $component->type == 'participation' ? 'selected' : '' }}>Participation</option>
                                        <option value="attendance" {{ $component->type == 'attendance' ? 'selected' : '' }}>Attendance</option>
                                        <option value="presentation" {{ $component->type == 'presentation' ? 'selected' : '' }}>Presentation</option>
                                        <option value="lab" {{ $component->type == 'lab' ? 'selected' : '' }}>Lab Work</option>
                                        <option value="homework" {{ $component->type == 'homework' ? 'selected' : '' }}>Homework</option>
                                        <option value="other" {{ $component->type == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control weight-input" 
                                           name="components[{{ $index }}][weight]" 
                                           value="{{ $component->weight }}" 
                                           min="0" max="100" step="0.01" 
                                           onchange="updateTotalWeight()" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control" 
                                           name="components[{{ $index }}][max_points]" 
                                           value="{{ $component->max_points }}" 
                                           min="0" step="0.01" required>
                                </td>
                                <td>
                                    <input type="date" class="form-control" 
                                           name="components[{{ $index }}][due_date]" 
                                           value="{{ $component->due_date ? $component->due_date->format('Y-m-d') : '' }}">
                                </td>
                                <td class="text-center">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input extra-credit-check" 
                                               name="components[{{ $index }}][is_extra_credit]" 
                                               value="1" 
                                               onchange="updateTotalWeight()"
                                               {{ $component->is_extra_credit ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeComponent(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <!-- Default components will be added if empty -->
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($components->isEmpty())
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    No components defined yet. Click "Add Component" to start building your grading structure.
                </div>
                @endif

                <!-- Quick Templates -->
                <div class="card mt-4 bg-light">
                    <div class="card-body">
                        <h6>Quick Templates:</h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="applyTemplate('standard')">
                                Standard Course
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="applyTemplate('lab')">
                                Lab Course
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="applyTemplate('project')">
                                Project-Based
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="applyTemplate('seminar')">
                                Seminar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <a href="{{ route('grades.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-success w-100" id="saveButton">
                            <i class="fas fa-save me-2"></i>Save Components
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let componentIndex = {{ $components->count() }};

// Add new component row
function addComponent() {
    const tbody = document.getElementById('componentsList');
    const newRow = document.createElement('tr');
    newRow.dataset.index = componentIndex;
    
    newRow.innerHTML = `
        <td class="text-center">${tbody.children.length + 1}</td>
        <td>
            <input type="text" class="form-control" 
                   name="components[${componentIndex}][name]" 
                   placeholder="e.g., Midterm Exam" required>
        </td>
        <td>
            <select class="form-select" name="components[${componentIndex}][type]" required>
                <option value="">Select Type</option>
                <option value="exam">Exam</option>
                <option value="quiz">Quiz</option>
                <option value="assignment">Assignment</option>
                <option value="project">Project</option>
                <option value="participation">Participation</option>
                <option value="attendance">Attendance</option>
                <option value="presentation">Presentation</option>
                <option value="lab">Lab Work</option>
                <option value="homework">Homework</option>
                <option value="other">Other</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control weight-input" 
                   name="components[${componentIndex}][weight]" 
                   min="0" max="100" step="0.01" 
                   onchange="updateTotalWeight()" required>
        </td>
        <td>
            <input type="number" class="form-control" 
                   name="components[${componentIndex}][max_points]" 
                   value="100" min="0" step="0.01" required>
        </td>
        <td>
            <input type="date" class="form-control" 
                   name="components[${componentIndex}][due_date]">
        </td>
        <td class="text-center">
            <div class="form-check">
                <input type="checkbox" class="form-check-input extra-credit-check" 
                       name="components[${componentIndex}][is_extra_credit]" 
                       value="1" onchange="updateTotalWeight()">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeComponent(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    componentIndex++;
    updateRowNumbers();
}

// Remove component row
function removeComponent(button) {
    if (confirm('Are you sure you want to remove this component?')) {
        button.closest('tr').remove();
        updateRowNumbers();
        updateTotalWeight();
    }
}

// Update row numbers
function updateRowNumbers() {
    const rows = document.querySelectorAll('#componentsList tr');
    rows.forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
}

// Update total weight calculation
function updateTotalWeight() {
    let total = 0;
    document.querySelectorAll('.weight-input').forEach((input, index) => {
        const row = input.closest('tr');
        const isExtraCredit = row.querySelector('.extra-credit-check').checked;
        
        if (!isExtraCredit && input.value) {
            total += parseFloat(input.value) || 0;
        }
    });
    
    const totalElement = document.querySelector('.card-body h5 span');
    totalElement.textContent = total.toFixed(2) + '%';
    totalElement.className = total !== 100 ? 'text-danger' : 'text-success';
    
    // Update warning message
    const cardDiv = document.querySelector('.card.mb-4');
    cardDiv.className = `card mb-4 ${total !== 100 ? 'border-danger' : 'border-success'}`;
    
    // Update save button
    const saveButton = document.getElementById('saveButton');
    if (total !== 100) {
        saveButton.classList.add('btn-warning');
        saveButton.classList.remove('btn-success');
    } else {
        saveButton.classList.add('btn-success');
        saveButton.classList.remove('btn-warning');
    }
}

// Apply predefined templates
function applyTemplate(type) {
    if (!confirm('This will replace all existing components. Continue?')) {
        return;
    }
    
    const templates = {
        'standard': [
            {name: 'Assignments', type: 'assignment', weight: 20, max_points: 100},
            {name: 'Quizzes', type: 'quiz', weight: 15, max_points: 100},
            {name: 'Midterm Exam', type: 'exam', weight: 25, max_points: 100},
            {name: 'Final Exam', type: 'exam', weight: 30, max_points: 100},
            {name: 'Participation', type: 'participation', weight: 10, max_points: 100}
        ],
        'lab': [
            {name: 'Lab Reports', type: 'lab', weight: 30, max_points: 100},
            {name: 'Lab Quizzes', type: 'quiz', weight: 10, max_points: 100},
            {name: 'Midterm Exam', type: 'exam', weight: 20, max_points: 100},
            {name: 'Final Exam', type: 'exam', weight: 25, max_points: 100},
            {name: 'Lab Performance', type: 'lab', weight: 15, max_points: 100}
        ],
        'project': [
            {name: 'Project Proposal', type: 'project', weight: 10, max_points: 100},
            {name: 'Progress Reports', type: 'assignment', weight: 15, max_points: 100},
            {name: 'Final Project', type: 'project', weight: 40, max_points: 100},
            {name: 'Presentation', type: 'presentation', weight: 20, max_points: 100},
            {name: 'Peer Review', type: 'participation', weight: 15, max_points: 100}
        ],
        'seminar': [
            {name: 'Class Participation', type: 'participation', weight: 25, max_points: 100},
            {name: 'Presentations', type: 'presentation', weight: 30, max_points: 100},
            {name: 'Research Paper', type: 'project', weight: 35, max_points: 100},
            {name: 'Peer Reviews', type: 'assignment', weight: 10, max_points: 100}
        ]
    };
    
    const template = templates[type];
    if (!template) return;
    
    // Clear existing components
    document.getElementById('componentsList').innerHTML = '';
    componentIndex = 0;
    
    // Add template components
    template.forEach(() => {
        addComponent();
    });
    
    // Fill in template values
    const rows = document.querySelectorAll('#componentsList tr');
    rows.forEach((row, index) => {
        if (template[index]) {
            row.querySelector(`input[name*="[name]"]`).value = template[index].name;
            row.querySelector(`select[name*="[type]"]`).value = template[index].type;
            row.querySelector(`input[name*="[weight]"]`).value = template[index].weight;
            row.querySelector(`input[name*="[max_points]"]`).value = template[index].max_points;
        }
    });
    
    updateTotalWeight();
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    updateTotalWeight();
});

// Form validation before submit
document.getElementById('componentsForm').addEventListener('submit', function(e) {
    const total = parseFloat(document.querySelector('.card-body h5 span').textContent);
    
    if (total !== 100) {
        if (!confirm(`Warning: Total weight is ${total}%, not 100%. Are you sure you want to continue?`)) {
            e.preventDefault();
        }
    }
});
</script>
@endpush
@endsection