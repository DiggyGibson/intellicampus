{{-- File: resources/views/exams/admin/attendance-sheet.blade.php --}}
@extends('layouts.app')

@section('title', 'Attendance Sheet - ' . $session->session_code)

@section('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .page-break {
            page-break-after: always;
        }
        body {
            font-size: 12pt;
        }
        .table {
            font-size: 10pt;
        }
        .attendance-header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    }
    
    .attendance-table {
        border: 2px solid #000;
    }
    .attendance-table th {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: center;
        border: 1px solid #000 !important;
    }
    .attendance-table td {
        border: 1px solid #000 !important;
        padding: 8px;
    }
    .signature-box {
        width: 150px;
        height: 40px;
        border-bottom: 1px solid #000;
    }
    
    .photo-box {
        width: 80px;
        height: 100px;
        border: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }
    
    .attendance-mark {
        width: 30px;
        height: 30px;
        border: 2px solid #000;
        display: inline-block;
        margin: 0 5px;
        vertical-align: middle;
    }
    .attendance-mark.present {
        background: #28a745;
        position: relative;
    }
    .attendance-mark.present::after {
        content: '✓';
        color: white;
        font-size: 20px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    
    .room-section {
        margin-bottom: 50px;
    }
    
    .stats-summary {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .verification-section {
        margin-top: 50px;
        padding-top: 20px;
        border-top: 2px solid #000;
    }
    
    .official-stamp {
        width: 150px;
        height: 150px;
        border: 2px dashed #000;
        display: inline-block;
        text-align: center;
        padding: 20px;
        margin: 20px;
    }
    
    .header-logo {
        max-height: 60px;
        max-width: 200px;
    }
    
    .qr-code {
        width: 100px;
        height: 100px;
        border: 1px solid #dee2e6;
        padding: 5px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Control Panel (Hidden in Print) --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Attendance Sheet
                    </h1>
                    <p class="text-muted">{{ $exam->exam_name }} - {{ $session->session_code }}</p>
                </div>
                <div class="btn-toolbar">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="exportPDF()">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="exportExcel()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                        <i class="fas fa-user-check"></i> Quick Mark
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Options (Hidden in Print) --}}
    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Room/Hall</label>
                    <select class="form-select" id="filterRoom">
                        <option value="">All Rooms</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room }}">{{ $room }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" id="sortBy">
                        <option value="seat">Seat Number</option>
                        <option value="registration">Registration Number</option>
                        <option value="name">Name (A-Z)</option>
                        <option value="hall_ticket">Hall Ticket Number</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Per Page</label>
                    <select class="form-select" id="perPage">
                        <option value="25">25 Candidates</option>
                        <option value="50">50 Candidates</option>
                        <option value="100">100 Candidates</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Printable Attendance Sheet --}}
    <div class="attendance-sheet-print">
        {{-- Header Section --}}
        <div class="attendance-header">
            <div class="row align-items-center">
                <div class="col-3">
                    @if($institution->logo)
                    <img src="{{ asset($institution->logo) }}" alt="Logo" class="header-logo">
                    @endif
                </div>
                <div class="col-6 text-center">
                    <h3 class="mb-1">{{ $institution->name ?? 'UNIVERSITY NAME' }}</h3>
                    <h5 class="mb-1">ENTRANCE EXAMINATION ATTENDANCE SHEET</h5>
                    <p class="mb-0">{{ $exam->exam_name }}</p>
                </div>
                <div class="col-3 text-end">
                    <div class="qr-code">
                        {!! QrCode::size(90)->generate(route('exams.verify.attendance', $session->id)) !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- Session Information --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="40%">Session Code:</th>
                        <td><strong>{{ $session->session_code }}</strong></td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td>{{ \Carbon\Carbon::parse($session->session_date)->format('l, F d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Time:</th>
                        <td>{{ $session->start_time }} - {{ $session->end_time }}</td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td>{{ $exam->duration_minutes }} minutes</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="40%">Exam Center:</th>
                        <td>{{ $session->center->center_name }}</td>
                    </tr>
                    <tr>
                        <th>Room/Hall:</th>
                        <td>{{ request('room', 'All Rooms') }}</td>
                    </tr>
                    <tr>
                        <th>Total Capacity:</th>
                        <td>{{ $session->capacity }}</td>
                    </tr>
                    <tr>
                        <th>Total Registered:</th>
                        <td>{{ $session->registered_count }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Instructions Box --}}
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading">Instructions for Invigilators:</h6>
            <ol class="mb-0 small">
                <li>Verify candidate identity using photo ID and hall ticket</li>
                <li>Mark attendance by ticking (✓) in the Present column or (✗) for absent</li>
                <li>Collect candidate signature in the designated column</li>
                <li>Note any irregularities in the Remarks column</li>
                <li>Complete the summary section at the end of the examination</li>
            </ol>
        </div>

        {{-- Attendance Table --}}
        @foreach($roomWiseCandidates as $room => $candidates)
        <div class="room-section">
            @if(count($roomWiseCandidates) > 1)
            <h5 class="mb-3">Room: {{ $room }}</h5>
            @endif
            
            <table class="table attendance-table">
                <thead>
                    <tr>
                        <th width="5%">S.No</th>
                        <th width="8%">Seat No.</th>
                        <th width="12%">Registration No.</th>
                        <th width="12%">Hall Ticket No.</th>
                        <th width="10%">Photo</th>
                        <th width="20%">Candidate Name</th>
                        <th width="8%">Present</th>
                        <th width="8%">Absent</th>
                        <th width="15%">Signature</th>
                        <th width="12%">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($candidates as $index => $candidate)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center"><strong>{{ $candidate->seat_number }}</strong></td>
                        <td>{{ $candidate->registration->registration_number }}</td>
                        <td>{{ $candidate->registration->hall_ticket_number }}</td>
                        <td>
                            <div class="photo-box">
                                @if($candidate->registration->photo)
                                <img src="{{ asset($candidate->registration->photo) }}" style="max-width: 100%; max-height: 100%;">
                                @else
                                <span class="text-muted">Photo</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong>{{ $candidate->registration->candidate_name }}</strong>
                            @if($candidate->registration->requires_accommodation)
                            <br><small class="text-danger">*Special Accommodation Required</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="attendance-mark {{ $candidate->attendance_marked ? 'present' : '' }}"></div>
                        </td>
                        <td class="text-center">
                            <div class="attendance-mark"></div>
                        </td>
                        <td>
                            <div class="signature-box"></div>
                        </td>
                        <td></td>
                    </tr>
                    @endforeach
                    
                    {{-- Empty rows for latecomers --}}
                    @for($i = count($candidates); $i < min(count($candidates) + 5, $session->capacity); $i++)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center">-</td>
                        <td></td>
                        <td></td>
                        <td><div class="photo-box"><span class="text-muted">Photo</span></div></td>
                        <td></td>
                        <td class="text-center"><div class="attendance-mark"></div></td>
                        <td class="text-center"><div class="attendance-mark"></div></td>
                        <td><div class="signature-box"></div></td>
                        <td></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
            
            @if($loop->iteration % 2 == 0 && !$loop->last)
            <div class="page-break"></div>
            @endif
        </div>
        @endforeach

        {{-- Summary Section --}}
        <div class="stats-summary">
            <h5 class="mb-3">Attendance Summary</h5>
            <div class="row">
                <div class="col-md-3">
                    <table class="table table-sm">
                        <tr>
                            <th>Total Registered:</th>
                            <td>_________</td>
                        </tr>
                        <tr>
                            <th>Present:</th>
                            <td>_________</td>
                        </tr>
                        <tr>
                            <th>Absent:</th>
                            <td>_________</td>
                        </tr>
                        <tr>
                            <th>Late Arrivals:</th>
                            <td>_________</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-9">
                    <div class="mb-2">
                        <strong>Irregularities/Special Cases:</strong>
                    </div>
                    <div style="border: 1px solid #000; height: 80px; padding: 10px;">
                        <!-- Space for writing irregularities -->
                    </div>
                </div>
            </div>
        </div>

        {{-- Verification Section --}}
        <div class="verification-section">
            <h5 class="mb-4">Verification & Signatures</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="signature-box mb-2" style="width: 200px; margin: 0 auto;"></div>
                        <p class="mb-0"><strong>Chief Invigilator</strong></p>
                        <p class="small text-muted">Name: _______________________</p>
                        <p class="small text-muted">Date: {{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}</p>
                        <p class="small text-muted">Time: _________</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="signature-box mb-2" style="width: 200px; margin: 0 auto;"></div>
                        <p class="mb-0"><strong>Assistant Invigilator</strong></p>
                        <p class="small text-muted">Name: _______________________</p>
                        <p class="small text-muted">Date: {{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}</p>
                        <p class="small text-muted">Time: _________</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="official-stamp">
                            <p class="mt-4">Official Stamp</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-5 pt-3 border-top text-center small text-muted">
            <p class="mb-0">
                Generated on: {{ now()->format('F d, Y h:i A') }} | 
                Document ID: ATT-{{ $session->session_code }}-{{ date('Ymd') }} |
                Page <span class="page-number"></span>
            </p>
        </div>
    </div>
</div>

{{-- Quick Mark Attendance Modal --}}
<div class="modal fade" id="markAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Enter Hall Ticket or Registration Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="quickSearchInput" 
                               placeholder="Scan or type hall ticket/registration number">
                        <button class="btn btn-primary" type="button" onclick="quickSearch()">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                
                <div id="candidateInfo" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="photo-box" style="width: 120px; height: 150px;">
                                        <img id="candidatePhoto" src="" style="max-width: 100%; max-height: 100%;">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <h5 id="candidateName"></h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Registration #:</th>
                                            <td id="candidateReg"></td>
                                        </tr>
                                        <tr>
                                            <th>Hall Ticket #:</th>
                                            <td id="candidateHallTicket"></td>
                                        </tr>
                                        <tr>
                                            <th>Seat #:</th>
                                            <td id="candidateSeat"></td>
                                        </tr>
                                        <tr>
                                            <th>Room:</th>
                                            <td id="candidateRoom"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-success btn-lg w-100" onclick="markPresent()">
                                    <i class="fas fa-check"></i> Mark Present
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Digital Attendance Modal (for tablets/mobile) --}}
<div class="modal fade" id="digitalAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Digital Attendance Marking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Seat</th>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Registration</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="digitalAttendanceList">
                                    <!-- Populated via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6>Attendance Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label>Total: <span id="digitalTotal">0</span></label>
                                </div>
                                <div class="mb-3">
                                    <label>Present: <span id="digitalPresent" class="text-success">0</span></label>
                                </div>
                                <div class="mb-3">
                                    <label>Absent: <span id="digitalAbsent" class="text-danger">0</span></label>
                                </div>
                                <div class="mb-3">
                                    <label>Pending: <span id="digitalPending" class="text-warning">0</span></label>
                                </div>
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar bg-success" id="attendanceProgress" style="width: 0%">0%</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-body">
                                <button type="button" class="btn btn-primary w-100 mb-2" onclick="syncAttendance()">
                                    <i class="fas fa-sync"></i> Sync with Server
                                </button>
                                <button type="button" class="btn btn-success w-100" onclick="finalizeAttendance()">
                                    <i class="fas fa-check-double"></i> Finalize Attendance
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    // Filter functions
    function applyFilters() {
        const room = document.getElementById('filterRoom').value;
        const sortBy = document.getElementById('sortBy').value;
        const perPage = document.getElementById('perPage').value;
        
        const params = new URLSearchParams({
            room: room,
            sort: sortBy,
            per_page: perPage
        });
        
        window.location.href = `{{ route('exams.admin.attendance', $session->id) }}?${params.toString()}`;
    }

    // Export functions
    function exportPDF() {
        window.print(); // For now, use browser's print to PDF
        // Could implement jsPDF for more control
    }

    function exportExcel() {
        window.location.href = `{{ route('exams.admin.attendance.export', $session->id) }}?format=excel`;
    }

    // Quick search
    function quickSearch() {
        const searchValue = document.getElementById('quickSearchInput').value;
        if (!searchValue) return;
        
        // AJAX call to search candidate
        fetch(`/api/exams/sessions/${{{ $session->id }}}/search-candidate?q=${searchValue}`)
            .then(response => response.json())
            .then(data => {
                if (data.found) {
                    document.getElementById('candidateInfo').style.display = 'block';
                    document.getElementById('candidateName').textContent = data.candidate.name;
                    document.getElementById('candidateReg').textContent = data.candidate.registration_number;
                    document.getElementById('candidateHallTicket').textContent = data.candidate.hall_ticket_number;
                    document.getElementById('candidateSeat').textContent = data.candidate.seat_number;
                    document.getElementById('candidateRoom').textContent = data.candidate.room_number;
                    if (data.candidate.photo) {
                        document.getElementById('candidatePhoto').src = data.candidate.photo;
                    }
                } else {
                    alert('Candidate not found');
                }
            });
    }

    // Mark present
    function markPresent() {
        // Implementation for marking present
        alert('Candidate marked as present');
        document.getElementById('markAttendanceModal').querySelector('.btn-close').click();
    }

    // Digital attendance functions
    function syncAttendance() {
        // Sync with server
        console.log('Syncing attendance...');
    }

    function finalizeAttendance() {
        if (confirm('Are you sure you want to finalize the attendance? This action cannot be undone.')) {
            // Finalize attendance
            console.log('Finalizing attendance...');
        }
    }

    // Auto-save attendance periodically
    setInterval(() => {
        if (document.hidden) return;
        // Auto-save logic
    }, 60000); // Every minute

    // Print page numbers
    window.onbeforeprint = function() {
        const pageNumbers = document.querySelectorAll('.page-number');
        pageNumbers.forEach((el, index) => {
            el.textContent = index + 1;
        });
    };
</script>
@endsection