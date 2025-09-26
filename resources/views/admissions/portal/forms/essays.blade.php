{{-- resources/views/admissions/portal/forms/essays.blade.php --}}
<div class="essays-section">
    <h4 class="mb-4"><i class="fas fa-pen-fancy me-2"></i>Essays & Personal Statements</h4>
    
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        Please write thoughtful, original essays. Word limits are strictly enforced.
    </div>
    
    @foreach($requirements['essay_prompts'] ?? ['personal_statement'] as $prompt)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    @switch($prompt)
                        @case('personal_statement')
                            Personal Statement
                            @break
                        @case('statement_of_purpose')
                            Statement of Purpose
                            @break
                        @case('research_interests')
                            Research Interests
                            @break
                        @case('transfer_essay')
                            Why Transfer Essay
                            @break
                        @case('why_international')
                            Why Study Abroad Essay
                            @break
                        @default
                            {{ ucwords(str_replace('_', ' ', $prompt)) }}
                    @endswitch
                    <span class="text-danger">*</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">
                        @switch($prompt)
                            @case('personal_statement')
                                Tell us about yourself, your background, and what makes you unique. (250-500 words)
                                @break
                            @case('statement_of_purpose')
                                Describe your academic and career goals. Why have you chosen this field of study? (500-1000 words)
                                @break
                            @case('research_interests')
                                Describe your research interests and how they align with our program. (300-500 words)
                                @break
                            @case('transfer_essay')
                                Why do you want to transfer? What do you hope to achieve at our institution? (250-500 words)
                                @break
                            @default
                                Respond to the prompt above.
                        @endswitch
                    </small>
                </div>
                
                <textarea class="form-control" 
                          id="{{ $prompt }}" 
                          name="{{ $prompt }}"
                          rows="10"
                          maxlength="5000"
                          required>{{ old($prompt, $application->$prompt) }}</textarea>
                          
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">
                        <span id="{{ $prompt }}_count">0</span> words
                    </small>
                    <small class="text-muted">
                        Min: {{ $prompt === 'statement_of_purpose' ? '500' : '250' }} | 
                        Max: {{ $prompt === 'statement_of_purpose' ? '1000' : '500' }} words
                    </small>
                </div>
            </div>
        </div>
        
        <script>
        document.getElementById('{{ $prompt }}').addEventListener('input', function() {
            const words = this.value.trim().split(/\s+/).filter(word => word.length > 0).length;
            document.getElementById('{{ $prompt }}_count').textContent = words;
        });
        // Trigger on load
        document.getElementById('{{ $prompt }}').dispatchEvent(new Event('input'));
        </script>
    @endforeach
    
    @if(isset($requirements['additional_requirements']) && in_array('writing_sample', $requirements['additional_requirements']))
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Writing Sample (Optional)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">You may upload a writing sample (academic paper, article, etc.)</p>
                <input type="file" class="form-control" name="writing_sample" accept=".pdf,.doc,.docx">
            </div>
        </div>
    @endif
</div>