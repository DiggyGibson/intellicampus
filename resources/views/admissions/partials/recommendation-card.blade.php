{{-- resources/views/admissions/portal/forms/partials/recommendation-card.blade.php --}}
<div class="recommendation-card" id="recommendation-{{ $index }}">
    <span class="recommendation-number">Recommender {{ $index + 1 }}</span>
    @if($canRemove)
        <button type="button" class="btn btn-sm btn-outline-danger remove-recommendation" 
                onclick="removeRecommendation({{ $index }})">
            <i class="fas fa-times"></i>
        </button>
    @endif
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Full Name <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('recommendations.'.$index.'.name') is-invalid @enderror" 
                   name="recommendations[{{ $index }}][name]" 
                   value="{{ old('recommendations.'.$index.'.name', $recommendation['name'] ?? '') }}"
                   placeholder="Dr. Jane Smith"
                   required>
            @error('recommendations.'.$index.'.name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Title/Position <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('recommendations.'.$index.'.title') is-invalid @enderror" 
                   name="recommendations[{{ $index }}][title]" 
                   value="{{ old('recommendations.'.$index.'.title', $recommendation['title'] ?? '') }}"
                   placeholder="Professor of Mathematics"
                   required>
            @error('recommendations.'.$index.'.title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">
                Institution/Organization <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('recommendations.'.$index.'.institution') is-invalid @enderror" 
                   name="recommendations[{{ $index }}][institution]" 
                   value="{{ old('recommendations.'.$index.'.institution', $recommendation['institution'] ?? '') }}"
                   placeholder="University Name or Company"
                   required>
            @error('recommendations.'.$index.'.institution')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Email Address <span class="text-danger">*</span>
                <i class="fas fa-info-circle info-tooltip ms-1" 
                   data-bs-toggle="tooltip" 
                   title="Use official institution email when possible"></i>
            </label>
            <input type="email" 
                   class="form-control @error('recommendations.'.$index.'.email') is-invalid @enderror" 
                   name="recommendations[{{ $index }}][email]" 
                   value="{{ old('recommendations.'.$index.'.email', $recommendation['email'] ?? '') }}"
                   placeholder="professor@university.edu"
                   required>
            @error('recommendations.'.$index.'.email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Phone Number
            </label>
            <input type="tel" 
                   class="form-control @error('recommendations.'.$index.'.phone') is-invalid @enderror" 
                   name="recommendations[{{ $index }}][phone]" 
                   value="{{ old('recommendations.'.$index.'.phone', $recommendation['phone'] ?? '') }}"
                   placeholder="+1 (555) 123-4567">
            @error('recommendations.'.$index.'.phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">
                Relationship to Applicant <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('recommendations.'.$index.'.relationship') is-invalid @enderror" 
                   name="recommendations[{{ $index }}][relationship]" 
                   value="{{ old('recommendations.'.$index.'.relationship', $recommendation['relationship'] ?? '') }}"
                   placeholder="e.g., Math Teacher (2 years), Research Supervisor, Direct Manager"
                   required>
            <div class="relationship-examples">
                Examples: Teacher, Professor, Academic Advisor, Employer, Supervisor, Coach, Mentor
            </div>
            @error('recommendations.'.$index.'.relationship')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>