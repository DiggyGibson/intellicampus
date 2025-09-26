{{-- resources/views/admissions/portal/forms/recommendations.blade.php --}}
<div class="recommendations-section">
    <h4 class="mb-4"><i class="fas fa-user-friends me-2"></i>Letters of Recommendation</h4>
    
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        You need <strong>{{ $requirements['min_recommendations'] ?? 2 }}</strong> recommendation letters.
        Enter your recommenders' information and we'll email them instructions.
    </div>
    
    @for($i = 1; $i <= ($requirements['min_recommendations'] ?? 2); $i++)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Recommender {{ $i }} <span class="text-danger">*</span></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" 
                               name="recommender_{{ $i }}_name"
                               value="{{ old("recommender_{$i}_name", $application->references[$i-1]['name'] ?? '') }}"
                               required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title/Position</label>
                        <input type="text" class="form-control" 
                               name="recommender_{{ $i }}_title"
                               value="{{ old("recommender_{$i}_title", $application->references[$i-1]['title'] ?? '') }}"
                               required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" 
                               name="recommender_{{ $i }}_email"
                               value="{{ old("recommender_{$i}_email", $application->references[$i-1]['email'] ?? '') }}"
                               required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Relationship</label>
                        <select class="form-select" name="recommender_{{ $i }}_relationship" required>
                            <option value="">Select...</option>
                            <option value="teacher">Teacher/Professor</option>
                            <option value="employer">Employer/Supervisor</option>
                            <option value="counselor">Counselor/Advisor</option>
                            <option value="mentor">Mentor</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                
                @if(isset($application->references[$i-1]['status']))
                    <div class="alert alert-{{ $application->references[$i-1]['status'] == 'received' ? 'success' : 'warning' }}">
                        Status: {{ ucfirst($application->references[$i-1]['status'] ?? 'Not sent') }}
                    </div>
                @endif
            </div>
        </div>
    @endfor
    
    {{-- Optional Additional Recommenders --}}
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">Additional Recommender (Optional)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="additional_recommender_name">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="additional_recommender_email">
                </div>
            </div>
        </div>
    </div>
</div>