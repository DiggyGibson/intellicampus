@extends('layouts.app')

@section('title', 'Register for Exam - ' . $exam->exam_name)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        {{-- Progress Bar --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Registration Progress</h3>
                    <span class="text-sm text-gray-500">Step <span id="currentStep">1</span> of 4</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progressBar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 25%"></div>
                </div>
                <div class="flex justify-between mt-2">
                    <span class="text-xs text-gray-600">Personal Info</span>
                    <span class="text-xs text-gray-600">Exam Details</span>
                    <span class="text-xs text-gray-600">Payment</span>
                    <span class="text-xs text-gray-600">Confirmation</span>
                </div>
            </div>
        </div>

        {{-- Registration Form --}}
        <form id="examRegistrationForm" method="POST" action="{{ route('exams.register.submit', $exam->id) }}" enctype="multipart/form-data">
            @csrf
            
            {{-- Step 1: Personal Information --}}
            <div id="step1" class="step-content">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Personal Information</h2>
                        
                        {{-- Check if user is logged in and has application --}}
                        @if(auth()->check() && $application)
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Information has been pre-filled from your admission application. Please verify and update if needed.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- First Name --}}
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="first_name" 
                                       id="first_name" 
                                       value="{{ old('first_name', $application->first_name ?? '') }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Last Name --}}
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">
                                    Last Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="last_name" 
                                       id="last_name" 
                                       value="{{ old('last_name', $application->last_name ?? '') }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       value="{{ old('email', $application->email ?? auth()->user()->email ?? '') }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone', $application->phone_primary ?? '') }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Date of Birth --}}
                            <div>
                                <label for="date_of_birth" class="block text-sm font-medium text-gray-700">
                                    Date of Birth <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       name="date_of_birth" 
                                       id="date_of_birth" 
                                       value="{{ old('date_of_birth', $application->date_of_birth ?? '') }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('date_of_birth')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700">
                                    Gender <span class="text-red-500">*</span>
                                </label>
                                <select name="gender" 
                                        id="gender" 
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $application->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $application->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $application->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Application Number (if exists) --}}
                            @if($application)
                                <input type="hidden" name="application_id" value="{{ $application->id }}">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Application Number
                                    </label>
                                    <input type="text" 
                                           value="{{ $application->application_number }}"
                                           disabled
                                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                                </div>
                            @endif
                        </div>

                        {{-- Photo Upload --}}
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">
                                Passport Photo <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 flex items-center">
                                <span class="inline-block h-24 w-24 rounded-full overflow-hidden bg-gray-100">
                                    <img id="photoPreview" src="{{ asset('images/default-avatar.png') }}" alt="Photo preview" class="h-full w-full object-cover">
                                </span>
                                <div class="ml-5">
                                    <input type="file" 
                                           name="photo" 
                                           id="photo" 
                                           accept="image/*"
                                           required
                                           onchange="previewPhoto(event)"
                                           class="hidden">
                                    <label for="photo" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Choose Photo
                                    </label>
                                    <p class="text-xs text-gray-500 mt-2">JPG, PNG up to 2MB. Passport size preferred.</p>
                                </div>
                            </div>
                            @error('photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Exam Details & Preferences --}}
            <div id="step2" class="step-content hidden">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Exam Details & Preferences</h2>

                        {{-- Exam Information Summary --}}
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h3 class="font-medium text-gray-900 mb-2">Exam Information</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Exam Name:</span>
                                    <span class="font-medium">{{ $exam->exam_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Exam Code:</span>
                                    <span class="font-medium">{{ $exam->exam_code }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Duration:</span>
                                    <span class="font-medium">{{ $exam->duration_minutes }} minutes</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Marks:</span>
                                    <span class="font-medium">{{ $exam->total_marks }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Mode:</span>
                                    <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $exam->delivery_mode)) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            {{-- Exam Center Selection (for paper-based/CBT) --}}
                            @if(in_array($exam->delivery_mode, ['paper_based', 'computer_based']))
                                <div>
                                    <label for="center_preference" class="block text-sm font-medium text-gray-700">
                                        Exam Center Preference <span class="text-red-500">*</span>
                                    </label>
                                    <select name="center_preference[]" 
                                            id="center_preference" 
                                            multiple
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @foreach($examCenters as $center)
                                            <option value="{{ $center->id }}">
                                                {{ $center->center_name }} - {{ $center->city }}, {{ $center->state }}
                                                (Capacity: {{ $center->available_seats }}/{{ $center->total_capacity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Select up to 3 centers in order of preference. Hold Ctrl/Cmd to select multiple.</p>
                                    @error('center_preference')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            {{-- Session Preference --}}
                            @if($examSessions->count() > 0)
                                <div>
                                    <label for="session_preference" class="block text-sm font-medium text-gray-700">
                                        Session Preference <span class="text-red-500">*</span>
                                    </label>
                                    <select name="session_preference" 
                                            id="session_preference" 
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Select Session</option>
                                        @foreach($examSessions as $session)
                                            <option value="{{ $session->id }}" 
                                                    {{ $session->registered_count >= $session->capacity ? 'disabled' : '' }}>
                                                {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }} - 
                                                {{ ucfirst($session->session_type) }} 
                                                ({{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - 
                                                {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }})
                                                @if($session->registered_count >= $session->capacity)
                                                    - FULL
                                                @else
                                                    - Available: {{ $session->capacity - $session->registered_count }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('session_preference')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            {{-- Special Accommodations --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Special Accommodations
                                </label>
                                <div class="space-y-3">
                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               name="requires_accommodation" 
                                               id="requires_accommodation"
                                               value="1"
                                               onchange="toggleAccommodations()"
                                               class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">
                                            I require special accommodations for this exam
                                        </span>
                                    </label>

                                    <div id="accommodationDetails" class="hidden space-y-3 ml-6">
                                        <div>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="accommodations[extra_time]" value="1"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <span class="ml-2 text-sm text-gray-700">Extra time required</span>
                                            </label>
                                            <input type="number" 
                                                   name="accommodations[extra_time_minutes]" 
                                                   placeholder="Minutes"
                                                   class="mt-1 ml-6 w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>

                                        <label class="flex items-center">
                                            <input type="checkbox" name="accommodations[large_print]" value="1"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-gray-700">Large print question paper</span>
                                        </label>

                                        <label class="flex items-center">
                                            <input type="checkbox" name="accommodations[reader_required]" value="1"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-gray-700">Reader assistance required</span>
                                        </label>

                                        <label class="flex items-center">
                                            <input type="checkbox" name="accommodations[scribe_required]" value="1"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-gray-700">Scribe/Writer assistance required</span>
                                        </label>

                                        <label class="flex items-center">
                                            <input type="checkbox" name="accommodations[wheelchair_access]" value="1"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-gray-700">Wheelchair accessible venue required</span>
                                        </label>

                                        <div>
                                            <label for="other_accommodations" class="block text-sm font-medium text-gray-700">
                                                Other Requirements
                                            </label>
                                            <textarea name="accommodations[other]" 
                                                      id="other_accommodations"
                                                      rows="3"
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                      placeholder="Please describe any other accommodations needed..."></textarea>
                                        </div>

                                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                            <p class="text-sm text-yellow-800">
                                                <strong>Note:</strong> Supporting documentation may be required for accommodation requests.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Payment --}}
            <div id="step3" class="step-content hidden">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Registration Fee Payment</h2>

                        {{-- Fee Details --}}
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h3 class="font-medium text-gray-900 mb-3">Fee Summary</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Registration Fee:</span>
                                    <span class="font-medium">${{ number_format($examFee->amount, 2) }}</span>
                                </div>
                                @if($examFee->late_fee && $isLateFee)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Late Registration Fee:</span>
                                        <span class="font-medium">${{ number_format($examFee->late_fee, 2) }}</span>
                                    </div>
                                @endif
                                <div class="border-t pt-2 flex justify-between">
                                    <span class="font-medium text-gray-900">Total Amount:</span>
                                    <span class="font-bold text-lg text-gray-900">
                                        ${{ number_format($totalFee, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Fee Waiver Check --}}
                        @if($canRequestWaiver)
                            <div class="mb-6">
                                <label class="flex items-start">
                                    <input type="checkbox" 
                                           name="request_fee_waiver" 
                                           id="request_fee_waiver"
                                           value="1"
                                           onchange="toggleFeeWaiver()"
                                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">
                                        I would like to request a fee waiver
                                    </span>
                                </label>

                                <div id="feeWaiverReason" class="hidden mt-3">
                                    <label for="waiver_reason" class="block text-sm font-medium text-gray-700">
                                        Reason for Fee Waiver Request <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="waiver_reason" 
                                              id="waiver_reason"
                                              rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                              placeholder="Please explain why you are requesting a fee waiver..."></textarea>
                                </div>
                            </div>
                        @endif

                        {{-- Payment Method Selection --}}
                        <div id="paymentSection">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Select Payment Method <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                @foreach($paymentMethods as $method)
                                    <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" 
                                               name="payment_method" 
                                               value="{{ $method->code }}"
                                               required
                                               onchange="showPaymentDetails('{{ $method->code }}')"
                                               class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-gray-900">
                                                {{ $method->name }}
                                            </span>
                                            @if($method->description)
                                                <span class="block text-xs text-gray-500">
                                                    {{ $method->description }}
                                                </span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Payment Details (Dynamic based on selection) --}}
                            <div id="paymentDetails" class="mt-6 hidden">
                                <!-- Payment details will be loaded here based on selection -->
                            </div>
                        </div>

                        {{-- Terms and Conditions --}}
                        <div class="mt-6">
                            <label class="flex items-start">
                                <input type="checkbox" 
                                       name="terms_accepted" 
                                       id="terms_accepted"
                                       value="1"
                                       required
                                       class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">
                                    I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500">terms and conditions</a> 
                                    and understand that the registration fee is non-refundable except in cases of exam cancellation.
                                </span>
                            </label>
                            @error('terms_accepted')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 4: Review & Confirmation --}}
            <div id="step4" class="step-content hidden">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Review & Confirm Registration</h2>

                        <div class="space-y-6">
                            {{-- Personal Information Review --}}
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Personal Information</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_name">-</dd>
                                    </div>
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_email">-</dd>
                                    </div>
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_phone">-</dd>
                                    </div>
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_dob">-</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Exam Details Review --}}
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Exam Details</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Exam</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $exam->exam_name }}</dd>
                                    </div>
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_exam_date">-</dd>
                                    </div>
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Center</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_center">-</dd>
                                    </div>
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Session</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_session">-</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Payment Review --}}
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Payment Information</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_amount">${{ number_format($totalFee, 2) }}</dd>
                                    </div>
                                    <div class="py-2">
                                        <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                        <dd class="mt-1 text-sm text-gray-900" id="review_payment_method">-</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Important Notes --}}
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Important Information</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <ul class="list-disc list-inside space-y-1">
                                                <li>Hall ticket will be available for download 7 days before the exam</li>
                                                <li>Carry a valid photo ID and hall ticket on the exam day</li>
                                                <li>Report to the exam center 30 minutes before the scheduled time</li>
                                                <li>Registration fee is non-refundable once payment is processed</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Declaration --}}
                            <div>
                                <label class="flex items-start">
                                    <input type="checkbox" 
                                           name="declaration" 
                                           id="declaration"
                                           value="1"
                                           required
                                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">
                                        I hereby declare that all information provided is true and correct to the best of my knowledge. 
                                        I understand that any false information may lead to cancellation of my registration.
                                    </span>
                                </label>
                                @error('declaration')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation Buttons --}}
            <div class="mt-6 flex justify-between">
                <button type="button" 
                        id="prevBtn" 
                        onclick="changeStep(-1)"
                        class="hidden inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Previous
                </button>

                <button type="button" 
                        id="nextBtn" 
                        onclick="changeStep(1)"
                        class="ml-auto inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Next
                    <svg class="ml-2 -mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <button type="submit" 
                        id="submitBtn" 
                        class="hidden ml-auto inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Complete Registration
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 4;

    function changeStep(direction) {
        // Validate current step before moving forward
        if (direction > 0 && !validateStep(currentStep)) {
            return;
        }

        // Hide current step
        document.getElementById(`step${currentStep}`).classList.add('hidden');
        
        // Update step number
        currentStep += direction;
        
        // Show new step
        document.getElementById(`step${currentStep}`).classList.remove('hidden');
        
        // Update progress bar
        updateProgressBar();
        
        // Update navigation buttons
        updateNavigationButtons();
        
        // If moving to review step, update review content
        if (currentStep === 4) {
            updateReviewContent();
        }
    }

    function updateProgressBar() {
        const progress = (currentStep / totalSteps) * 100;
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('currentStep').textContent = currentStep;
    }

    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        // Show/hide previous button
        if (currentStep === 1) {
            prevBtn.classList.add('hidden');
        } else {
            prevBtn.classList.remove('hidden');
        }
        
        // Show/hide next button and submit button
        if (currentStep === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
    }

    function validateStep(step) {
        let isValid = true;
        
        switch(step) {
            case 1:
                // Validate personal information
                const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender'];
                requiredFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (input && !input.value) {
                        input.classList.add('border-red-500');
                        isValid = false;
                    } else if (input) {
                        input.classList.remove('border-red-500');
                    }
                });
                
                if (!isValid) {
                    alert('Please fill in all required fields.');
                }
                break;
                
            case 2:
                // Validate exam preferences
                if (document.getElementById('center_preference') && !document.getElementById('center_preference').value) {
                    alert('Please select at least one exam center.');
                    isValid = false;
                }
                break;
                
            case 3:
                // Validate payment information
                if (!document.querySelector('input[name="payment_method"]:checked')) {
                    alert('Please select a payment method.');
                    isValid = false;
                }
                if (!document.getElementById('terms_accepted').checked) {
                    alert('Please accept the terms and conditions.');
                    isValid = false;
                }
                break;
        }
        
        return isValid;
    }

    function updateReviewContent() {
        // Update personal information
        document.getElementById('review_name').textContent = 
            document.getElementById('first_name').value + ' ' + document.getElementById('last_name').value;
        document.getElementById('review_email').textContent = document.getElementById('email').value;
        document.getElementById('review_phone').textContent = document.getElementById('phone').value;
        document.getElementById('review_dob').textContent = document.getElementById('date_of_birth').value;
        
        // Update exam details
        const sessionSelect = document.getElementById('session_preference');
        if (sessionSelect && sessionSelect.selectedIndex > 0) {
            document.getElementById('review_session').textContent = 
                sessionSelect.options[sessionSelect.selectedIndex].text;
        }
        
        const centerSelect = document.getElementById('center_preference');
        if (centerSelect && centerSelect.selectedIndex >= 0) {
            const selectedCenters = Array.from(centerSelect.selectedOptions)
                .map(option => option.text)
                .join(', ');
            document.getElementById('review_center').textContent = selectedCenters;
        }
        
        // Update payment method
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (paymentMethod) {
            document.getElementById('review_payment_method').textContent = 
                paymentMethod.parentElement.querySelector('.font-medium').textContent;
        }
    }

    function previewPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photoPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }

    function toggleAccommodations() {
        const checkbox = document.getElementById('requires_accommodation');
        const details = document.getElementById('accommodationDetails');
        
        if (checkbox.checked) {
            details.classList.remove('hidden');
        } else {
            details.classList.add('hidden');
        }
    }

    function toggleFeeWaiver() {
        const checkbox = document.getElementById('request_fee_waiver');
        const reason = document.getElementById('feeWaiverReason');
        const paymentSection = document.getElementById('paymentSection');
        
        if (checkbox.checked) {
            reason.classList.remove('hidden');
            paymentSection.classList.add('opacity-50', 'pointer-events-none');
        } else {
            reason.classList.add('hidden');
            paymentSection.classList.remove('opacity-50', 'pointer-events-none');
        }
    }

    function showPaymentDetails(method) {
        const detailsDiv = document.getElementById('paymentDetails');
        detailsDiv.classList.remove('hidden');
        
        // Load payment details based on method
        // This would typically make an AJAX call to get payment-specific fields
        switch(method) {
            case 'credit_card':
                detailsDiv.innerHTML = `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Card Number</label>
                            <input type="text" name="card_number" placeholder="1234 5678 9012 3456" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                <input type="text" name="card_expiry" placeholder="MM/YY" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">CVV</label>
                                <input type="text" name="card_cvv" placeholder="123" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'mobile_money':
                detailsDiv.innerHTML = `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mobile Money Provider</label>
                            <select name="mobile_provider" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Provider</option>
                                <option value="orange">Orange Money</option>
                                <option value="mtn">MTN Mobile Money</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mobile Number</label>
                            <input type="tel" name="mobile_number" placeholder="+231 77 123 4567" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                `;
                break;
                
            case 'bank_transfer':
                detailsDiv.innerHTML = `
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-2">Bank Transfer Instructions</h4>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Bank Name:</dt>
                                <dd class="font-medium">University Bank</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Account Name:</dt>
                                <dd class="font-medium">IntelliCampus Exams</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Account Number:</dt>
                                <dd class="font-medium">1234567890</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Reference:</dt>
                                <dd class="font-medium">Your Registration Number</dd>
                            </div>
                        </dl>
                        <p class="mt-3 text-xs text-gray-500">
                            Please upload proof of payment after completing the transfer.
                        </p>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700">Upload Payment Proof</label>
                            <input type="file" name="payment_proof" accept="image/*,application/pdf" 
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                `;
                break;
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateProgressBar();
        updateNavigationButtons();
    });
</script>
@endpush
@endsection