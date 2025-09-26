@extends('layouts.app')

@section('title', 'Course Catalog')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right"></i>
    <span>Course Catalog</span>
@endsection

@section('page-actions')
    <a href="{{ route('registration.cart') }}" class="btn btn-primary me-2">
        <i class="fas fa-shopping-cart me-1"></i> 
        Registration Cart
        @if(count($cartItems) > 0)
            <span class="badge bg-white text-primary ms-2">{{ count($cartItems) }}</span>
        @endif
    </a>
    <a href="{{ route('registration.schedule') }}" class="btn btn-success me-2">
        <i class="fas fa-calendar-alt me-1"></i> My Schedule
    </a>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-ellipsis-v me-1"></i> More
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('registration.history') }}">
                <i class="fas fa-history me-2"></i>Registration History
            </a></li>
            <li><a class="dropdown-item" href="{{ route('registration.holds') }}">
                <i class="fas fa-ban me-2"></i>View Holds
            </a></li>
            <li><a class="dropdown-item" href="{{ route('registration.waitlist') }}">
                <i class="fas fa-clock me-2"></i>Waitlist Status
            </a></li>
        </ul>
    </div>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Course Catalog</h2>
        <p class="text-muted">Browse and register for available courses</p>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('registration.catalog') }}">
                <div class="row mb-3">
                    <!-- Search -->
                    <div class="col-md-4">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by course code or title..." 
                               class="form-control">
                    </div>

                    <!-- Term -->
                    <div class="col-md-2">
                        <select name="term_id" class="form-select">
                            <option value="">All Terms</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ $selectedTerm == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department -->
                    <div class="col-md-2">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Credits -->
                    <div class="col-md-2">
                        <select name="credits" class="form-select">
                            <option value="">Any Credits</option>
                            <option value="1" {{ request('credits') == '1' ? 'selected' : '' }}>1 Credit</option>
                            <option value="2" {{ request('credits') == '2' ? 'selected' : '' }}>2 Credits</option>
                            <option value="3" {{ request('credits') == '3' ? 'selected' : '' }}>3 Credits</option>
                            <option value="4" {{ request('credits') == '4' ? 'selected' : '' }}>4 Credits</option>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </div>
                </div>

                <div class="row">
                    <!-- Delivery Mode -->
                    <div class="col-md-3">
                        <select name="delivery_mode" class="form-select">
                            <option value="">Any Mode</option>
                            <option value="traditional" {{ request('delivery_mode') == 'traditional' ? 'selected' : '' }}>In-Person</option>
                            <option value="online_sync" {{ request('delivery_mode') == 'online_sync' ? 'selected' : '' }}>Online Sync</option>
                            <option value="online_async" {{ request('delivery_mode') == 'online_async' ? 'selected' : '' }}>Online Async</option>
                            <option value="hybrid" {{ request('delivery_mode') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                            <option value="hyflex" {{ request('delivery_mode') == 'hyflex' ? 'selected' : '' }}>HyFlex</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <div class="col-md-3">
                        <a href="{{ route('registration.catalog') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear All Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Course Sections -->
    <div class="card shadow-sm">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Available Courses</h5>
        </div>
        <div class="card-body">
            @if($sections->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No courses found</h5>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                </div>
            @else
                @foreach($sections as $section)
                    <div class="card mb-3 course-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-9">
                                    <!-- Course Info -->
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">
                                                {{ $section->course->code }} - {{ $section->course->title }}
                                            </h5>
                                            <p class="text-muted mb-0">
                                                Section {{ $section->section_number }} | CRN: {{ $section->crn }}
                                            </p>
                                        </div>
                                        @php
                                            $modeClass = match($section->delivery_mode) {
                                                'traditional' => 'bg-success',
                                                'online_sync' => 'bg-primary',
                                                'online_async' => 'bg-purple',
                                                'hybrid' => 'bg-warning',
                                                'hyflex' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $modeClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $section->delivery_mode)) }}
                                        </span>
                                    </div>

                                    <!-- Schedule Info -->
                                    <div class="row small text-muted">
                                        <div class="col-md-4">
                                            <i class="fas fa-user-tie me-1"></i>
                                            <strong>Instructor:</strong> {{ $section->instructor->name ?? 'TBA' }}
                                        </div>
                                        <div class="col-md-4">
                                            <i class="fas fa-clock me-1"></i>
                                            <strong>Schedule:</strong>
                                            @if($section->days_of_week && $section->start_time)
                                                {{ $section->days_of_week }} {{ date('g:i A', strtotime($section->start_time)) }}
                                            @else
                                                TBA
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <strong>Room:</strong> {{ $section->room ?: 'Online' }}
                                        </div>
                                    </div>

                                    <!-- Course Details -->
                                    <div class="row small mt-2">
                                        <div class="col-md-4">
                                            <strong>Credits:</strong> {{ $section->course->credits }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Department:</strong> {{ $section->course->department }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Enrollment:</strong>
                                            <span class="{{ $section->current_enrollment >= $section->enrollment_capacity ? 'text-danger' : 'text-success' }}">
                                                {{ $section->current_enrollment }}/{{ $section->enrollment_capacity }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="col-md-3 text-end">
                                    @if(in_array($section->id, $enrolledSections))
                                        <button class="btn btn-success" disabled>
                                            <i class="fas fa-check me-1"></i> Enrolled
                                        </button>
                                    @elseif(in_array($section->id, $cartItems))
                                        <button class="btn btn-info" disabled>
                                            <i class="fas fa-shopping-cart me-1"></i> In Cart
                                        </button>
                                    @elseif($section->current_enrollment >= $section->enrollment_capacity)
                                        <button onclick="joinWaitlist({{ $section->id }})" class="btn btn-warning">
                                            <i class="fas fa-clock me-1"></i> Join Waitlist
                                        </button>
                                    @else
                                        <button onclick="addToCart({{ $section->id }})" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Add to Cart
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $sections->withQueryString()->links('custom.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}
.bg-purple {
    background-color: #6f42c1;
}
.course-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<script>
function addToCart(sectionId) {
    fetch('{{ route("registration.cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            section_id: sectionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.success);
            location.reload();
        } else {
            alert(data.error || 'Failed to add to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function joinWaitlist(sectionId) {
    if (confirm('Do you want to join the waitlist for this course?')) {
        fetch('{{ route("registration.waitlist.join") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                section_id: sectionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.success);
                location.reload();
            } else {
                alert(data.error || 'Failed to join waitlist');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
@endsection