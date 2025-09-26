{{-- resources/views/admissions/portal/forms/activities.blade.php --}}
<div class="activities-section">
    <h4 class="mb-4"><i class="fas fa-trophy me-2"></i>Extracurricular Activities & Awards</h4>
    
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        List your most significant activities, leadership positions, and awards. Focus on quality over quantity.
    </div>
    
    {{-- Extracurricular Activities --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Extracurricular Activities</h5>
        </div>
        <div class="card-body">
            @for($i = 1; $i <= 5; $i++)
                <div class="activity-item mb-4 pb-3 border-bottom">
                    <h6>Activity {{ $i }}</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Activity Name</label>
                            <input type="text" class="form-control" 
                                   name="activity_{{ $i }}_name"
                                   placeholder="e.g., Student Government, Soccer Team"
                                   value="{{ old("activity_{$i}_name", $application->extracurricular_activities[$i-1]['name'] ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position/Role</label>
                            <input type="text" class="form-control" 
                                   name="activity_{{ $i }}_position"
                                   placeholder="e.g., President, Team Captain"
                                   value="{{ old("activity_{$i}_position", $application->extracurricular_activities[$i-1]['position'] ?? '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Years Participated</label>
                            <input type="text" class="form-control" 
                                   name="activity_{{ $i }}_years"
                                   placeholder="e.g., 2020-2023"
                                   value="{{ old("activity_{$i}_years", $application->extracurricular_activities[$i-1]['years'] ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Hours per Week</label>
                            <input type="number" class="form-control" 
                                   name="activity_{{ $i }}_hours"
                                   min="0" max="168"
                                   value="{{ old("activity_{$i}_hours", $application->extracurricular_activities[$i-1]['hours'] ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" 
                                      name="activity_{{ $i }}_description"
                                      rows="2"
                                      placeholder="Brief description of your role and achievements">{{ old("activity_{$i}_description", $application->extracurricular_activities[$i-1]['description'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
    
    {{-- Awards and Honors --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Awards & Honors</h5>
        </div>
        <div class="card-body">
            @for($i = 1; $i <= 3; $i++)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Award {{ $i }}</label>
                        <input type="text" class="form-control" 
                               name="award_{{ $i }}_name"
                               placeholder="Award name"
                               value="{{ old("award_{$i}_name", $application->awards_honors[$i-1]['name'] ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <input type="text" class="form-control" 
                               name="award_{{ $i }}_year"
                               placeholder="Year received"
                               value="{{ old("award_{$i}_year", $application->awards_honors[$i-1]['year'] ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Level</label>
                        <select class="form-select" name="award_{{ $i }}_level">
                            <option value="">Select...</option>
                            <option value="school">School</option>
                            <option value="district">District/Regional</option>
                            <option value="state">State/Provincial</option>
                            <option value="national">National</option>
                            <option value="international">International</option>
                        </select>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>