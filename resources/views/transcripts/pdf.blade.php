<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $transcriptData['type'] === 'official' ? 'Official' : 'Unofficial' }} Transcript</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .institution-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .transcript-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(200, 200, 200, 0.3);
            z-index: -1;
        }
        
        .student-info {
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-table td {
            padding: 2px 0;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        
        .term-header {
            background-color: #f0f0f0;
            padding: 5px;
            margin: 15px 0 5px 0;
            font-weight: bold;
        }
        
        .course-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .course-table th {
            background-color: #e0e0e0;
            border: 1px solid #999;
            padding: 3px;
            text-align: left;
            font-size: 10pt;
        }
        
        .course-table td {
            border: 1px solid #999;
            padding: 3px;
            font-size: 10pt;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .summary-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #000;
        }
        
        .gpa-summary {
            margin: 20px 0;
            padding: 10px;
            background-color: #f9f9f9;
        }
        
        .signature-section {
            margin-top: 50px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 250px;
            margin: 40px auto 5px auto;
        }
        
        .qr-code {
            position: absolute;
            bottom: 30px;
            right: 30px;
            width: 100px;
            height: 100px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    @if($transcriptData['type'] === 'unofficial')
    <div class="watermark">UNOFFICIAL</div>
    @endif

    <!-- Header -->
    <div class="header">
        <div class="institution-name">{{ $transcriptData['institution']['name'] }}</div>
        <div>{{ $transcriptData['institution']['address'] }}</div>
        <div>{{ $transcriptData['institution']['city'] }}, {{ $transcriptData['institution']['state'] }} {{ $transcriptData['institution']['zip'] }}</div>
        <div>Phone: {{ $transcriptData['institution']['phone'] }}</div>
        <div class="transcript-title">
            {{ $transcriptData['type'] === 'official' ? 'OFFICIAL' : 'UNOFFICIAL' }} ACADEMIC TRANSCRIPT
        </div>
    </div>

    <!-- Student Information -->
    <div class="student-info">
        <table class="info-table">
            <tr>
                <td class="info-label">Student Name:</td>
                <td>{{ $transcriptData['student']['name'] }}</td>
                <td class="info-label">Student ID:</td>
                <td>{{ $transcriptData['student']['student_id'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Date of Birth:</td>
                <td>{{ $transcriptData['student']['date_of_birth'] }}</td>
                <td class="info-label">Program:</td>
                <td>{{ $transcriptData['student']['program'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Major:</td>
                <td>{{ $transcriptData['student']['major'] }}</td>
                <td class="info-label">Minor:</td>
                <td>{{ $transcriptData['student']['minor'] ?? 'None' }}</td>
            </tr>
            <tr>
                <td class="info-label">Enrollment Date:</td>
                <td>{{ $transcriptData['student']['enrollment_date'] }}</td>
                <td class="info-label">Status:</td>
                <td>{{ $transcriptData['student']['status'] }}</td>
            </tr>
        </table>
    </div>

    <!-- Academic Record -->
    @foreach($transcriptData['academic_record'] as $term)
    <div class="term-header">
        {{ $term['term_name'] }} - {{ $term['academic_year'] }}
        ({{ $term['start_date'] }} - {{ $term['end_date'] }})
    </div>
    
    <table class="course-table">
        <thead>
            <tr>
                <th width="15%">Course</th>
                <th width="45%">Title</th>
                <th width="10%" class="text-center">Credits</th>
                <th width="10%" class="text-center">Grade</th>
                <th width="10%" class="text-center">Points</th>
                <th width="10%" class="text-center">Quality Pts</th>
            </tr>
        </thead>
        <tbody>
            @foreach($term['courses'] as $course)
            <tr>
                <td>{{ $course['code'] }}</td>
                <td>{{ $course['title'] }}</td>
                <td class="text-center">{{ number_format($course['credits'], 1) }}</td>
                <td class="text-center">{{ $course['grade'] }}</td>
                <td class="text-center">{{ number_format($course['grade_points'], 2) }}</td>
                <td class="text-center">{{ number_format($course['quality_points'], 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td colspan="2" class="text-right">Term Totals:</td>
                <td class="text-center">{{ number_format($term['term_credits_attempted'], 1) }}</td>
                <td></td>
                <td></td>
                <td class="text-center">{{ number_format($term['term_quality_points'], 2) }}</td>
            </tr>
            <tr style="background-color: #f5f5f5;">
                <td colspan="4" class="text-right">Term GPA: <strong>{{ number_format($term['term_gpa'], 2) }}</strong></td>
                <td colspan="2" class="text-right">Cumulative GPA: <strong>{{ number_format($term['cumulative_gpa'], 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <!-- Summary -->
    <div class="summary-section">
        <h3>Academic Summary</h3>
        <div class="gpa-summary">
            <table style="width: 100%;">
                <tr>
                    <td><strong>Total Credits Attempted:</strong> {{ $transcriptData['summary']['total_credits_attempted'] }}</td>
                    <td><strong>Total Credits Earned:</strong> {{ $transcriptData['summary']['total_credits_earned'] }}</td>
                </tr>
                <tr>
                    <td><strong>Cumulative GPA:</strong> {{ number_format($transcriptData['summary']['cumulative_gpa'], 2) }}</td>
                    <td><strong>Academic Standing:</strong> {{ $transcriptData['summary']['academic_standing'] }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Honors -->
    @if(!empty($transcriptData['honors']))
    <div style="margin-top: 20px;">
        <h4>Honors and Awards</h4>
        <ul>
            @foreach($transcriptData['honors'] as $honor)
            <li>{{ $honor['type'] }} - {{ $honor['term'] ?? $honor['date'] ?? '' }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Degree Information -->
    @if($transcriptData['degree'])
    <div style="margin-top: 20px; padding: 10px; background-color: #e8f5e9;">
        <h4>Degree Awarded</h4>
        <p><strong>{{ $transcriptData['degree']['degree_type'] }}</strong> in {{ $transcriptData['degree']['major'] }}</p>
        @if($transcriptData['degree']['minor'])
        <p>Minor: {{ $transcriptData['degree']['minor'] }}</p>
        @endif
        <p>Conferred: {{ $transcriptData['degree']['graduation_date'] }}</p>
        @if($transcriptData['degree']['honors'])
        <p><em>{{ $transcriptData['degree']['honors'] }}</em></p>
        @endif
    </div>
    @endif

    <!-- Signature Section (Official Only) -->
    @if($transcriptData['type'] === 'official')
    <div class="signature-section">
        <div class="signature-line"></div>
        <div class="text-center">
            {{ $transcriptData['institution']['registrar'] }}<br>
            {{ $transcriptData['institution']['registrar_title'] }}<br>
            Date: {{ $transcriptData['generated_at']->format('F d, Y') }}
        </div>
    </div>
    
    <!-- QR Code for Verification -->
    @if(isset($transcriptData['qr_code']))
    <div class="qr-code">
        <img src="data:image/png;base64,{{ $transcriptData['qr_code'] }}" width="100" height="100">
        <div style="font-size: 8pt; text-align: center;">
            Verify: {{ $transcriptData['verification_code'] }}
        </div>
    </div>
    @endif
    @endif

    <!-- Footer -->
    <div style="position: fixed; bottom: 10px; left: 20px; right: 20px; font-size: 8pt; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
        Generated: {{ $transcriptData['generated_at']->format('F d, Y h:i A') }}
        @if($transcriptData['verification_code'])
        | Verification Code: {{ $transcriptData['verification_code'] }}
        @endif
        | Page <span class="pagenum"></span>
    </div>
</body>
</html>