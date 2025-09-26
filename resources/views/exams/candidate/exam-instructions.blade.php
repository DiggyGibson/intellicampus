@extends('layouts.app')

@section('title', 'Exam Instructions - ' . $registration->exam->exam_name)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $registration->exam->exam_name }}</h1>
                        <p class="mt-1 text-blue-100">Pre-Examination Instructions & Guidelines</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-blue-100">Registration Number</p>
                        <p class="text-xl font-semibold">{{ $registration->registration_number }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Countdown Timer (if exam date is set) --}}
        @if($registration->session)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Time Until Exam</h3>
                        <div id="countdown" class="flex justify-center space-x-4">
                            <div class="bg-gray-100 rounded-lg p-4">
                                <div class="text-3xl font-bold text-indigo-600" id="days">00</div>
                                <div class="text-xs text-gray-500 uppercase">Days</div>
                            </div>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <div class="text-3xl font-bold text-indigo-600" id="hours">00</div>
                                <div class="text-xs text-gray-500 uppercase">Hours</div>
                            </div>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <div class="text-3xl font-bold text-indigo-600" id="minutes">00</div>
                                <div class="text-xs text-gray-500 uppercase">Minutes</div>
                            </div>
                            <div class="bg-gray-100 rounded-lg p-4">
                                <div class="text-3xl font-bold text-indigo-600" id="seconds">00</div>
                                <div class="text-xs text-gray-500 uppercase">Seconds</div>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-600">
                            Exam Date: {{ \Carbon\Carbon::parse($registration->session->session_date)->format('l, d F Y') }} at 
                            {{ \Carbon\Carbon::parse($registration->session->start_time)->format('h:i A') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Actions --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('exams.hall-ticket', $registration->id) }}" 
                       class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                        <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Download Hall Ticket</p>
                            <p class="text-sm text-gray-600">Get your admit card</p>
                        </div>
                    </a>

                    @if($registration->exam->syllabus_file)
                        <a href="{{ route('exams.download-syllabus', $registration->exam->id) }}" 
                           class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                            <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900">Exam Syllabus</p>
                                <p class="text-sm text-gray-600">Download syllabus PDF</p>
                            </div>
                        </a>
                    @endif

                    @if($registration->exam->sample_papers)
                        <a href="{{ route('exams.sample-papers', $registration->exam->id) }}" 
                           class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                            <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900">Sample Papers</p>
                                <p class="text-sm text-gray-600">Practice questions</p>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Exam Pattern & Structure --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Exam Pattern & Structure</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3">Exam Overview</h4>
                        <dl class="space-y-2">
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-sm text-gray-600">Total Questions</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $registration->exam->total_questions }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-sm text-gray-600">Total Marks</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $registration->exam->total_marks }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-sm text-gray-600">Duration</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $registration->exam->duration_minutes }} minutes</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-sm text-gray-600">Passing Marks</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $registration->exam->passing_marks }}</dd>
                            </div>
                            @if($registration->exam->negative_marking)
                                <div class="flex justify-between py-2 border-b">
                                    <dt class="text-sm text-gray-600">Negative Marking</dt>
                                    <dd class="text-sm font-medium text-red-600">-{{ $registration->exam->negative_mark_value }} per wrong answer</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-700 mb-3">Exam Mode</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm font-medium text-gray-900 mb-2">
                                {{ ucfirst(str_replace('_', ' ', $registration->exam->delivery_mode)) }}
                            </p>
                            @if($registration->exam->delivery_mode == 'computer_based')
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• Questions will appear on computer screen</li>
                                    <li>• Navigate using on-screen buttons</li>
                                    <li>• Timer will be displayed on screen</li>
                                    <li>• Auto-submit when time expires</li>
                                </ul>
                            @elseif($registration->exam->delivery_mode == 'paper_based')
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• OMR sheet will be provided</li>
                                    <li>• Use only blue/black ball point pen</li>
                                    <li>• Fill circles completely</li>
                                    <li>• No negative marking for unattempted questions</li>
                                </ul>
                            @elseif($registration->exam->delivery_mode == 'online_proctored')
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• Webcam must remain on throughout</li>
                                    <li>• Screen will be monitored</li>
                                    <li>• Ensure stable internet connection</li>
                                    <li>• Use Chrome/Firefox browser only</li>
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Section-wise Breakup --}}
                @if($registration->exam->sections)
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3">Section-wise Breakup</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Questions</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Marks</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(json_decode($registration->exam->sections) as $section)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $section->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $section->questions }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $section->marks }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $section->duration }} min</td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full 
                                                    {{ $section->is_mandatory ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $section->is_mandatory ? 'Mandatory' : 'Optional' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- General Instructions --}}
        @if($registration->exam->general_instructions)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">General Instructions</h3>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($registration->exam->general_instructions)) !!}
                    </div>
                </div>
            </div>
        @endif

        {{-- Exam Day Guidelines --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Exam Day Guidelines</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Before Exam --}}
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Before the Exam
                        </h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Get proper sleep (at least 7-8 hours) the night before</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Have a light, nutritious breakfast on exam day</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Check all required documents the night before</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Plan your route to the exam center in advance</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Leave home early to avoid any last-minute rush</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Carry a water bottle and light snacks (if permitted)</span>
                            </li>
                        </ul>
                    </div>

                    {{-- During Exam --}}
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            During the Exam
                        </h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Read all instructions carefully before starting</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Manage your time wisely - allocate time per section</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Attempt easy questions first to build confidence</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Mark difficult questions for review if time permits</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Keep track of time - wear a watch if allowed</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 mt-0.5">✓</span>
                                <span>Review your answers if time permits</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents Checklist --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Documents Checklist</h3>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800 mb-3">
                        <strong>Important:</strong> Entry to the examination hall will be denied without these documents
                    </p>
                    <div class="space-y-3">
                        <label class="flex items-start">
                            <input type="checkbox" class="mt-1 rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">
                                <strong>Hall Ticket (Admit Card)</strong> - Printed copy with clear photograph
                            </span>
                        </label>
                        <label class="flex items-start">
                            <input type="checkbox" class="mt-1 rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">
                                <strong>Photo ID Proof</strong> - Any one of: Passport / National ID / Driver's License
                            </span>
                        </label>
                        <label class="flex items-start">
                            <input type="checkbox" class="mt-1 rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">
                                <strong>Registration Confirmation</strong> - Email or SMS confirmation (optional but recommended)
                            </span>
                        </label>
                        @if($registration->requires_accommodation)
                            <label class="flex items-start">
                                <input type="checkbox" class="mt-1 rounded border-gray-300 text-indigo-600">
                                <span class="ml-2 text-sm text-gray-700">
                                    <strong>Special Accommodation Letter</strong> - If you have requested special accommodations
                                </span>
                            </label>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Do's and Don'ts --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Do's and Don'ts</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="font-medium text-green-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            DO's
                        </h4>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li>• Report at least 30 minutes before exam time</li>
                            <li>• Follow all instructions given by invigilators</li>
                            <li>• Maintain silence in the examination hall</li>
                            <li>• Raise your hand if you need assistance</li>
                            <li>• Check question paper for completeness</li>
                            <li>• Write clearly and legibly</li>
                            <li>• Submit your answer sheet before leaving</li>
                            <li>• Keep calm and stay focused</li>
                        </ul>
                    </div>

                    <div class="bg-red-50 rounded-lg p-4">
                        <h4 class="font-medium text-red-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            DON'Ts
                        </h4>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li>• Don't carry mobile phones or electronic devices</li>
                            <li>• Don't bring study materials or notes</li>
                            <li>• Don't talk to other candidates during exam</li>
                            <li>• Don't leave your seat without permission</li>
                            <li>• Don't use unfair means or malpractice</li>
                            <li>• Don't argue with invigilators</li>
                            <li>• Don't leave the hall without submitting paper</li>
                            <li>• Don't panic if you find questions difficult</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Need Help?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full mb-3">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900">Helpline</h4>
                        <p class="text-sm text-gray-600 mt-1">+231 77 123 4567</p>
                        <p class="text-xs text-gray-500">Mon-Fri, 9 AM - 5 PM</p>
                    </div>

                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full mb-3">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900">Email Support</h4>
                        <p class="text-sm text-gray-600 mt-1">exams@intellicampus.edu</p>
                        <p class="text-xs text-gray-500">24-48 hour response</p>
                    </div>

                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full mb-3">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900">Live Chat</h4>
                        <p class="text-sm text-gray-600 mt-1">Available on exam day</p>
                        <p class="text-xs text-gray-500">7 AM - 7 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Countdown Timer
    @if($registration->session)
        const examDate = new Date('{{ $registration->session->session_date }} {{ $registration->session->start_time }}').getTime();
        
        const countdown = setInterval(function() {
            const now = new Date().getTime();
            const distance = examDate - now;
            
            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById('days').innerHTML = String(days).padStart(2, '0');
                document.getElementById('hours').innerHTML = String(hours).padStart(2, '0');
                document.getElementById('minutes').innerHTML = String(minutes).padStart(2, '0');
                document.getElementById('seconds').innerHTML = String(seconds).padStart(2, '0');
            } else {
                clearInterval(countdown);
                document.getElementById('countdown').innerHTML = '<p class="text-lg text-red-600 font-semibold">Exam has started!</p>';
            }
        }, 1000);
    @endif
</script>
@endpush
@endsection