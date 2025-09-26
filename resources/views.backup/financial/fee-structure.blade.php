@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-coins me-2"></i>Fee Structure Management
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('financial.admin-dashboard') }}">Financial Dashboard</a></li>
                    <li class="breadcrumb-item active">Fee Structure</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFeeModal">
                <i class="fas fa-plus me-2"></i>Add New Fee
            </button>
            <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                <i class="fas fa-file-upload me-2"></i>Bulk Upload
            </button>
            <a href="{{ route('financial.fee-structure.export') }}" class="btn btn-secondary ms-2">
                <i class="fas fa-download me-2"></i>Export Fees
            </a>
        </div>
    </div>

    <!-- Fee Structure Tabs -->
    <div class="card shadow">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="feeTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tuition-tab" data-bs-toggle="tab" href="#tuition" role="tab">
                        Tuition Fees
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="mandatory-tab" data-bs-toggle="tab" href="#mandatory" role="tab">
                        Mandatory Fees
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="optional-tab" data-bs-toggle="tab" href="#optional" role="tab">
                        Optional Fees
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="miscellaneous-tab" data-bs-toggle="tab" href="#miscellaneous" role="tab">
                        Miscellaneous
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="feeTabContent">
                <!-- Tuition Fees Tab -->
                <div class="tab-pane fade show active" id="tuition" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fee Code</th>
                                    <th>Description</th>
                                    <th>Program</th>
                                    <th>Student Type</th>
                                    <th>Credit Hour Rate</th>
                                    <th>Flat Rate</th>
                                    <th>Effective Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tuitionFees as $fee)
                                <tr>
                                    <td><code>{{ $fee->code }}</code></td>
                                    <td>{{ $fee->description }}</td>
                                    <td>{{ $fee->program->name ?? 'All Programs' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($fee->student_type) }}</span>
                                    </td>
                                    <td>
                                        @if($fee->per_credit_amount)
                                            ${{ number_format($fee->per_credit_amount, 2) }}/credit
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($fee->flat_amount)
                                            ${{ number_format($fee->flat_amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($fee->effective_date)
                                            {{ \Carbon\Carbon::parse($fee->effective_date)->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($fee->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" onclick="editFee({{ $fee->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info" onclick="viewHistory({{ $fee->id }})">
                                                <i class="fas fa-history"></i>
                                            </button>
                                            @if($fee->is_active)
                                                <button class="btn btn-outline-warning" onclick="toggleStatus({{ $fee->id }})">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-outline-success" onclick="toggleStatus({{ $fee->id }})">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-outline-danger" onclick="deleteFee({{ $fee->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mandatory Fees Tab -->
                <div class="tab-pane fade" id="mandatory" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fee Code</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Frequency</th>
                                    <th>Applies To</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mandatoryFees as $fee)
                                <tr>
                                    <td><code>{{ $fee->code }}</code></td>
                                    <td>{{ $fee->description }}</td>
                                    <td>{{ $fee->category }}</td>
                                    <td>${{ number_format($fee->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($fee->frequency) }}</span>
                                    </td>
                                    <td>{{ $fee->applies_to }}</td>
                                    <td>
                                        @if($fee->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" onclick="editFee({{ $fee->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteFee({{ $fee->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Optional and Miscellaneous tabs similar structure... -->
            </div>
        </div>
    </div>

    <!-- Fee Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-body">
                    <h6 class="text-muted">Total Active Fees</h6>
                    <h3 class="mb-0">{{ $totalActiveFees }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-body">
                    <h6 class="text-muted">Average Tuition/Credit</h6>
                    <h3 class="mb-0">${{ number_format($avgTuitionPerCredit, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-body">
                    <h6 class="text-muted">Total Mandatory Fees</h6>
                    <h3 class="mb-0">${{ number_format($totalMandatoryFees, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-body">
                    <h6 class="text-muted">Last Updated</h6>
                    <h3 class="mb-0">{{ $lastUpdated->diffForHumans() }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Fee Modal -->
<div class="modal fade" id="createFeeModal" tabindex="-1" aria-labelledby="createFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('financial.fee.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createFeeModalLabel">Create New Fee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Fee Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Fee Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="type" class="form-label">Fee Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="tuition">Tuition</option>
                                <option value="mandatory">Mandatory</option>
                                <option value="optional">Optional</option>
                                <option value="miscellaneous">Miscellaneous</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="calculation_type" class="form-label">Calculation Type</label>
                            <select class="form-select" id="calculation_type" name="calculation_type">
                                <option value="flat">Flat Amount</option>
                                <option value="per_credit">Per Credit Hour</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="frequency" class="form-label">Frequency</label>
                            <select class="form-select" id="frequency" name="frequency">
                                <option value="one_time">One Time</option>
                                <option value="per_term">Per Term</option>
                                <option value="per_year">Per Year</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="effective_date" class="form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="effective_date" name="effective_date" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="program_id" class="form-label">Program (Optional)</label>
                            <select class="form-select" id="program_id" name="program_id">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="student_type" class="form-label">Student Type</label>
                            <select class="form-select" id="student_type" name="student_type">
                                <option value="all">All Students</option>
                                <option value="undergraduate">Undergraduate</option>
                                <option value="graduate">Graduate</option>
                                <option value="doctoral">Doctoral</option>
                                <option value="international">International</option>
                                <option value="in_state">In-State</option>
                                <option value="out_of_state">Out-of-State</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active (Fee will be applied immediately)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Fee</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editFee(id) {
    // Load fee data and open edit modal
    window.location.href = '/financial/fee-structure/' + id + '/edit';
}

function toggleStatus(id) {
    if(confirm('Are you sure you want to change the status of this fee?')) {
        // Submit status toggle
    }
}

function deleteFee(id) {
    if(confirm('Are you sure you want to delete this fee? This action cannot be undone.')) {
        // Submit delete request
    }
}

function viewHistory(id) {
    // Show fee history modal
}
</script>
@endpush
@endsection