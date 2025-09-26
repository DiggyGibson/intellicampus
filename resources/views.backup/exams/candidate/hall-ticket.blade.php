@extends('layouts.app')

@section('title', 'Hall Ticket - ' . $registration->exam->exam_name)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        {{-- Action Buttons --}}
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('exams.my-registrations') }}" 
               class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to My Registrations
            </a>
            
            <div class="space-x-3">
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
                
                <a href="{{ route('exams.hall-ticket.download', $registration->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>

        {{-- Hall Ticket Card --}}
        <div id="hallTicket" class="bg-white overflow-hidden shadow-sm sm:rounded-lg print:shadow-none">
            {{-- Header with Logo and Title --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white p-6 print:bg-none print:text-black print:border-b-2 print:border-black">
                <div class="flex justify-between items-start">
                    <div class="flex items-center">
                        <img src="{{ asset('images/university-logo.png') }}" alt="University Logo" class="h-20 w-20 mr-4 print:h-16 print:w-16">
                        <div>
                            <h1 class="text-2xl font-bold print:text-black">{{ config('app.name', 'IntelliCampus University') }}</h1>
                            <p class="text-sm opacity-90 print:text-black">Entrance Examination Hall Ticket</p>
                            <p class="text-xs opacity-75 print:text-black">{{ $registration->exam->term->name }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="bg-white text-gray-900 px-3 py-1 rounded print:border print:border-black">
                            <p class="text-xs">Hall Ticket No.</p>
                            <p class="text-lg font-bold">{{ $registration->hall_ticket_number }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                {{-- Candidate Photo and Information --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    {{-- Photo Section --}}
                    <div class="md:col-span-1">
                        <div class="border-2 border-gray-300 rounded-lg p-2">
                            @if($registration->candidate_photo)
                                <img src="{{ Storage::url($registration->candidate_photo) }}" 
                                     alt="Candidate Photo" 
                                     class="w-full h-48 object-cover rounded">
                            @else
                                <div class="w-full h-48 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-500">No Photo</span>
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-center text-gray-500 mt-2">Candidate Photo</p>
                        
                        {{-- Signature --}}
                        <div class="mt-4 border border-gray-300 rounded p-2 h-16">
                            @if($registration->candidate_signature)
                                <img src="{{ Storage::url($registration->candidate_signature) }}" 
                                     alt="Signature" 
                                     class="h-full w-auto mx-auto">
                            @else
                                <div class="h-full flex items-center justify-center">
                                    <span class="text-xs text-gray-400">Signature</span>
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-center text-gray-500 mt-1">Candidate Signature</p>
                    </div>

                    {{-- Personal Information --}}
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Candidate Details</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Number</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $registration->registration_number }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Application Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $registration->application->application_number ?? 'N/A' }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ strtoupper($registration->candidate_name ?? $registration->application->first_name . ' ' . $registration->application->last_name) }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($registration->date_of_birth ?? $registration->application->date_of_birth)->format('d M Y') }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ ucfirst($registration->application->gender ?? 'N/A') }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Category</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $registration->category ?? 'General' }}
                                </dd>
                            </div>
                            
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $registration->candidate_email ?? $registration->application->email }}
                                </dd>
                            </div>
                            
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $registration->candidate_phone ?? $registration->application->phone_primary }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Exam Details Section --}}
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Examination Details</h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                            <div>
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Exam Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $registration->exam->exam_name }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Exam Code</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $registration->exam->exam_code }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Exam Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ \Carbon\Carbon::parse($registration->session->session_date)->format('l, d F Y') }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Reporting Time</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ \Carbon\Carbon::parse($registration->session->start_time)->subMinutes(30)->format('h:i A') }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Exam Time</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ \Carbon\Carbon::parse($registration->session->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($registration->session->end_time)->format('h:i A') }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ $registration->exam->duration_minutes }} minutes
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Venue Details --}}
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Venue Details</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                            <div>
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Exam Center</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ $registration->seatAllocation->center->center_name }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Center Code</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ $registration->seatAllocation->center->center_code }}
                                </dd>
                            </div>
                            
                            @if($registration->seatAllocation->room_number)
                                <div>
                                    <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Room/Hall Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ $registration->seatAllocation->room_number }}
                                    </dd>
                                </div>
                            @endif
                            
                            @if($registration->seatAllocation->seat_number)
                                <div>
                                    <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Seat Number</dt>
                                    <dd class="mt-1 text-lg text-gray-900 font-bold text-red-600">
                                        {{ $registration->seatAllocation->seat_number }}
                                    </dd>
                                </div>
                            @endif
                            
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-700 uppercase tracking-wider">Center Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $registration->seatAllocation->center->address }},
                                    {{ $registration->seatAllocation->center->city }},
                                    {{ $registration->seatAllocation->center->state }}
                                    @if($registration->seatAllocation->center->postal_code)
                                        - {{ $registration->seatAllocation->center->postal_code }}
                                    @endif
                                </dd>
                            </div>

                            @if($registration->seatAllocation->center->latitude && $registration->seatAllocation->center->longitude)
                                <div class="sm:col-span-2">
                                    <a href="https://maps.google.com/?q={{ $registration->seatAllocation->center->latitude }},{{ $registration->seatAllocation->center->longitude }}" 
                                       target="_blank"
                                       class="text-sm text-indigo-600 hover:text-indigo-500 print:hidden">
                                        View on Google Maps →
                                    </a>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- Important Instructions --}}
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Important Instructions</h3>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">▪</span>
                                <span>Candidates must report to the examination center <strong>30 minutes before</strong> the scheduled exam time.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">▪</span>
                                <span>This Hall Ticket must be carried to the examination center along with a valid photo ID proof (Passport/Driver's License/National ID).</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">▪</span>
                                <span>Electronic devices including mobile phones, smart watches, calculators (unless specified) are <strong>strictly prohibited</strong> in the examination hall.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">▪</span>
                                <span>Candidates will not be allowed to enter the examination hall <strong>15 minutes after</strong> the commencement of the examination.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">▪</span>
                                <span>Use of unfair means will lead to immediate disqualification and cancellation of candidature.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">▪</span>
                                <span>Candidates must bring their own blue/black ball point pen. Pencil is allowed only for diagrams/graphs.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">▪</span>
                                <span>Rough work should be done only on the sheets provided in the examination hall.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Items to Bring --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Items to Bring:</h4>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <li>✓ Hall Ticket (this document)</li>
                            <li>✓ Valid Photo ID Proof</li>
                            <li>✓ Blue/Black Ball Point Pen (2 nos.)</li>
                            <li>✓ HB Pencil for diagrams</li>
                            <li>✓ Transparent water bottle</li>
                            <li>✓ Analog wrist watch (optional)</li>
                            @if($registration->exam->allowed_materials)
                                @foreach(json_decode($registration->exam->allowed_materials) as $material)
                                    <li>✓ {{ $material }}</li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Items NOT Allowed:</h4>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <li>✗ Mobile phones/Smart watches</li>
                            <li>✗ Electronic gadgets</li>
                            <li>✗ Calculators (unless specified)</li>
                            <li>✗ Books/Notes/Papers</li>
                            <li>✗ Bags/Pouches</li>
                            <li>✗ Eatables (except for medical reasons)</li>
                        </ul>
                    </div>
                </div>

                {{-- Footer with Signatures --}}
                <div class="border-t border-gray-200 pt-6 mt-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="border-t-2 border-gray-400 w-32 mx-auto mb-2"></div>
                            <p class="text-xs text-gray-600">Candidate's Signature</p>
                            <p class="text-xs text-gray-500 mt-1">(To be signed at the exam center)</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="border-t-2 border-gray-400 w-32 mx-auto mb-2"></div>
                            <p class="text-xs text-gray-600">Invigilator's Signature</p>
                            <p class="text-xs text-gray-500 mt-1">(With date & time)</p>
                        </div>
                        
                        <div class="text-center">
                            @if($controllerSignature)
                                <img src="{{ $controllerSignature }}" alt="Controller Signature" class="h-12 mx-auto mb-2">
                            @else
                                <div class="h-12 mb-2"></div>
                            @endif
                            <p class="text-xs text-gray-600">Controller of Examinations</p>
                            <p class="text-xs text-gray-500 mt-1">{{ config('app.name') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Barcode/QR Code for Verification --}}
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-xs text-gray-500">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
                            <p class="text-xs text-gray-500">Valid for: {{ $registration->exam->exam_name }}</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="bg-white p-2 border border-gray-300 rounded">
                                {!! QrCode::size(100)->generate(route('exams.verify-hall-ticket', $registration->hall_ticket_number)) !!}
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Verification QR Code</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Information Card (Not printed) --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4 print:hidden">
            <h4 class="font-medium text-blue-900 mb-2">Additional Information</h4>
            <ul class="space-y-1 text-sm text-blue-700">
                <li>• For any queries, contact the examination helpdesk at: <strong>exams@intellicampus.edu</strong></li>
                <li>• Helpline Number: <strong>+231 77 123 4567</strong> (Available Mon-Fri, 9 AM - 5 PM)</li>
                <li>• Results will be announced on: <strong>{{ \Carbon\Carbon::parse($registration->exam->result_publish_date)->format('d M Y') }}</strong></li>
                <li>• Keep this hall ticket safe as it will be required during counseling/admission process.</li>
            </ul>
        </div>
    </div>
</div>

{{-- Print Styles --}}
@push('styles')
<style>
    @media print {
        body {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
        
        .no-print {
            display: none !important;
        }
        
        #hallTicket {
            page-break-inside: avoid;
        }
        
        .print\:shadow-none {
            box-shadow: none !important;
        }
        
        .print\:bg-none {
            background: none !important;
        }
        
        .print\:text-black {
            color: black !important;
        }
        
        .print\:border {
            border: 1px solid black !important;
        }
    }
</style>
@endpush
@endsection