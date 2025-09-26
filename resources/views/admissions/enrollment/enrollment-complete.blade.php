@extends('layouts.app')

@section('title', 'Enrollment Complete')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Success Animation -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
            <i class="fas fa-check-circle text-green-500 text-5xl animate-bounce"></i>
        </div>
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Welcome to {{ config('app.name') }}!</h1>
        <p class="text-xl text-gray-600">Your enrollment is complete</p>
    </div>

    <!-- Confirmation Details -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">Enrollment Confirmation</h2>
            <p class="mt-2 text-green-100">Confirmation #: {{ $enrollment->confirmation_number }}</p>
        </div>
        
        <div class="p-6">
            <!-- Student Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">Student Information</h3>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Name:</span> {{ $application->first_name }} {{ $application->last_name }}</p>
                        <p><span class="font-semibold">Student ID:</span> {{ $enrollment->student_id }}</p>
                        <p><span class="font-semibold">Email:</span> {{ $studentAccount->email ?? $application->email }}</p>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">Academic Information</h3>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Program:</span> {{ $application->program->name }}</p>
                        <p><span class="font-semibold">Term:</span> {{ $application->term->name }}</p>
                        <p><span class="font-semibold">Status:</span> <span class="text-green-600">Enrolled</span></p>
                    </div>
                </div>
            </div>

            <!-- Completion Summary -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold mb-4">Enrollment Tasks Completed</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span>Admission offer accepted</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span>Enrollment deposit paid</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span>Health forms submitted</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span>Immunization records uploaded</span>
                    </div>
                    @if($enrollment->housing_applied)
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span>Housing application submitted</span>
                    </div>
                    @endif
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span>Orientation registered</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span>Student account created</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span>Student ID generated</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Account Access -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Your Student Account</h2>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Account Credentials</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Your student account has been created with the following credentials:</p>
                            <div class="mt-2 bg-white rounded p-3">
                                <p><strong>Username:</strong> {{ $studentAccount->username ?? $enrollment->student_id }}</p>
                                <p><strong>Email:</strong> {{ $studentAccount->email ?? $application->email }}</p>
                                <p class="mt-2 text-xs">A temporary password has been sent to your email. Please change it upon first login.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('student.portal') }}" class="block p-4 border rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-blue-100 rounded-lg p-3 mr-4">
                            <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">Student Portal</h4>
                            <p class="text-sm text-gray-600">Access your dashboard, courses, and grades</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('registration.catalog') }}" class="block p-4 border rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-purple-100 rounded-lg p-3 mr-4">
                            <i class="fas fa-book text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">Course Registration</h4>
                            <p class="text-sm text-gray-600">Browse and register for your classes</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('financial.dashboard') }}" class="block p-4 border rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-green-100 rounded-lg p-3 mr-4">
                            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">Financial Services</h4>
                            <p class="text-sm text-gray-600">View bills and make payments</p>
                        </div>
                    </div>
                </a>
                
                <a href="https://email.{{ config('app.domain') }}" target="_blank" class="block p-4 border rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-red-100 rounded-lg p-3 mr-4">
                            <i class="fas fa-envelope text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">Student Email</h4>
                            <p class="text-sm text-gray-600">Access your university email account</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Important Dates Reminder -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Important Dates</h2>
            
            <div class="space-y-4">
                @if($orientationDate)
                <div class="flex items-start">
                    <div class="bg-purple-100 rounded-full p-2 mr-4 mt-1">
                        <i class="fas fa-calendar-day text-purple-600"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold">New Student Orientation</h4>
                                <p class="text-sm text-gray-600">{{ $orientationDate->format('l, F j, Y') }}</p>
                            </div>
                            <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">
                                {{ $orientationDate->diffInDays(now()) }} days
                            </span>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($moveInDate)
                <div class="flex items-start">
                    <div class="bg-blue-100 rounded-full p-2 mr-4 mt-1">
                        <i class="fas fa-home text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold">Move-In Day</h4>
                                <p class="text-sm text-gray-600">{{ $moveInDate->format('l, F j, Y') }}</p>
                            </div>
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm">
                                {{ $moveInDate->diffInDays(now()) }} days
                            </span>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="flex items-start">
                    <div class="bg-green-100 rounded-full p-2 mr-4 mt-1">
                        <i class="fas fa-graduation-cap text-green-600"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold">First Day of Classes</h4>
                                <p class="text-sm text-gray-600">{{ $firstDayOfClasses->format('l, F j, Y') }}</p>
                            </div>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                                {{ $firstDayOfClasses->diffInDays(now()) }} days
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-yellow-100 rounded-full p-2 mr-4 mt-1">
                        <i class="fas fa-calendar-times text-yellow-600"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold">Add/Drop Deadline</h4>
                                <p class="text-sm text-gray-600">{{ $addDropDeadline->format('l, F j, Y') }}</p>
                            </div>
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                                {{ $addDropDeadline->diffInDays(now()) }} days
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Steps Checklist -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Before Classes Begin</h2>
            
            <div class="space-y-3">
                <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" class="mt-1 rounded border-gray-300 text-green-600">
                    <div class="ml-3">
                        <p class="font-semibold">Set up your student email</p>
                        <p class="text-sm text-gray-600">Check regularly for important university communications</p>
                    </div>
                </label>
                
                <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" class="mt-1 rounded border-gray-300 text-green-600">
                    <div class="ml-3">
                        <p class="font-semibold">Register for classes</p>
                        <p class="text-sm text-gray-600">Work with your advisor to select appropriate courses</p>
                    </div>
                </label>
                
                <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" class="mt-1 rounded border-gray-300 text-green-600">
                    <div class="ml-3">
                        <p class="font-semibold">Order textbooks</p>
                        <p class="text-sm text-gray-600">Check the bookstore for required materials</p>
                    </div>
                </label>
                
                <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" class="mt-1 rounded border-gray-300 text-green-600">
                    <div class="ml-3">
                        <p class="font-semibold">Set up parking permit</p>
                        <p class="text-sm text-gray-600">If you'll be bringing a vehicle to campus</p>
                    </div>
                </label>
                
                <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" class="mt-1 rounded border-gray-300 text-green-600">
                    <div class="ml-3">
                        <p class="font-semibold">Review financial aid</p>
                        <p class="text-sm text-gray-600">Ensure all aid is properly applied to your account</p>
                    </div>
                </label>
                
                <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" class="mt-1 rounded border-gray-300 text-green-600">
                    <div class="ml-3">
                        <p class="font-semibold">Join student organizations</p>
                        <p class="text-sm text-gray-600">Explore clubs and activities that interest you</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Resources -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Helpful Resources</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-3">Academic Resources</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Academic Calendar
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Course Catalog
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Library Services
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Tutoring Center
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-3">Student Services</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Health Services
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Counseling Center
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Career Services
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Campus Safety
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-3">Campus Life</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Student Organizations
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Recreation Center
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Dining Services
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Campus Events
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-3">Technology</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>IT Help Desk
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>WiFi Setup
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Software Downloads
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-2"></i>Computer Labs
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center space-y-4">
        <div class="space-x-4">
            <a href="{{ route('student.portal') }}" class="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
                <i class="fas fa-home mr-2"></i> Go to Student Portal
            </a>
            <button onclick="window.print()" class="inline-block px-8 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700">
                <i class="fas fa-print mr-2"></i> Print Confirmation
            </button>
        </div>
        
        <p class="text-sm text-gray-600">
            A copy of this confirmation has been sent to your email at {{ $application->email }}
        </p>
    </div>

    <!-- Contact Support -->
    <div class="mt-12 bg-gray-50 rounded-lg p-6 text-center">
        <h3 class="font-semibold mb-2">Need Help?</h3>
        <p class="text-sm text-gray-600 mb-4">Our student support team is here to assist you</p>
        <div class="flex justify-center space-x-6 text-sm">
            <div>
                <i class="fas fa-phone text-gray-400 mr-2"></i>
                <span>(555) 123-4567</span>
            </div>
            <div>
                <i class="fas fa-envelope text-gray-400 mr-2"></i>
                <span>support@university.edu</span>
            </div>
            <div>
                <i class="fas fa-comments text-gray-400 mr-2"></i>
                <span>Live Chat Available</span>
            </div>
        </div>
    </div>
</div>

<!-- Confetti Animation -->
<div id="confetti-container"></div>

@endsection

@push('styles')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out;
    }
    
    #confetti-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
    }
    
    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #f9ca24);
        animation: fall linear;
    }
    
    @keyframes fall {
        to {
            transform: translateY(100vh) rotate(360deg);
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confetti animation on page load
    function createConfetti() {
        const container = document.getElementById('confetti-container');
        const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#6c5ce7', '#a29bfe'];
        
        for (let i = 0; i < 100; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = confetti.style.width;
                container.appendChild(confetti);
                
                setTimeout(() => confetti.remove(), 5000);
            }, i * 30);
        }
    }
    
    // Run confetti animation
    createConfetti();
    
    // Save checklist state in localStorage
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    
    // Load saved state
    checkboxes.forEach(checkbox => {
        const key = 'enrollment_checklist_' + checkbox.nextElementSibling.querySelector('p').textContent;
        const saved = localStorage.getItem(key);
        if (saved === 'true') {
            checkbox.checked = true;
        }
    });
    
    // Save state on change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const key = 'enrollment_checklist_' + this.nextElementSibling.querySelector('p').textContent;
            localStorage.setItem(key, this.checked);
        });
    });
});
</script>
@endpush