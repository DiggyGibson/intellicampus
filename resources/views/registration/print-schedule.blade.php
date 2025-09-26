<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Schedule - {{ $currentTerm->name }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 8.5in;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1a202c;
        }
        .header h2 {
            margin: 5px 0;
            color: #4a5568;
            font-weight: normal;
        }
        .student-info {
            background: #f7fafc;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .student-info p {
            margin: 5px 0;
        }
        .schedule-grid {
            margin-bottom: 30px;
        }
        .schedule-grid table {
            width: 100%;
            border-collapse: collapse;
        }
        .schedule-grid th {
            background: #2b6cb0;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        .schedule-grid td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: top;
            min-height: 60px;
            font-size: 11px;
        }
        .time-slot {
            background: #edf2f7;
            font-weight: bold;
            text-align: center;
        }
        .course-block {
            background: #bee3f8;
            padding: 5px;
            margin: 2px 0;
            border-radius: 3px;
            border-left: 3px solid #2b6cb0;
        }
        .course-block .course-code {
            font-weight: bold;
            color: #2b6cb0;
        }
        .course-list {
            margin-bottom: 20px;
        }
        .course-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .course-list th {
            background: #e2e8f0;
            padding: 8px;
            text-align: left;
            font-size: 12px;
            border: 1px solid #cbd5e0;
        }
        .course-list td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .summary {
            background: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .print-button {
            background: #2b6cb0;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 20px 0;
        }
        .print-button:hover {
            background: #2c5282;
        }
    </style>
</head>
<body>
    <!-- Print Button (hidden when printing) -->
    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="print-button">🖨️ Print Schedule</button>
        <button onclick="window.close()" class="print-button" style="background: #718096;">✕ Close</button>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>INTELLICAMPUS UNIVERSITY</h1>
        <h2>Student Schedule - {{ $currentTerm->name }}</h2>
    </div>

    <!-- Student Information -->
    <div class="student-info">
        <p><strong>Student Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
        <p><strong>Student ID:</strong> {{ $student->student_id }}</p>
        <p><strong>Academic Level:</strong> {{ ucfirst($student->academic_level ?? 'Undergraduate') }}</p>
        <p><strong>Print Date:</strong> {{ now()->format('F j, Y g:i A') }}</p>
    </div>

    <!-- Weekly Schedule Grid -->
    <div class="schedule-grid">
        <h3 style="margin-bottom: 10px;">Weekly Schedule</h3>
        <table>
            <thead>
                <tr>
                    <th width="10%">Time</th>
                    <th width="18%">Monday</th>
                    <th width="18%">Tuesday</th>
                    <th width="18%">Wednesday</th>
                    <th width="18%">Thursday</th>
                    <th width="18%">Friday</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $times = [
                        '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM',
                        '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM', '6:00 PM'
                    ];
                    $dayMap = ['M' => 1, 'T' => 2, 'W' => 3, 'R' => 4, 'F' => 5];
                @endphp
                
                @foreach($times as $time)
                    <tr>
                        <td class="time-slot">{{ $time }}</td>
                        @foreach(['M', 'T', 'W', 'R', 'F'] as $day)
                            <td>
                                @if(isset($weeklySchedule[$day]))
                                    @foreach($weeklySchedule[$day] as $class)
                                        @php
                                            $classStart = \Carbon\Carbon::parse(explode(' - ', $class['time'])[0]);
                                            $currentTime = \Carbon\Carbon::parse($time);
                                        @endphp
                                        @if($classStart->format('g:00 A') == $time)
                                            <div class="course-block">
                                                <div class="course-code">{{ $class['course'] }}</div>
                                                <div>{{ $class['room'] }}</div>
                                                <div>{{ $class['time'] }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Course List -->
    <div class="course-list">
        <h3 style="margin-bottom: 10px;">Enrolled Courses</h3>
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Section</th>
                    <th>Credits</th>
                    <th>Schedule</th>
                    <th>Location</th>
                    <th>Instructor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrollments as $enrollment)
                    <tr>
                        <td><strong>{{ $enrollment->course_code }}</strong></td>
                        <td>{{ $enrollment->title }}</td>
                        <td>{{ $enrollment->section_number }}</td>
                        <td>{{ $enrollment->credits }}</td>
                        <td>
                            @if($enrollment->days_of_week && $enrollment->start_time)
                                {{ $enrollment->days_of_week }}
                                {{ \Carbon\Carbon::parse($enrollment->start_time)->format('g:i A') }} -
                                {{ \Carbon\Carbon::parse($enrollment->end_time)->format('g:i A') }}
                            @else
                                Online/Async
                            @endif
                        </td>
                        <td>
                            @if($enrollment->building && $enrollment->room)
                                {{ $enrollment->building }} {{ $enrollment->room }}
                            @else
                                Online
                            @endif
                        </td>
                        <td>{{ $enrollment->instructor_name ?? 'TBA' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-item">
            <strong>Total Courses:</strong> {{ $enrollments->count() }}
        </div>
        <div class="summary-item">
            <strong>Total Credits:</strong> {{ $totalCredits }}
        </div>
        <div class="summary-item">
            <strong>Enrollment Status:</strong> 
            @if($totalCredits >= 12)
                Full-Time
            @else
                Part-Time
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is an official schedule for {{ $currentTerm->name }}</p>
        <p>IntelliCampus University | registrar@intellicampus.edu | www.intellicampus.edu</p>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>