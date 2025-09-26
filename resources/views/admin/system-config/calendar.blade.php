{{-- File: resources/views/admin/system-config/calendar.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Academic Calendar Management</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.system-config.index') }}">System Configuration</a></li>
                            <li class="breadcrumb-item active">Academic Calendar</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.system-config.calendar.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Calendar Alert --}}
    @if($activeCalendar)
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <strong>Active Calendar:</strong> {{ $activeCalendar->name }} ({{ $activeCalendar->academic_year }})
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> <strong>No Active Calendar:</strong> Please set an active academic calendar.
        </div>
    @endif

    {{-- Calendars List --}}
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">Academic Calendars</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Academic Year</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Events</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calendars as $calendar)
                            <tr>
                                <td>
                                    <strong>{{ $calendar->name }}</strong>
                                    @if($calendar->description)
                                        <br><small class="text-muted">{{ $calendar->description }}</small>
                                    @endif
                                </td>
                                <td>{{ $calendar->academic_year }}</td>
                                <td>{{ \Carbon\Carbon::parse($calendar->year_start)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($calendar->year_end)->format('M d, Y') }}</td>
                                <td>
                                    @if($calendar->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $calendar->events_count ?? 0 }} events</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.system-config.calendar.events', $calendar->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Manage Events">
                                            <i class="fas fa-calendar-day"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewCalendar({{ $calendar->id }})" title="View Calendar">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$calendar->is_active)
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="activateCalendar({{ $calendar->id }})" title="Set as Active">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteCalendar({{ $calendar->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No academic calendars found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            {{ $calendars->links() }}
        </div>
    </div>

    {{-- Calendar View Modal --}}
    <div class="modal fade" id="calendarViewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Academic Calendar View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="calendarContainer"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
<script>
function activateCalendar(id) {
    if (confirm('Set this calendar as active? This will deactivate the current active calendar.')) {
        fetch(`/admin/system-config/calendar/${id}/activate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

function deleteCalendar(id) {
    if (confirm('Are you sure you want to delete this calendar? This action cannot be undone.')) {
        fetch(`/admin/system-config/calendar/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

function viewCalendar(id) {
    const modal = new bootstrap.Modal(document.getElementById('calendarViewModal'));
    
    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendarContainer');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        events: `/admin/system-config/calendar/${id}/events/json`,
        eventClick: function(info) {
            alert(info.event.title + '\n' + info.event.extendedProps.description);
        }
    });
    
    modal.show();
    calendar.render();
}
</script>
@endpush
@endsection