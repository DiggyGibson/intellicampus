@extends('layouts.app')

@section('title', 'Available Entrance Exams')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Available Entrance Exams</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Register for entrance examinations for admission to {{ config('app.name') }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('exams.my-registrations') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            My Registrations
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('exams.available') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="exam_type" class="block text-sm font-medium text-gray-700 mb-1">
                                Exam Type
                            </label>
                            <select name="exam_type" id="exam_type" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Types</option>
                                <option value="entrance" {{ request('exam_type') == 'entrance' ? 'selected' : '' }}>
                                    Entrance Exam
                                </option>
                                <option value="placement" {{ request('exam_type') == 'placement' ? 'selected' : '' }}>
                                    Placement Test
                                </option>
                                <option value="scholarship" {{ request('exam_type') == 'scholarship' ? 'selected' : '' }}>
                                    Scholarship Exam
                                </option>
                                <option value="diagnostic" {{ request('exam_type') == 'diagnostic' ? 'selected' : '' }}>
                                    Diagnostic Test
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="program" class="block text-sm font-medium text-gray-700 mb-1">
                                Program
                            </label>
                            <select name="program" id="program" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" 
                                            {{ request('program') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="delivery_mode" class="block text-sm font-medium text-gray-700 mb-1">
                                Delivery Mode
                            </label>
                            <select name="delivery_mode" id="delivery_mode" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Modes</option>
                                <option value="paper_based" {{ request('delivery_mode') == 'paper_based' ? 'selected' : '' }}>
                                    Paper Based
                                </option>
                                <option value="computer_based" {{ request('delivery_mode') == 'computer_based' ? 'selected' : '' }}>
                                    Computer Based (CBT)
                                </option>
                                <option value="online_proctored" {{ request('delivery_mode') == 'online_proctored' ? 'selected' : '' }}>
                                    Online Proctored
                                </option>
                                <option value="online_unproctored" {{ request('delivery_mode') == 'online_unproctored' ? 'selected' : '' }}>
                                    Online Unproctored
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <a href="{{ route('exams.available') }}" 
                           class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Clear Filters
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Available Exams List --}}
        @if($exams->isEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No exams available</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        There are no entrance exams available for registration at this time.
                    </p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach($exams as $exam)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    {{-- Exam Header --}}
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $exam->exam_name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Code: {{ $exam->exam_code }} | Type: {{ ucfirst(str_replace('_', ' ', $exam->exam_type)) }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($exam->status == 'registration_open') bg-green-100 text-green-800
                                            @elseif($exam->status == 'registration_closed') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                                        </span>
                                    </div>

                                    {{-- Exam Description --}}
                                    @if($exam->description)
                                        <p class="mt-3 text-gray-600">
                                            {{ Str::limit($exam->description, 200) }}
                                        </p>
                                    @endif

                                    {{-- Exam Details Grid --}}
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {{-- Delivery Mode --}}
                                        <div class="flex items-center text-sm">
                                            <svg class="flex-shrink-0 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-gray-600">
                                                Mode: <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $exam->delivery_mode)) }}</span>
                                            </span>
                                        </div>

                                        {{-- Duration --}}
                                        <div class="flex items-center text-sm">
                                            <svg class="flex-shrink-0 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-gray-600">
                                                Duration: <span class="font-medium">{{ $exam->duration_minutes }} minutes</span>
                                            </span>
                                        </div>

                                        {{-- Total Marks --}}
                                        <div class="flex items-center text-sm">
                                            <svg class="flex-shrink-0 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            <span class="text-gray-600">
                                                Total Marks: <span class="font-medium">{{ $exam->total_marks }}</span>
                                            </span>
                                        </div>

                                        {{-- Questions --}}
                                        <div class="flex items-center text-sm">
                                            <svg class="flex-shrink-0 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-gray-600">
                                                Questions: <span class="font-medium">{{ $exam->total_questions }}</span>
                                            </span>
                                        </div>

                                        {{-- Exam Date --}}
                                        <div class="flex items-center text-sm">
                                            <svg class="flex-shrink-0 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-gray-600">
                                                @if($exam->exam_date)
                                                    Date: <span class="font-medium">{{ \Carbon\Carbon::parse($exam->exam_date)->format('M d, Y') }}</span>
                                                @elseif($exam->exam_window_start && $exam->exam_window_end)
                                                    Window: <span class="font-medium">
                                                        {{ \Carbon\Carbon::parse($exam->exam_window_start)->format('M d') }} - 
                                                        {{ \Carbon\Carbon::parse($exam->exam_window_end)->format('M d, Y') }}
                                                    </span>
                                                @else
                                                    Date: <span class="font-medium">To be announced</span>
                                                @endif
                                            </span>
                                        </div>

                                        {{-- Registration Deadline --}}
                                        <div class="flex items-center text-sm">
                                            <svg class="flex-shrink-0 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-gray-600">
                                                Registration Deadline: 
                                                <span class="font-medium {{ \Carbon\Carbon::parse($exam->registration_end_date)->isPast() ? 'text-red-600' : '' }}">
                                                    {{ \Carbon\Carbon::parse($exam->registration_end_date)->format('M d, Y') }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Applicable Programs --}}
                                    @if($exam->applicable_programs)
                                        <div class="mt-4">
                                            <span class="text-sm text-gray-600">Applicable for: </span>
                                            <div class="mt-1 flex flex-wrap gap-2">
                                                @foreach($exam->programs as $program)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $program->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Exam Sections --}}
                                    @if($exam->sections)
                                        <div class="mt-4">
                                            <span class="text-sm text-gray-600">Exam Sections:</span>
                                            <div class="mt-2 space-y-1">
                                                @foreach(json_decode($exam->sections) as $section)
                                                    <div class="flex justify-between text-sm">
                                                        <span class="text-gray-600">
                                                            {{ $section->name }} 
                                                            @if(!$section->is_mandatory)
                                                                <span class="text-xs text-gray-500">(Optional)</span>
                                                            @endif
                                                        </span>
                                                        <span class="text-gray-900">
                                                            {{ $section->questions }} questions | {{ $section->marks }} marks | {{ $section->duration }} min
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Action Buttons --}}
                                    <div class="mt-6 flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            @if($exam->general_instructions || $exam->exam_rules)
                                                <button type="button" 
                                                        onclick="showInstructions('{{ $exam->id }}')"
                                                        class="text-sm text-indigo-600 hover:text-indigo-500">
                                                    View Instructions
                                                </button>
                                            @endif
                                            
                                            @if($exam->syllabus_file)
                                                <a href="{{ route('exams.download-syllabus', $exam->id) }}" 
                                                   class="text-sm text-indigo-600 hover:text-indigo-500">
                                                    Download Syllabus
                                                </a>
                                            @endif
                                        </div>

                                        <div>
                                            @if($exam->status == 'registration_open' && !\Carbon\Carbon::parse($exam->registration_end_date)->isPast())
                                                @if($exam->user_registration)
                                                    <a href="{{ route('exams.registration.view', $exam->user_registration->id) }}" 
                                                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                                        View Registration
                                                    </a>
                                                @else
                                                    <a href="{{ route('exams.register', $exam->id) }}" 
                                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                                        Register Now
                                                    </a>
                                                @endif
                                            @elseif($exam->status == 'registration_closed' || \Carbon\Carbon::parse($exam->registration_end_date)->isPast())
                                                <span class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                                                    Registration Closed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                                    Coming Soon
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Instructions Modal (Hidden by default) --}}
<div id="instructionsModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Exam Instructions
                        </h3>
                        <div class="mt-4">
                            <div id="instructionsContent" class="prose max-w-none">
                                <!-- Instructions will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        onclick="closeInstructionsModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showInstructions(examId) {
        // Fetch and display instructions
        fetch(`/api/exams/${examId}/instructions`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('instructionsContent').innerHTML = data.instructions;
                document.getElementById('instructionsModal').classList.remove('hidden');
            });
    }

    function closeInstructionsModal() {
        document.getElementById('instructionsModal').classList.add('hidden');
    }
</script>
@endpush
@endsection