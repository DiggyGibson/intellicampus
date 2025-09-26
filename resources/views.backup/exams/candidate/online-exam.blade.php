@extends('layouts.app')

@section('title', 'Online Exam - ' . $registration->exam->exam_name)

@section('content')
<div id="examInterface" class="min-h-screen bg-gray-100">
    {{-- Exam Header --}}
    <div class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3">
                {{-- Left: Exam Info --}}
                <div class="flex items-center space-x-4">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">{{ $registration->exam->exam_name }}</h1>
                        <p class="text-sm text-gray-500">Registration: {{ $registration->registration_number }}</p>
                    </div>
                </div>

                {{-- Center: Timer --}}
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div id="examTimer" class="text-lg font-bold text-gray-900">
                        <span id="hours">00</span>:<span id="minutes">00</span>:<span id="seconds">00</span>
                    </div>
                    <span class="text-sm text-gray-500">remaining</span>
                </div>

                {{-- Right: Actions --}}
                <div class="flex items-center space-x-3">
                    <button onclick="toggleCalculator()" 
                            class="p-2 text-gray-400 hover:text-gray-600 {{ !in_array('calculator', json_decode($registration->exam->allowed_materials ?? '[]')) ? 'hidden' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </button>
                    <button onclick="toggleInstructions()" class="p-2 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <button onclick="confirmSubmit()" 
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Submit Exam
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="flex h-screen pt-16">
        {{-- Left Sidebar: Question Navigation --}}
        <div class="w-64 bg-white border-r border-gray-200 overflow-y-auto">
            <div class="p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Question Navigation</h3>
                
                {{-- Question Status Legend --}}
                <div class="mb-4 space-y-2 text-xs">
                    <div class="flex items-center">
                        <span class="w-6 h-6 bg-green-500 rounded mr-2"></span>
                        <span>Answered</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-6 h-6 bg-yellow-500 rounded mr-2"></span>
                        <span>Marked for Review</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-6 h-6 bg-gray-300 rounded mr-2"></span>
                        <span>Not Visited</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-6 h-6 bg-white border-2 border-gray-300 rounded mr-2"></span>
                        <span>Not Answered</span>
                    </div>
                </div>

                {{-- Section Tabs --}}
                @if($examPaper->sections)
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-2">
                            @foreach($examPaper->sections as $index => $section)
                                <button onclick="switchSection({{ $index }})" 
                                        class="section-tab py-2 px-3 text-xs font-medium rounded-t-lg {{ $index === 0 ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}"
                                        data-section="{{ $index }}">
                                    {{ $section->name }}
                                </button>
                            @endforeach
                        </nav>
                    </div>
                @endif

                {{-- Question Grid --}}
                <div id="questionGrid" class="grid grid-cols-5 gap-2">
                    @for($i = 1; $i <= $examPaper->total_questions; $i++)
                        <button onclick="goToQuestion({{ $i }})" 
                                id="qBtn{{ $i }}"
                                class="question-btn w-10 h-10 flex items-center justify-center text-sm font-medium rounded border-2 
                                       {{ $i === 1 ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-gray-300' }} 
                                       hover:border-indigo-400"
                                data-question="{{ $i }}"
                                data-status="not_visited">
                            {{ $i }}
                        </button>
                    @endfor
                </div>

                {{-- Section Summary --}}
                <div class="mt-6 p-3 bg-gray-50 rounded-lg">
                    <h4 class="text-xs font-medium text-gray-700 mb-2">Section Summary</h4>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span>Answered:</span>
                            <span id="answeredCount" class="font-medium">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Not Answered:</span>
                            <span id="notAnsweredCount" class="font-medium">{{ $examPaper->total_questions }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Marked for Review:</span>
                            <span id="markedCount" class="font-medium">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Not Visited:</span>
                            <span id="notVisitedCount" class="font-medium">{{ $examPaper->total_questions }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content: Question Display --}}
        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <form id="examForm" method="POST" action="{{ route('exams.submit', $registration->id) }}">
                    @csrf
                    <input type="hidden" name="response_id" value="{{ $response->id }}">
                    
                    {{-- Question Container --}}
                    <div id="questionContainer" class="bg-white rounded-lg shadow-sm p-6">
                        {{-- Question will be loaded here dynamically --}}
                    </div>

                    {{-- Navigation Buttons --}}
                    <div class="mt-6 flex justify-between items-center">
                        <div class="flex space-x-3">
                            <button type="button" 
                                    onclick="clearResponse()"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Clear Response
                            </button>
                            <button type="button" 
                                    onclick="markForReview()"
                                    class="px-4 py-2 border border-yellow-300 rounded-md text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100">
                                Mark for Review
                            </button>
                        </div>

                        <div class="flex space-x-3">
                            <button type="button" 
                                    id="prevBtn"
                                    onclick="previousQuestion()"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                ← Previous
                            </button>
                            <button type="button" 
                                    onclick="saveAndNext()"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                Save & Next →
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Instructions Modal --}}
    <div id="instructionsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white rounded-lg max-w-2xl w-full">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Exam Instructions</h3>
                </div>
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <div class="prose text-sm">
                        {!! nl2br(e($registration->exam->general_instructions)) !!}
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button onclick="toggleInstructions()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Calculator Modal --}}
    <div id="calculatorModal" class="fixed bottom-4 right-4 z-40 hidden">
        <div class="bg-white rounded-lg shadow-lg p-4 w-64">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-sm font-medium">Calculator</h4>
                <button onclick="toggleCalculator()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <input type="text" id="calcDisplay" class="w-full p-2 mb-3 text-right bg-gray-100 rounded" readonly>
            <div class="grid grid-cols-4 gap-2">
                <button onclick="appendToCalc('7')" class="calc-btn">7</button>
                <button onclick="appendToCalc('8')" class="calc-btn">8</button>
                <button onclick="appendToCalc('9')" class="calc-btn">9</button>
                <button onclick="appendToCalc('/')" class="calc-btn bg-gray-200">/</button>
                
                <button onclick="appendToCalc('4')" class="calc-btn">4</button>
                <button onclick="appendToCalc('5')" class="calc-btn">5</button>
                <button onclick="appendToCalc('6')" class="calc-btn">6</button>
                <button onclick="appendToCalc('*')" class="calc-btn bg-gray-200">×</button>
                
                <button onclick="appendToCalc('1')" class="calc-btn">1</button>
                <button onclick="appendToCalc('2')" class="calc-btn">2</button>
                <button onclick="appendToCalc('3')" class="calc-btn">3</button>
                <button onclick="appendToCalc('-')" class="calc-btn bg-gray-200">-</button>
                
                <button onclick="appendToCalc('0')" class="calc-btn">0</button>
                <button onclick="appendToCalc('.')" class="calc-btn">.</button>
                <button onclick="calculateResult()" class="calc-btn bg-indigo-600 text-white">=</button>
                <button onclick="appendToCalc('+')" class="calc-btn bg-gray-200">+</button>
                
                <button onclick="clearCalc()" class="calc-btn col-span-2 bg-red-500 text-white">Clear</button>
                <button onclick="backspaceCalc()" class="calc-btn col-span-2 bg-yellow-500 text-white">←</button>
            </div>
        </div>
    </div>

    {{-- Warning Modal for Tab Switch/Copy/Paste --}}
    <div id="warningModal" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="fixed inset-0 bg-red-500 bg-opacity-75"></div>
            <div class="relative bg-white rounded-lg p-6 max-w-md">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-red-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Warning!</h3>
                    <p id="warningMessage" class="text-sm text-gray-600 mb-4">Your action has been recorded.</p>
                    <button onclick="closeWarning()" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        I Understand
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Prevent text selection during exam */
    #examInterface {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    
    /* Allow selection only in answer input areas */
    .answer-input {
        -webkit-user-select: text;
        -moz-user-select: text;
        -ms-user-select: text;
        user-select: text;
    }
    
    /* Calculator button styles */
    .calc-btn {
        @apply p-2 text-sm font-medium rounded hover:bg-gray-100 border border-gray-200;
    }
    
    /* Disable right-click */
    #examInterface {
        oncontextmenu: return false;
    }
</style>
@endpush

@push('scripts')
<script>
    // Exam Questions Data (loaded from server)
    const examQuestions = @json($examQuestions);
    const totalQuestions = {{ $examPaper->total_questions }};
    const examDuration = {{ $registration->exam->duration_minutes }} * 60; // Convert to seconds
    
    let currentQuestion = 1;
    let responses = {};
    let questionStatus = {};
    let timeRemaining = examDuration;
    let timerInterval;
    let violations = 0;
    
    // Initialize exam
    document.addEventListener('DOMContentLoaded', function() {
        initializeExam();
        startTimer();
        loadQuestion(1);
        preventCheating();
    });
    
    function initializeExam() {
        // Initialize all questions as not visited
        for (let i = 1; i <= totalQuestions; i++) {
            questionStatus[i] = 'not_visited';
        }
        
        // Load saved responses if any
        const savedResponses = localStorage.getItem('exam_responses_' + {{ $response->id }});
        if (savedResponses) {
            responses = JSON.parse(savedResponses);
            // Update question status based on saved responses
            Object.keys(responses).forEach(qNum => {
                if (responses[qNum].answer) {
                    questionStatus[qNum] = responses[qNum].marked ? 'marked_review' : 'answered';
                }
            });
            updateQuestionGrid();
        }
        
        // Auto-save every 30 seconds
        setInterval(autoSave, 30000);
    }
    
    function startTimer() {
        timerInterval = setInterval(function() {
            timeRemaining--;
            
            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;
            
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
            
            // Warning at 5 minutes
            if (timeRemaining === 300) {
                alert('Warning: Only 5 minutes remaining!');
            }
            
            // Auto-submit when time is up
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                autoSubmit();
            }
        }, 1000);
    }
    
    function loadQuestion(questionNumber) {
        currentQuestion = questionNumber;
        const question = examQuestions[questionNumber - 1];
        
        // Mark as visited
        if (questionStatus[questionNumber] === 'not_visited') {
            questionStatus[questionNumber] = 'not_answered';
        }
        
        // Build question HTML
        let questionHTML = `
            <div class="mb-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Question ${questionNumber} of ${totalQuestions}</h3>
                    <span class="px-3 py-1 text-sm bg-gray-100 rounded-full">${question.marks} marks</span>
                </div>
                <div class="prose max-w-none mb-6">
                    ${question.question_text}
                </div>
        `;
        
        // Add question image if exists
        if (question.question_image) {
            questionHTML += `<img src="${question.question_image}" class="mb-4 max-w-full rounded-lg">`;
        }
        
        // Add answer options based on question type
        if (question.question_type === 'multiple_choice') {
            questionHTML += '<div class="space-y-3">';
            Object.entries(question.options).forEach(([key, value]) => {
                const isChecked = responses[questionNumber]?.answer === key;
                questionHTML += `
                    <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" 
                               name="answer_${questionNumber}" 
                               value="${key}"
                               ${isChecked ? 'checked' : ''}
                               onchange="saveAnswer(${questionNumber}, '${key}')"
                               class="mt-1 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3">${key.toUpperCase()}. ${value}</span>
                    </label>
                `;
            });
            questionHTML += '</div>';
        } else if (question.question_type === 'multiple_answer') {
            questionHTML += '<div class="space-y-3">';
            questionHTML += '<p class="text-sm text-gray-600 mb-2">Select all that apply:</p>';
            Object.entries(question.options).forEach(([key, value]) => {
                const answers = responses[questionNumber]?.answer || [];
                const isChecked = Array.isArray(answers) && answers.includes(key);
                questionHTML += `
                    <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" 
                               name="answer_${questionNumber}_${key}" 
                               value="${key}"
                               ${isChecked ? 'checked' : ''}
                               onchange="saveMultipleAnswer(${questionNumber}, '${key}')"
                               class="mt-1 text-indigo-600 focus:ring-indigo-500 rounded">
                        <span class="ml-3">${key.toUpperCase()}. ${value}</span>
                    </label>
                `;
            });
            questionHTML += '</div>';
        } else if (question.question_type === 'true_false') {
            const isTrue = responses[questionNumber]?.answer === 'true';
            const isFalse = responses[questionNumber]?.answer === 'false';
            questionHTML += `
                <div class="space-y-3">
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" 
                               name="answer_${questionNumber}" 
                               value="true"
                               ${isTrue ? 'checked' : ''}
                               onchange="saveAnswer(${questionNumber}, 'true')"
                               class="text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3">True</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" 
                               name="answer_${questionNumber}" 
                               value="false"
                               ${isFalse ? 'checked' : ''}
                               onchange="saveAnswer(${questionNumber}, 'false')"
                               class="text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3">False</span>
                    </label>
                </div>
            `;
        } else if (question.question_type === 'numerical') {
            const savedAnswer = responses[questionNumber]?.answer || '';
            questionHTML += `
                <div class="max-w-xs">
                    <input type="number" 
                           id="answer_${questionNumber}"
                           value="${savedAnswer}"
                           onchange="saveAnswer(${questionNumber}, this.value)"
                           class="answer-input w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Enter your answer">
                </div>
            `;
        } else if (question.question_type === 'short_answer') {
            const savedAnswer = responses[questionNumber]?.answer || '';
            questionHTML += `
                <div>
                    <textarea id="answer_${questionNumber}"
                              rows="4"
                              onchange="saveAnswer(${questionNumber}, this.value)"
                              class="answer-input w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Type your answer here...">${savedAnswer}</textarea>
                </div>
            `;
        }
        
        questionHTML += '</div>';
        
        document.getElementById('questionContainer').innerHTML = questionHTML;
        updateQuestionGrid();
        updateNavigationButtons();
    }
    
    function saveAnswer(questionNumber, answer) {
        responses[questionNumber] = {
            answer: answer,
            marked: responses[questionNumber]?.marked || false,
            time_spent: responses[questionNumber]?.time_spent || 0
        };
        
        if (answer) {
            questionStatus[questionNumber] = responses[questionNumber].marked ? 'marked_review' : 'answered';
        } else {
            questionStatus[questionNumber] = 'not_answered';
        }
        
        updateQuestionGrid();
        autoSave();
    }
    
    function saveMultipleAnswer(questionNumber, option) {
        if (!responses[questionNumber]) {
            responses[questionNumber] = { answer: [], marked: false };
        }
        
        const answers = responses[questionNumber].answer || [];
        const index = answers.indexOf(option);
        
        if (index > -1) {
            answers.splice(index, 1);
        } else {
            answers.push(option);
        }
        
        responses[questionNumber].answer = answers;
        questionStatus[questionNumber] = answers.length > 0 ? 
            (responses[questionNumber].marked ? 'marked_review' : 'answered') : 'not_answered';
        
        updateQuestionGrid();
        autoSave();
    }
    
    function clearResponse() {
        const questionNumber = currentQuestion;
        
        // Clear the answer
        delete responses[questionNumber];
        questionStatus[questionNumber] = 'not_answered';
        
        // Reload the question to clear form inputs
        loadQuestion(questionNumber);
    }
    
    function markForReview() {
        const questionNumber = currentQuestion;
        
        if (!responses[questionNumber]) {
            responses[questionNumber] = { answer: null, marked: true };
        } else {
            responses[questionNumber].marked = !responses[questionNumber].marked;
        }
        
        if (responses[questionNumber].marked) {
            questionStatus[questionNumber] = 'marked_review';
        } else if (responses[questionNumber].answer) {
            questionStatus[questionNumber] = 'answered';
        } else {
            questionStatus[questionNumber] = 'not_answered';
        }
        
        updateQuestionGrid();
    }
    
    function saveAndNext() {
        if (currentQuestion < totalQuestions) {
            loadQuestion(currentQuestion + 1);
        } else {
            // If on last question, show submit confirmation
            confirmSubmit();
        }
    }
    
    function previousQuestion() {
        if (currentQuestion > 1) {
            loadQuestion(currentQuestion - 1);
        }
    }
    
    function goToQuestion(questionNumber) {
        loadQuestion(questionNumber);
    }
    
    function updateQuestionGrid() {
        for (let i = 1; i <= totalQuestions; i++) {
            const btn = document.getElementById(`qBtn${i}`);
            const status = questionStatus[i];
            
            // Remove all status classes
            btn.className = btn.className.replace(/bg-\S+/g, '').replace(/border-\S+/g, '');
            
            // Add appropriate classes based on status
            if (i === currentQuestion) {
                btn.classList.add('border-indigo-500', 'bg-indigo-50', 'border-2');
            } else {
                switch (status) {
                    case 'answered':
                        btn.classList.add('bg-green-500', 'text-white', 'border-green-500');
                        break;
                    case 'marked_review':
                        btn.classList.add('bg-yellow-500', 'text-white', 'border-yellow-500');
                        break;
                    case 'not_answered':
                        btn.classList.add('bg-white', 'border-gray-300', 'border-2');
                        break;
                    case 'not_visited':
                        btn.classList.add('bg-gray-300', 'border-gray-300');
                        break;
                }
            }
        }
        
        // Update summary counts
        const counts = {
            answered: 0,
            not_answered: 0,
            marked_review: 0,
            not_visited: 0
        };
        
        Object.values(questionStatus).forEach(status => {
            if (status === 'answered') counts.answered++;
            else if (status === 'marked_review') counts.marked_review++;
            else if (status === 'not_answered') counts.not_answered++;
            else if (status === 'not_visited') counts.not_visited++;
        });
        
        document.getElementById('answeredCount').textContent = counts.answered;
        document.getElementById('notAnsweredCount').textContent = counts.not_answered;
        document.getElementById('markedCount').textContent = counts.marked_review;
        document.getElementById('notVisitedCount').textContent = counts.not_visited;
    }
    
    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevBtn');
        prevBtn.disabled = currentQuestion === 1;
        prevBtn.classList.toggle('opacity-50', currentQuestion === 1);
        prevBtn.classList.toggle('cursor-not-allowed', currentQuestion === 1);
    }
    
    function autoSave() {
        // Save to localStorage
        localStorage.setItem('exam_responses_' + {{ $response->id }}, JSON.stringify(responses));
        
        // Save to server
        fetch('{{ route("exams.auto-save", $response->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                responses: responses,
                current_question: currentQuestion,
                time_spent: examDuration - timeRemaining
            })
        });
    }
    
    function confirmSubmit() {
        // Check for unanswered questions
        const unanswered = [];
        const markedForReview = [];
        
        for (let i = 1; i <= totalQuestions; i++) {
            if (questionStatus[i] === 'not_answered' || questionStatus[i] === 'not_visited') {
                unanswered.push(i);
            } else if (questionStatus[i] === 'marked_review') {
                markedForReview.push(i);
            }
        }
        
        let confirmMessage = 'Are you sure you want to submit your exam?\n\n';
        
        if (unanswered.length > 0) {
            confirmMessage += `⚠️ You have ${unanswered.length} unanswered questions: ${unanswered.slice(0, 10).join(', ')}${unanswered.length > 10 ? '...' : ''}\n\n`;
        }
        
        if (markedForReview.length > 0) {
            confirmMessage += `📌 You have ${markedForReview.length} questions marked for review: ${markedForReview.slice(0, 10).join(', ')}${markedForReview.length > 10 ? '...' : ''}\n\n`;
        }
        
        confirmMessage += 'Once submitted, you cannot make any changes.';
        
        if (confirm(confirmMessage)) {
            submitExam();
        }
    }
    
    function submitExam() {
        clearInterval(timerInterval);
        
        // Prepare submission data
        const submissionData = {
            responses: responses,
            time_spent: examDuration - timeRemaining,
            submitted_at: new Date().toISOString(),
            question_status: questionStatus
        };
        
        // Show loading overlay
        document.body.innerHTML += `
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 text-center">
                    <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-lg font-medium">Submitting your exam...</p>
                    <p class="text-sm text-gray-500 mt-2">Please do not close this window.</p>
                </div>
            </div>
        `;
        
        // Submit the exam
        fetch('{{ route("exams.submit", $response->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(submissionData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear localStorage
                localStorage.removeItem('exam_responses_' + {{ $response->id }});
                
                // Redirect to completion page
                window.location.href = data.redirect_url || '{{ route("exams.completed", $registration->id) }}';
            } else {
                alert('Error submitting exam. Please try again.');
            }
        })
        .catch(error => {
            alert('Network error. Your responses have been saved. Please contact support.');
            console.error('Submission error:', error);
        });
    }
    
    function autoSubmit() {
        alert('Time is up! Your exam will be submitted automatically.');
        submitExam();
    }
    
    // Prevent cheating functions
    function preventCheating() {
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            recordViolation('right_click');
            return false;
        });
        
        // Detect tab switching
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                recordViolation('tab_switch');
                showWarning('Tab switching detected! This action has been recorded.');
            }
        });
        
        // Detect copy attempts
        document.addEventListener('copy', function(e) {
            e.preventDefault();
            recordViolation('copy_attempt');
            showWarning('Copying is not allowed during the exam!');
            return false;
        });
        
        // Detect paste attempts
        document.addEventListener('paste', function(e) {
            e.preventDefault();
            recordViolation('paste_attempt');
            showWarning('Pasting is not allowed during the exam!');
            return false;
        });
        
        // Detect print attempts
        window.addEventListener('beforeprint', function(e) {
            e.preventDefault();
            recordViolation('print_attempt');
            showWarning('Printing is not allowed during the exam!');
            return false;
        });
        
        // Detect developer tools (basic detection)
        let devtools = {open: false, orientation: null};
        const threshold = 160;
        
        setInterval(function() {
            if (window.outerHeight - window.innerHeight > threshold || 
                window.outerWidth - window.innerWidth > threshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    recordViolation('devtools_open');
                    showWarning('Developer tools detected! This has been recorded.');
                }
            } else {
                devtools.open = false;
            }
        }, 500);
        
        // Prevent F12 and other shortcuts
        document.addEventListener('keydown', function(e) {
            // F12
            if (e.keyCode === 123) {
                e.preventDefault();
                recordViolation('f12_pressed');
                return false;
            }
            // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
                e.preventDefault();
                recordViolation('devtools_shortcut');
                return false;
            }
            // Ctrl+U (View Source)
            if (e.ctrlKey && e.keyCode === 85) {
                e.preventDefault();
                recordViolation('view_source_attempt');
                return false;
            }
        });
    }
    
    function recordViolation(type) {
        violations++;
        
        // Log violation to server
        fetch('{{ route("exams.log-violation", $response->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                violation_type: type,
                question_number: currentQuestion,
                timestamp: new Date().toISOString(),
                violation_count: violations
            })
        });
        
        // Auto-terminate after 5 violations
        if (violations >= 5) {
            alert('Multiple violations detected. Your exam has been terminated.');
            submitExam();
        }
    }
    
    function showWarning(message) {
        document.getElementById('warningMessage').textContent = message;
        document.getElementById('warningModal').classList.remove('hidden');
    }
    
    function closeWarning() {
        document.getElementById('warningModal').classList.add('hidden');
    }
    
    // Calculator functions
    function toggleCalculator() {
        document.getElementById('calculatorModal').classList.toggle('hidden');
    }
    
    function appendToCalc(value) {
        document.getElementById('calcDisplay').value += value;
    }
    
    function clearCalc() {
        document.getElementById('calcDisplay').value = '';
    }
    
    function backspaceCalc() {
        const display = document.getElementById('calcDisplay');
        display.value = display.value.slice(0, -1);
    }
    
    function calculateResult() {
        try {
            const display = document.getElementById('calcDisplay');
            display.value = eval(display.value);
        } catch (e) {
            document.getElementById('calcDisplay').value = 'Error';
        }
    }
    
    function toggleInstructions() {
        document.getElementById('instructionsModal').classList.toggle('hidden');
    }
    
    function switchSection(sectionIndex) {
        // Update section tab styling
        document.querySelectorAll('.section-tab').forEach(tab => {
            tab.classList.remove('bg-indigo-50', 'text-indigo-600', 'border-b-2', 'border-indigo-600');
            tab.classList.add('text-gray-500');
        });
        
        const activeTab = document.querySelector(`[data-section="${sectionIndex}"]`);
        activeTab.classList.add('bg-indigo-50', 'text-indigo-600', 'border-b-2', 'border-indigo-600');
        activeTab.classList.remove('text-gray-500');
        
        // Load first question of the section
        // This would need section-to-question mapping from server
        // For now, just a placeholder
        console.log('Switching to section:', sectionIndex);
    }
    
    // Fullscreen mode for better exam experience
    function enterFullscreen() {
        const elem = document.documentElement;
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    }
    
    // Detect fullscreen exit
    document.addEventListener('fullscreenchange', function() {
        if (!document.fullscreenElement) {
            recordViolation('fullscreen_exit');
            showWarning('Exiting fullscreen mode is not allowed during the exam!');
            // Try to re-enter fullscreen
            setTimeout(enterFullscreen, 1000);
        }
    });
    
    // Optional: Enter fullscreen when exam starts
    // enterFullscreen();
</script>
@endpush
@endsection