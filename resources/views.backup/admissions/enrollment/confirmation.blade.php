@extends('layouts.app')

@section('title', 'Enrollment Confirmation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-t-lg">
            <h1 class="text-3xl font-bold">Congratulations on Your Admission!</h1>
            <p class="mt-2 text-green-100">Welcome to {{ config('app.name', 'IntelliCampus') }} Class of {{ $enrollment->entry_year }}</p>
        </div>
        
        <!-- Admission Details -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Student Information</h3>
                    <div class="mt-2">
                        <p class="text-lg font-medium">{{ $application->first_name }} {{ $application->last_name }}</p>
                        <p class="text-gray-600">Application #: {{ $application->application_number }}</p>
                        <p class="text-gray-600">Email: {{ $application->email }}</p>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Admission Details</h3>
                    <div class="mt-2">
                        <p class="text-lg font-medium">{{ $application->program->name }}</p>
                        <p class="text-gray-600">Term: {{ $application->term->name }}</p>
                        <p class="text-gray-600">Entry Type: {{ ucfirst($application->entry_type) }} {{ $application->entry_year }}</p>
                    </div>
                </div>
            </div>

            <!-- Important Dates -->
            <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Important Dates</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Enrollment Deadline: <strong>{{ $enrollment->enrollment_deadline->format('F j, Y') }}</strong></li>
                                <li>Orientation Date: <strong>{{ $enrollment->orientation_date ? $enrollment->orientation_date->format('F j, Y') : 'TBA' }}</strong></li>
                                <li>Move-in Date: <strong>{{ $enrollment->move_in_date ? $enrollment->move_in_date->format('F j, Y') : 'TBA' }}</strong></li>
                                <li>Classes Begin: <strong>{{ $enrollment->classes_start_date ? $enrollment->classes_start_date->format('F j, Y') : 'TBA' }}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Decision Section -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Confirm Your Enrollment</h2>
            
            @if($enrollment->decision === 'pending')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <p class="text-gray-700 mb-4">
                        To secure your place in the {{ $application->term->name }} class, please confirm your enrollment decision 
                        and submit the required enrollment deposit by <strong>{{ $enrollment->enrollment_deadline->format('F j, Y') }}</strong>.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Accept Offer -->
                        <div class="border-2 border-green-500 rounded-lg p-6 bg-green-50">
                            <h3 class="text-lg font-semibold text-green-800 mb-3">Accept Admission Offer</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                By accepting, you commit to enrolling at {{ config('app.name') }} and agree to submit the enrollment deposit.
                            </p>
                            <form action="{{ route('enrollment.accept', $enrollment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-200">
                                    <i class="fas fa-check-circle mr-2"></i> Accept Offer
                                </button>
                            </form>
                        </div>
                        
                        <!-- Decline Offer -->
                        <div class="border-2 border-gray-300 rounded-lg p-6 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Decline Admission Offer</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                If you've decided not to attend, please let us know so we can offer your spot to another student.
                            </p>
                            <button onclick="showDeclineModal()" class="w-full bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-700 transition duration-200">
                                <i class="fas fa-times-circle mr-2"></i> Decline Offer
                            </button>
                        </div>
                    </div>
                </div>
            @elseif($enrollment->decision === 'accept')
                <!-- Enrollment Accepted Status -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-green-800">Enrollment Confirmed!</h3>
                            <p class="text-green-700 mt-1">
                                You confirmed your enrollment on {{ $enrollment->decision_date->format('F j, Y') }}.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($enrollment->decision === 'decline')
                <!-- Enrollment Declined Status -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle text-gray-500 text-3xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Offer Declined</h3>
                            <p class="text-gray-700 mt-1">
                                You declined the admission offer on {{ $enrollment->decision_date->format('F j, Y') }}.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Enrollment Checklist -->
    @if($enrollment->decision === 'accept')
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Enrollment Checklist</h2>
            
            <div class="space-y-4">
                <!-- Deposit Payment -->
                <div class="flex items-center justify-between p-4 {{ $enrollment->deposit_paid ? 'bg-green-50' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($enrollment->deposit_paid)
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            @else
                                <i class="far fa-circle text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Enrollment Deposit</h4>
                            <p class="text-sm text-gray-600">
                                @if($enrollment->deposit_paid)
                                    Paid on {{ $enrollment->deposit_paid_date->format('F j, Y') }} - Amount: ${{ number_format($enrollment->deposit_amount, 2) }}
                                @else
                                    Required deposit: ${{ number_format($enrollmentDepositAmount, 2) }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @if(!$enrollment->deposit_paid)
                        <a href="{{ route('enrollment.deposit', $enrollment->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                            Pay Now
                        </a>
                    @endif
                </div>

                <!-- Health Form -->
                <div class="flex items-center justify-between p-4 {{ $enrollment->health_form_submitted ? 'bg-green-50' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($enrollment->health_form_submitted)
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            @else
                                <i class="far fa-circle text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Health Form</h4>
                            <p class="text-sm text-gray-600">Submit your health history and medical information</p>
                        </div>
                    </div>
                    @if(!$enrollment->health_form_submitted)
                        <a href="{{ route('enrollment.health-form', $enrollment->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                            Submit Form
                        </a>
                    @endif
                </div>

                <!-- Immunization Records -->
                <div class="flex items-center justify-between p-4 {{ $enrollment->immunization_submitted ? 'bg-green-50' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($enrollment->immunization_submitted)
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            @else
                                <i class="far fa-circle text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Immunization Records</h4>
                            <p class="text-sm text-gray-600">Upload proof of required vaccinations</p>
                        </div>
                    </div>
                    @if(!$enrollment->immunization_submitted)
                        <a href="{{ route('enrollment.immunization', $enrollment->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                            Upload Records
                        </a>
                    @endif
                </div>

                <!-- Housing Application -->
                <div class="flex items-center justify-between p-4 {{ $enrollment->housing_applied ? 'bg-green-50' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($enrollment->housing_applied)
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            @else
                                <i class="far fa-circle text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Housing Application</h4>
                            <p class="text-sm text-gray-600">Apply for on-campus housing (optional)</p>
                        </div>
                    </div>
                    @if(!$enrollment->housing_applied)
                        <a href="{{ route('housing.apply') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                            Apply
                        </a>
                    @endif
                </div>

                <!-- Orientation Registration -->
                <div class="flex items-center justify-between p-4 {{ $enrollment->orientation_registered ? 'bg-green-50' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($enrollment->orientation_registered)
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            @else
                                <i class="far fa-circle text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Orientation Registration</h4>
                            <p class="text-sm text-gray-600">Register for new student orientation</p>
                        </div>
                    </div>
                    @if(!$enrollment->orientation_registered)
                        <a href="{{ route('enrollment.orientation', $enrollment->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                            Register
                        </a>
                    @endif
                </div>

                <!-- ID Card Processing -->
                <div class="flex items-center justify-between p-4 {{ $enrollment->id_card_processed ? 'bg-green-50' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($enrollment->id_card_processed)
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            @else
                                <i class="far fa-circle text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold">Student ID Card</h4>
                            <p class="text-sm text-gray-600">
                                @if($enrollment->id_card_processed && $enrollment->student_id)
                                    Your Student ID: <strong>{{ $enrollment->student_id }}</strong>
                                @else
                                    Will be processed after deposit payment
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Summary -->
            @php
                $totalItems = 6;
                $completedItems = 
                    ($enrollment->deposit_paid ? 1 : 0) +
                    ($enrollment->health_form_submitted ? 1 : 0) +
                    ($enrollment->immunization_submitted ? 1 : 0) +
                    ($enrollment->housing_applied ? 1 : 0) +
                    ($enrollment->orientation_registered ? 1 : 0) +
                    ($enrollment->id_card_processed ? 1 : 0);
                $progressPercentage = ($completedItems / $totalItems) * 100;
            @endphp
            
            <div class="mt-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Enrollment Progress</span>
                    <span>{{ $completedItems }} of {{ $totalItems }} completed</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progressPercentage }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Next Steps -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Next Steps</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Check Your Email</h3>
                    <p class="text-sm text-gray-600">We'll send important updates and reminders to your registered email address.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Mark Your Calendar</h3>
                    <p class="text-sm text-gray-600">Save important dates including orientation, move-in, and the first day of classes.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Connect With Us</h3>
                    <p class="text-sm text-gray-600">Join our admitted students group to connect with your future classmates.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div id="declineModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Decline Admission Offer</h3>
            <form action="{{ route('enrollment.decline', $enrollment->id) }}" method="POST" class="mt-4">
                @csrf
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Are you sure you want to decline your admission offer? This action cannot be undone.
                    </p>
                    <textarea name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                              placeholder="Please let us know why you're declining (optional)"></textarea>
                </div>
                <div class="items-center px-4 py-3">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Confirm Decline
                    </button>
                    <button type="button" onclick="hideDeclineModal()" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function showDeclineModal() {
        document.getElementById('declineModal').classList.remove('hidden');
    }
    
    function hideDeclineModal() {
        document.getElementById('declineModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        let modal = document.getElementById('declineModal');
        if (event.target == modal) {
            modal.classList.add('hidden');
        }
    }
</script>
@endpush