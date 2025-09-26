<?php

// ============================================
// resources/views/pdf/override-request.blade.php
// ============================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Override Request #{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #2c3e50; margin-bottom: 5px; }
        .section { margin-bottom: 20px; }
        .section h3 { background: #ecf0f1; padding: 5px 10px; margin-bottom: 10px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; border-bottom: 1px solid #ecf0f1; }
        .info-table td:first-child { font-weight: bold; width: 30%; }
        .status-approved { color: #27ae60; font-weight: bold; }
        .status-denied { color: #e74c3c; font-weight: bold; }
        .status-pending { color: #f39c12; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 10pt; color: #7f8c8d; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REGISTRATION OVERRIDE REQUEST</h1>
        <p>Request ID: #{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p>Generated: {{ $generated_at->format('F d, Y h:i A') }}</p>
    </div>
    
    <div class="section">
        <h3>Student Information</h3>
        <table class="info-table">
            <tr>
                <td>Name:</td>
                <td>{{ $student->user->name }}</td>
            </tr>
            <tr>
                <td>Student ID:</td>
                <td>{{ $student->student_id }}</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $student->email }}</td>
            </tr>
            <tr>
                <td>Academic Level:</td>
                <td>{{ ucfirst($student->academic_level) }}</td>
            </tr>
            <tr>
                <td>GPA:</td>
                <td>{{ number_format($student->cumulative_gpa, 2) }}</td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h3>Request Details</h3>
        <table class="info-table">
            <tr>
                <td>Request Type:</td>
                <td>{{ $request->type_label }}</td>
            </tr>
            <tr>
                <td>Submitted Date:</td>
                <td>{{ $request->created_at->format('F d, Y h:i A') }}</td>
            </tr>
            <tr>
                <td>Status:</td>
                <td class="status-{{ $request->status }}">{{ strtoupper($request->status) }}</td>
            </tr>
            @if($request->request_type == 'credit_overload')
            <tr>
                <td>Current Credits:</td>
                <td>{{ $request->current_credits }}</td>
            </tr>
            <tr>
                <td>Requested Credits:</td>
                <td>{{ $request->requested_credits }}</td>
            </tr>
            @elseif($request->course)
            <tr>
                <td>Course:</td>
                <td>{{ $request->course->code }} - {{ $request->course->title }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div class="section">
        <h3>Student Justification</h3>
        <p>{{ $request->student_justification }}</p>
    </div>
    
    @if($request->status != 'pending')
    <div class="section">
        <h3>Decision Information</h3>
        <table class="info-table">
            <tr>
                <td>Decision:</td>
                <td class="status-{{ $request->status }}">{{ strtoupper($request->status) }}</td>
            </tr>
            <tr>
                <td>Decided By:</td>
                <td>{{ $approver ? $approver->name : 'System' }}</td>
            </tr>
            <tr>
                <td>Decision Date:</td>
                <td>{{ $request->approval_date ? $request->approval_date->format('F d, Y h:i A') : 'N/A' }}</td>
            </tr>
            @if($request->override_code)
            <tr>
                <td>Override Code:</td>
                <td><strong>{{ $request->override_code }}</strong></td>
            </tr>
            <tr>
                <td>Code Expires:</td>
                <td>{{ $request->override_expires_at->format('F d, Y h:i A') }}</td>
            </tr>
            @endif
        </table>
        
        @if($request->approver_notes)
        <h4>Decision Notes:</h4>
        <p>{{ $request->approver_notes }}</p>
        @endif
    </div>
    @endif
    
    <div class="footer">
        <p>This is an official document from the IntelliCampus Registration System</p>
        <p>For questions, contact the Registrar's Office</p>
    </div>
</body>
</html>
