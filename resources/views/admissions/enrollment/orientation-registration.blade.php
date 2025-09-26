@extends('layouts.app')

@section('title', 'New Student Orientation Registration')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-t-lg">
            <h1 class="text-3xl font-bold">New Student Orientation Registration</h1>
            <p class="mt-2 text-purple-100">Start your journey at {{ config('app.name') }}</p>
        </div>
        
        <!-- Welcome Message -->
        <div class="p-6">
            <div class="prose max-w-none">
                <p class="text-lg text-gray-700">
                    Welcome to the {{ config('app.name') }} family! New Student Orientation is your official introduction to campus life, 
                    academic resources, and the vibrant community that will support you throughout your college journey.
                </p>
            </div>
        </div>
    </div>

    <!-- Student Information Summary -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">Student Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Name:</span>
                    <p class="font-semibold">{{ $application->first_name }} {{ $application->last_name }}</p>
                </div>
                <div>
                    <span class="text-gray-600">Student ID:</span>
                    <p class="font-semibold">{{ $enrollment->student_id ?? 'Pending' }}</p>
                </div>
                <div>
                    <span class="text-gray-600">Program:</span>
                    <p class="font-semibold">{{ $application->program->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Orientation Sessions -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Available Orientation Sessions</h2>
            
            @if($orientationSessions->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800">No orientation sessions are currently available. Please check back later.</p>
                </div>
            @else
                <form action="{{ route('enrollment.orientation.register', $enrollment->id) }}" method="POST" id="orientationForm">
                    @csrf
                    
                    <div class="space-y-4 mb-6">
                        @foreach($orientationSessions as $session)
                        <label class="block cursor-pointer">
                            <input type="radio" name="session_id" value="{{ $session->id }}" class="hidden peer" required>
                            <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-purple-300 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <h3 class="text-lg font-semibold">{{ $session->name }}</h3>
                                            @if($session->spots_remaining < 10)
                                            <span class="ml-3 px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">
                                                Only {{ $session->spots_remaining }} spots left!
                                            </span>
                                            @endif
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                                <span class="text-gray-600">Dates:</span>
                                                <span class="font-medium">
                                                    {{ $session->start_date->format('M j') }} - {{ $session->end_date->format('M j, Y') }}
                                                </span>
                                            </div>
                                            
                                            <div>
                                                <i class="fas fa-clock text-gray-400 mr-2"></i>
                                                <span class="text-gray-600">Duration:</span>
                                                <span class="font-medium">{{ $session->duration_days }} days</span>
                                            </div>
                                            
                                            <div>
                                                <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                                <span class="text-gray-600">Format:</span>
                                                <span class="font-medium">{{ ucfirst($session->format) }}</span>
                                            </div>
                                            
                                            <div>
                                                <i class="fas fa-users text-gray-400 mr-2"></i>
                                                <span class="text-gray-600">Available Spots:</span>
                                                <span class="font-medium">{{ $session->spots_remaining }} / {{ $session->capacity }}</span>
                                            </div>
                                        </div>
                                        
                                        @if($session->description)
                                        <p class="mt-3 text-sm text-gray-600">{{ $session->description }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="ml-4 flex-shrink-0">
                                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-purple-500 peer-checked:bg-purple-500 relative">
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <div class="w-2 h-2 bg-white rounded-full hidden peer-checked:block"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <!-- Guest Information -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Guest Information</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Family members are welcome to attend certain orientation events. Please indicate if you'll be bringing guests.
                        </p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Guests</label>
                                <select name="num_guests" id="numGuests" class="w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option value="0">No guests</option>
                                    <option value="1">1 guest</option>
                                    <option value="2">2 guests</option>
                                    <option value="3">3 guests</option>
                                    <option value="4">4 guests</option>
                                </select>
                            </div>
                            
                            <div id="guestDetails" class="hidden space-y-3">
                                <!-- Guest detail fields will be dynamically added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Special Accommodations -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-semibold mb-4">Special Accommodations</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Please let us know if you have any special needs or dietary restrictions.
                        </p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-start">
                                    <input type="checkbox" name="needs_accessibility" class="mt-1 rounded border-gray-300 text-purple-600">
                                    <span class="ml-2 text-sm">I require wheelchair accessibility</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="flex items-start">
                                    <input type="checkbox" name="needs_interpreter" class="mt-1 rounded border-gray-300 text-purple-600">
                                    <span class="ml-2 text-sm">I need sign language interpretation</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dietary Restrictions</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="dietary[]" value="vegetarian" class="rounded border-gray-300 text-purple-600">
                                        <span class="ml-2 text-sm">Vegetarian</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="dietary[]" value="vegan" class="rounded border-gray-300 text-purple-600">
                                        <span class="ml-2 text-sm">Vegan</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="dietary[]" value="gluten_free" class="rounded border-gray-300 text-purple-600">
                                        <span class="ml-2 text-sm">Gluten-Free</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="dietary[]" value="halal" class="rounded border-gray-300 text-purple-600">
                                        <span class="ml-2 text-sm">Halal</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="dietary[]" value="kosher" class="rounded border-gray-300 text-purple-600">
                                        <span class="ml-2 text-sm">Kosher</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="dietary[]" value="nut_allergy" class="rounded border-gray-300 text-purple-600">
                                        <span class="ml-2 text-sm">Nut Allergy</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Other Special Needs or Requests</label>
                                <textarea name="special_requests" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" 
                                          placeholder="Please describe any other accommodations you may need..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-semibold mb-4">Emergency Contact for Orientation</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name <span class="text-red-500">*</span></label>
                                <input type="text" name="emergency_name" required value="{{ $application->emergency_contact_name }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Relationship <span class="text-red-500">*</span></label>
                                <input type="text" name="emergency_relationship" required value="{{ $application->emergency_contact_relationship }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="emergency_phone" required value="{{ $application->emergency_contact_phone }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="emergency_email" value="{{ $application->emergency_contact_email }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-semibold mb-4">Terms and Conditions</h3>
                        <div class="bg-gray-50 p-4 rounded-lg mb-4 max-h-48 overflow-y-auto text-sm text-gray-600">
                            <p class="mb-3">By registering for New Student Orientation, you agree to the following:</p>
                            <ul class="list-disc list-inside space-y-2">
                                <li>Attendance at all mandatory sessions is required for enrollment completion</li>
                                <li>The orientation fee is non-refundable except in cases of session cancellation</li>
                                <li>You will comply with all university policies and codes of conduct</li>
                                <li>Photo/video may be taken during orientation for university promotional purposes</li>
                                <li>You are responsible for your own transportation to and from orientation venues</li>
                                <li>Guest attendance is subject to space availability and specific session restrictions</li>
                            </ul>
                        </div>
                        
                        <label class="flex items-start">
                            <input type="checkbox" name="agree_terms" required class="mt-1 rounded border-gray-300 text-purple-600">
                            <span class="ml-2 text-sm">
                                I have read and agree to the orientation terms and conditions <span class="text-red-500">*</span>
                            </span>
                        </label>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="mt-8 flex justify-between">
                        <a href="{{ route('enrollment.confirmation', $enrollment->id) }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Enrollment
                        </a>
                        
                        <button type="submit" class="px-8 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Complete Registration <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Orientation Information -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">What to Expect at Orientation</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-3 text-purple-600">Academic Sessions</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Meet with academic advisors</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Register for fall classes</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Learn about degree requirements</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Explore academic resources</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-3 text-purple-600">Campus Life</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Campus tours and navigation</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Student organization fair</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Residence hall information</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Dining services overview</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-3 text-purple-600">Essential Services</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Get your student ID card</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Set up IT accounts</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Financial aid consultation</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Health services registration</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-3 text-purple-600">Social Activities</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Meet your classmates</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Team building activities</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Welcome reception</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Campus traditions ceremony</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- What to Bring -->
    <div class="bg-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">What to Bring to Orientation</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <ul class="space-y-2">
                <li class="flex items-start">
                    <i class="fas fa-clipboard-check text-blue-600 mt-1 mr-2"></i>
                    <span>Photo ID (driver's license or passport)</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-clipboard-check text-blue-600 mt-1 mr-2"></i>
                    <span>Health insurance information</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-clipboard-check text-blue-600 mt-1 mr-2"></i>
                    <span>Immunization records</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-clipboard-check text-blue-600 mt-1 mr-2"></i>
                    <span>Any required medications</span>
                </li>
            </ul>
            <ul class="space-y-2">
                <li class="flex items-start">
                    <i class="fas fa-clipboard-check text-blue-600 mt-1 mr-2"></i>
                    <span>Comfortable walking shoes</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-clipboard-check text-blue-600 mt-1 mr-2"></i>
                    <span>Weather-appropriate clothing</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-clipboard-check text-blue-600 mt-1 mr-2"></i>
                    <span>Notebook and pen</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-clipboard-check text-blue-600 mt-1 mr-2"></i>
                    <span>Water bottle</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="text-center mt-8 text-sm text-gray-600">
        <p>Questions about orientation? Contact the New Student Programs Office</p>
        <p class="mt-1">
            <i class="fas fa-phone mr-2"></i> (555) 123-4568
            <span class="mx-3">|</span>
            <i class="fas fa-envelope mr-2"></i> orientation@university.edu
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Guest details dynamic fields
    const numGuestsSelect = document.getElementById('numGuests');
    const guestDetailsDiv = document.getElementById('guestDetails');
    
    numGuestsSelect.addEventListener('change', function() {
        const numGuests = parseInt(this.value);
        
        if (numGuests === 0) {
            guestDetailsDiv.classList.add('hidden');
            guestDetailsDiv.innerHTML = '';
        } else {
            guestDetailsDiv.classList.remove('hidden');
            let html = '<p class="text-sm text-gray-600 mb-3">Please provide information for each guest:</p>';
            
            for (let i = 1; i <= numGuests; i++) {
                html += `
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-medium mb-3">Guest ${i}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" name="guest_name_${i}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                                <select name="guest_relationship_${i}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option value="">Select...</option>
                                    <option value="parent">Parent</option>
                                    <option value="guardian">Guardian</option>
                                    <option value="sibling">Sibling</option>
                                    <option value="spouse">Spouse</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            guestDetailsDiv.innerHTML = html;
        }
    });
    
    // Form validation
    const form = document.getElementById('orientationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const sessionSelected = document.querySelector('input[name="session_id"]:checked');
            if (!sessionSelected) {
                e.preventDefault();
                alert('Please select an orientation session.');
                return false;
            }
            
            const termsAgreed = document.querySelector('input[name="agree_terms"]').checked;
            if (!termsAgreed) {
                e.preventDefault();
                alert('Please agree to the terms and conditions.');
                return false;
            }
        });
    }
});
</script>
@endpush