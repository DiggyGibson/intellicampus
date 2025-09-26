{{-- resources/views/degree-audit/student/what-if.blade.php --}}
@extends('layouts.app')

@section('title', 'What-If Analysis')

@section('styles')
<style>
    .scenario-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .scenario-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    }
    
    .scenario-card.selected {
        border-color: #667eea;
        background: linear-gradient(135deg, #667eea10, #764ba210);
    }
    
    .comparison-table td {
        vertical-align: middle;
    }
    
    .better { color: #10b981; font-weight: bold; }
    .worse { color: #ef4444; font-weight: bold; }
    .same { color: #6b7280; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>What-If Analysis</h2>
            <p class="text-muted">Explore how changing your major, minor, or concentration would affect your degree progress</p>
        </div>
    </div>

    <!-- Scenario Builder -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Build Your Scenario</h5>
                </div>
                <div class="card-body">
                    <form id="whatIfForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="newMajor" class="form-label">New Major</label>
                                <select class="form-select" id="newMajor" name="major_id">
                                    <option value="">Keep Current Major</option>
                                    @foreach($programs ?? [] as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="newMinor" class="form-label">Add/Change Minor</label>
                                <select class="form-select" id="newMinor" name="minor_id">
                                    <option value="">No Minor</option>
                                    @foreach($minors ?? [] as $minor)
                                        <option value="{{ $minor->id }}">{{ $minor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="concentration" class="form-label">Concentration/Track</label>
                                <select class="form-select" id="concentration" name="concentration_id">
                                    <option value="">No Concentration</option>
                                    @foreach($concentrations ?? [] as $concentration)
                                        <option value="{{ $concentration->id }}">{{ $concentration->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="catalogYear" class="form-label">Catalog Year</label>
                                <select class="form-select" id="catalogYear" name="catalog_year">
                                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                        <option value="{{ $year }}">{{ $year }}-{{ $year + 1 }}</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <button type="button" class="btn btn-success" onclick="runAnalysis()">
                                    <i class="fas fa-play"></i> Run Analysis
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Saved Scenarios -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Saved Scenarios</h5>
                </div>
                <div class="card-body">
                    @forelse($savedScenarios ?? [] as $scenario)
                    <div class="scenario-card p-3 mb-3" onclick="loadScenario({{ $scenario->id }})">
                        <h6>{{ $scenario->name }}</h6>
                        <small class="text-muted">
                            Major: {{ $scenario->major_name }}<br>
                            Created: {{ $scenario->created_at->format('M d, Y') }}
                        </small>
                    </div>
                    @empty
                    <p class="text-muted">No saved scenarios yet</p>
                    @endforelse
                    
                    <button class="btn btn-sm btn-outline-primary w-100 mt-3" 
                            onclick="$('#saveScenarioModal').modal('show')">
                        <i class="fas fa-save"></i> Save Current Scenario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Results -->
    <div class="row" id="analysisResults" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Analysis Results</h5>
                </div>
                <div class="card-body">
                    <!-- Comparison Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Current Program</h6>
                            <div class="card">
                                <div class="card-body">
                                    <p><strong>Major:</strong> <span id="currentMajor">Computer Science</span></p>
                                    <p><strong>Credits to Graduate:</strong> <span id="currentCredits">30</span></p>
                                    <p><strong>Terms Remaining:</strong> <span id="currentTerms">2</span></p>
                                    <p><strong>Completion:</strong> <span id="currentCompletion">75%</span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>What-If Scenario</h6>
                            <div class="card border-primary">
                                <div class="card-body">
                                    <p><strong>Major:</strong> <span id="whatIfMajor">Information Systems</span></p>
                                    <p><strong>Credits to Graduate:</strong> <span id="whatIfCredits">45</span></p>
                                    <p><strong>Terms Remaining:</strong> <span id="whatIfTerms">3</span></p>
                                    <p><strong>Completion:</strong> <span id="whatIfCompletion">60%</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Comparison Table -->
                    <h6>Requirement Comparison</h6>
                    <table class="table comparison-table">
                        <thead>
                            <tr>
                                <th>Requirement</th>
                                <th>Current Status</th>
                                <th>What-If Status</th>
                                <th>Impact</th>
                            </tr>
                        </thead>
                        <tbody id="comparisonTableBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>

                    <!-- Recommendations -->
                    <div class="alert alert-warning mt-4">
                        <h6><i class="fas fa-lightbulb"></i> Recommendations</h6>
                        <ul class="mb-0" id="recommendations">
                            <!-- Populated by JavaScript -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save Scenario Modal -->
<div class="modal fade" id="saveScenarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Scenario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="scenarioName" class="form-label">Scenario Name</label>
                    <input type="text" class="form-control" id="scenarioName" 
                           placeholder="e.g., Switch to Information Systems">
                </div>
                <div class="mb-3">
                    <label for="scenarioNotes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="scenarioNotes" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveScenario()">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function runAnalysis() {
    const formData = new FormData(document.getElementById('whatIfForm'));
    
    fetch('{{ route("degree-audit.what-if.analyze") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        displayResults(data);
    });
}

function displayResults(data) {
    document.getElementById('analysisResults').style.display = 'block';
    
    // Update comparison cards
    document.getElementById('currentMajor').textContent = data.current.major;
    document.getElementById('currentCredits').textContent = data.current.credits_remaining;
    document.getElementById('currentTerms').textContent = data.current.terms_remaining;
    document.getElementById('currentCompletion').textContent = data.current.completion + '%';
    
    document.getElementById('whatIfMajor').textContent = data.whatif.major;
    document.getElementById('whatIfCredits').textContent = data.whatif.credits_remaining;
    document.getElementById('whatIfTerms').textContent = data.whatif.terms_remaining;
    document.getElementById('whatIfCompletion').textContent = data.whatif.completion + '%';
    
    // Populate comparison table
    let tableHtml = '';
    data.comparison.forEach(item => {
        const impactClass = item.impact === 'positive' ? 'better' : 
                           item.impact === 'negative' ? 'worse' : 'same';
        tableHtml += `
            <tr>
                <td>${item.requirement}</td>
                <td>${item.current}</td>
                <td>${item.whatif}</td>
                <td class="${impactClass}">${item.impact_text}</td>
            </tr>
        `;
    });
    document.getElementById('comparisonTableBody').innerHTML = tableHtml;
    
    // Populate recommendations
    let recsHtml = '';
    data.recommendations.forEach(rec => {
        recsHtml += `<li>${rec}</li>`;
    });
    document.getElementById('recommendations').innerHTML = recsHtml;
    
    // Scroll to results
    document.getElementById('analysisResults').scrollIntoView({ behavior: 'smooth' });
}

function saveScenario() {
    const name = document.getElementById('scenarioName').value;
    const notes = document.getElementById('scenarioNotes').value;
    
    // Save scenario logic here
    alert('Scenario saved successfully!');
    $('#saveScenarioModal').modal('hide');
}

function loadScenario(scenarioId) {
    // Load saved scenario
    console.log('Loading scenario:', scenarioId);
}

function resetForm() {
    document.getElementById('whatIfForm').reset();
    document.getElementById('analysisResults').style.display = 'none';
}
</script>
@endsection