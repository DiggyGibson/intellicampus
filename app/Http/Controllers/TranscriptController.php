<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\TranscriptRequest;
use App\Models\TranscriptVerification;
use App\Models\TranscriptLog;
use App\Models\StudentHonor;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class TranscriptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['verify']);
    }

    /**
     * Display transcript main page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if user is admin/registrar
        if ($user->hasRole(['super-administrator', 'admin', 'registrar', 'academic-administrator'])) {
            // Admin view - show all transcript requests and allow searching for students
            $recentRequests = TranscriptRequest::with(['student.user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            $pendingRequests = TranscriptRequest::where('status', 'pending')
                ->with(['student.user'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get students for dropdown or search
            $students = Student::with('user')
                ->orderBy('student_id')
                ->get();
            
            return view('transcripts.admin-index', compact('recentRequests', 'pendingRequests', 'students'));
        }
        
        // Regular student view
        $student = $user->student;
        
        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'No student record found for your account.');
        }

        // Get student's transcript requests
        $recentRequests = TranscriptRequest::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('transcripts.index', compact('student', 'recentRequests'));
    }

    /**
     * Display transcript for viewing
     */
    public function view(Request $request, $studentId = null)
    {
        $user = Auth::user();
        
        // Admins and registrars can view any student's transcript
        if ($user->hasRole(['super-administrator', 'admin', 'registrar', 'academic-administrator'])) {
            if (!$studentId) {
                // If no student ID provided, show a student selection page
                $students = Student::with('user')->orderBy('student_id')->get();
                return view('transcripts.select-student', compact('students'));
            }
            
            $student = Student::findOrFail($studentId);
        } else {
            // Regular students can only view their own transcript
            $student = $user->student;
            if (!$student) {
                return redirect()->route('dashboard')
                    ->with('error', 'No student record found for your account.');
            }
            
            // Prevent students from viewing other students' transcripts
            if ($studentId && $studentId != $student->id) {
                abort(403, 'Unauthorized access to transcript.');
            }
        }

        // Log the view action
        $this->logTranscriptAction($student, 'viewed', 'unofficial');

        // Build transcript data
        $transcriptData = $this->buildTranscriptData($student, 'unofficial');

        return view('transcripts.view', compact('transcriptData', 'student'));
    }

    /**
     * Generate PDF transcript
     */
    public function generatePDF(Request $request, $studentId = null)
    {
        $user = Auth::user();
        
        // Handle default for backward compatibility route
        if (!$request->has('type')) {
            $request->merge(['type' => $request->route()->defaults['type'] ?? 'unofficial']);
        }

        $request->validate([
            'type' => 'required|in:official,unofficial',
            'purpose' => 'nullable|string|max:255',
        ]);

        // Determine which student's transcript to generate
        if ($user->hasRole(['super-administrator', 'admin', 'registrar', 'academic-administrator'])) {
            if (!$studentId) {
                return redirect()->route('transcripts.index')
                    ->with('error', 'Please select a student to generate transcript for.');
            }
            $student = Student::findOrFail($studentId);
        } else {
            $student = $user->student;
            if (!$student) {
                return redirect()->back()
                    ->with('error', 'No student record found.');
            }
            
            // Prevent students from generating other students' transcripts
            if ($studentId && $studentId != $student->id) {
                abort(403, 'Unauthorized access to transcript.');
            }
        }

        // Check for holds if official transcript (only for students, admins can override)
        if ($request->type === 'official' && !$user->hasRole(['super-administrator', 'admin', 'registrar'])) {
            $holds = $this->checkStudentHolds($student);
            if (!empty($holds)) {
                return redirect()->back()
                    ->with('error', 'Cannot generate official transcript. Student has holds: ' . implode(', ', $holds));
            }
        }

        // Build transcript data
        $transcriptData = $this->buildTranscriptData($student, $request->type);

        // Generate verification code for official transcripts
        if ($request->type === 'official') {
            $verification = $this->createVerification($student, $request->type);
            $transcriptData['verification_code'] = $verification->verification_code;
            $transcriptData['qr_code'] = $this->generateQRCode($verification->verification_code);
        }

        // Log the generation
        $this->logTranscriptAction($student, 'generated', $request->type, $request->purpose);

        // Generate PDF
        $pdf = PDF::loadView('transcripts.pdf', compact('transcriptData', 'student'));
        $pdf->setPaper('letter', 'portrait');

        $filename = sprintf(
            '%s_transcript_%s_%s.pdf',
            $request->type,
            $student->student_id,
            now()->format('Y-m-d')
        );

        // Log download
        $this->logTranscriptAction($student, 'downloaded', $request->type);

        return $pdf->download($filename);
    }

    /**
     * Show transcript request form
     */
    public function requestForm()
    {
        $user = Auth::user();
        
        // Admins can request transcripts for any student
        if ($user->hasRole(['super-administrator', 'admin', 'registrar', 'academic-administrator'])) {
            $students = Student::with('user')->orderBy('student_id')->get();
            
            // Get all pending requests
            $pendingRequests = TranscriptRequest::whereIn('status', ['pending', 'processing'])
                ->with(['student.user'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get request history
            $requestHistory = TranscriptRequest::with(['student.user'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
            
            return view('transcripts.admin-request', compact('students', 'pendingRequests', 'requestHistory'));
        }
        
        // Regular student view
        $student = $user->student;
        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'No student record found.');
        }

        // Check for existing pending requests
        $pendingRequests = TranscriptRequest::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'processing'])
            ->get();

        // Get request history
        $requestHistory = TranscriptRequest::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('transcripts.request', compact('student', 'pendingRequests', 'requestHistory'));
    }

    /**
     * Submit transcript request
     */
    public function submitRequest(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:students,id', // For admin requests
            'type' => 'required|in:official,unofficial',
            'delivery_method' => 'required|in:electronic,mail,pickup',
            'copies' => 'required|integer|min:1|max:10',
            'recipient_name' => 'required|string|max:255',
            'recipient_email' => 'nullable|required_if:delivery_method,electronic|email',
            'mailing_address' => 'nullable|required_if:delivery_method,mail|string',
            'purpose' => 'required|string|max:255',
            'rush_order' => 'boolean',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        
        // Determine which student this request is for
        if ($user->hasRole(['super-administrator', 'admin', 'registrar', 'academic-administrator'])) {
            // Admin is requesting for a specific student
            if (empty($validated['student_id'])) {
                return redirect()->back()
                    ->with('error', 'Please select a student for the transcript request.')
                    ->withInput();
            }
            $student = Student::findOrFail($validated['student_id']);
        } else {
            // Student is requesting their own transcript
            $student = $user->student;
            if (!$student) {
                return redirect()->back()->with('error', 'No student record found.');
            }
        }

        // Check for holds if requesting official transcript (admins can override)
        if ($validated['type'] === 'official' && !$user->hasRole(['super-administrator', 'admin', 'registrar'])) {
            $holds = $this->checkStudentHolds($student);
            if (!empty($holds)) {
                return redirect()->back()
                    ->with('error', 'Cannot request official transcript due to holds: ' . implode(', ', $holds));
            }
        }

        // Calculate fee
        $fee = $this->calculateTranscriptFee(
            $validated['type'],
            $validated['copies'],
            $validated['delivery_method'],
            $validated['rush_order'] ?? false
        );

        DB::beginTransaction();
        try {
            // Create transcript request
            $transcriptRequest = TranscriptRequest::create([
                'student_id' => $student->id,
                'request_number' => $this->generateRequestNumber(),
                'type' => $validated['type'],
                'delivery_method' => $validated['delivery_method'],
                'copies' => $validated['copies'],
                'recipient_name' => $validated['recipient_name'],
                'recipient_email' => $validated['recipient_email'] ?? null,
                'mailing_address' => $validated['mailing_address'] ?? null,
                'purpose' => $validated['purpose'],
                'rush_order' => $validated['rush_order'] ?? false,
                'special_instructions' => $validated['special_instructions'] ?? null,
                'fee' => $fee,
                'payment_status' => $fee > 0 ? 'pending' : 'not_required',
                'status' => 'pending',
                'requested_by' => Auth::id(),
                'requested_at' => now(),
            ]);

            // Log the request
            $this->logTranscriptAction($student, 'requested', $validated['type'], $validated['purpose'], $transcriptRequest->id);

            // Create billing item if there's a fee (only for students, not when admin requests)
            if ($fee > 0 && !$user->hasRole(['super-administrator', 'admin', 'registrar']) && class_exists(\App\Models\BillingItem::class)) {
                \App\Models\BillingItem::create([
                    'student_id' => $student->id,
                    'type' => 'transcript_fee',
                    'description' => sprintf(
                        '%s Transcript Request #%s - %d copies (%s delivery)',
                        ucfirst($validated['type']),
                        $transcriptRequest->request_number,
                        $validated['copies'],
                        $validated['delivery_method']
                    ),
                    'amount' => $fee,
                    'due_date' => now()->addDays(7),
                    'is_paid' => false,
                ]);
            }

            DB::commit();

            return redirect()->route('transcripts.request.status', $transcriptRequest)
                ->with('success', 'Transcript request submitted successfully. Request #' . $transcriptRequest->request_number);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Transcript request failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit transcript request. Please try again.');
        }
    }

    /**
     * Admin methods for managing transcript requests
     */
    public function allRequests()
    {
        $this->authorize('viewAny', TranscriptRequest::class);
        
        $requests = TranscriptRequest::with(['student.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('transcripts.admin.all-requests', compact('requests'));
    }
    
    public function pendingRequests()
    {
        $this->authorize('viewAny', TranscriptRequest::class);
        
        $requests = TranscriptRequest::where('status', 'pending')
            ->with(['student.user'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('transcripts.admin.pending-requests', compact('requests'));
    }
    
    public function processRequest(TranscriptRequest $transcriptRequest)
    {
        $this->authorize('update', $transcriptRequest);
        
        $transcriptRequest->update([
            'status' => 'processing',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);
        
        $this->logTranscriptAction(
            $transcriptRequest->student,
            'processed',
            $transcriptRequest->type,
            null,
            $transcriptRequest->id
        );
        
        return redirect()->back()
            ->with('success', 'Transcript request is now being processed.');
    }
    
    public function completeRequest(Request $request, TranscriptRequest $transcriptRequest)
    {
        $this->authorize('update', $transcriptRequest);
        
        $validated = $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        $transcriptRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'tracking_number' => $validated['tracking_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);
        
        $this->logTranscriptAction(
            $transcriptRequest->student,
            'completed',
            $transcriptRequest->type,
            null,
            $transcriptRequest->id
        );
        
        return redirect()->back()
            ->with('success', 'Transcript request has been completed.');
    }

    /**
     * Build comprehensive transcript data
     */
    protected function buildTranscriptData(Student $student, $type = 'unofficial')
    {
        // Get all enrollments with grades, grouped by term
        $enrollments = Enrollment::where('student_id', $student->id)
            ->whereIn('enrollment_status', ['completed', 'graded'])
            ->with(['section.course', 'section.term', 'grade'])
            ->orderBy('created_at')
            ->get();

        // Group by term
        $termGroups = $enrollments->groupBy(function ($enrollment) {
            return $enrollment->section->term_id ?? 0;
        });

        // Build academic record
        $academicRecord = [];
        $totalCreditsEarned = 0;
        $totalCreditsAttempted = 0;
        $totalQualityPoints = 0;

        foreach ($termGroups as $termId => $termEnrollments) {
            if (!$termId) continue; // Skip if no term
            
            $term = $termEnrollments->first()->section->term;
            if (!$term) continue;
            
            $termData = [
                'term_id' => $term->id,
                'term_name' => $term->name,
                'academic_year' => $term->academic_year,
                'start_date' => $term->start_date ? $term->start_date->format('M d, Y') : 'N/A',
                'end_date' => $term->end_date ? $term->end_date->format('M d, Y') : 'N/A',
                'courses' => [],
                'term_credits_attempted' => 0,
                'term_credits_earned' => 0,
                'term_quality_points' => 0,
                'term_gpa' => 0,
                'credits_attempted' => 0, // Add this for view compatibility
                'quality_points' => 0,    // Add this for view compatibility
            ];

            foreach ($termEnrollments as $enrollment) {
                $course = $enrollment->section->course;
                $grade = $enrollment->grade;
                
                if (!$grade) continue;
                
                $gradePoints = $this->getGradePoints($grade->letter_grade ?? 'F');
                $qualityPoints = $gradePoints * $course->credits;
                
                $courseData = [
                    'code' => $course->code,
                    'title' => $course->title,
                    'credits' => $course->credits,
                    'grade' => $grade->letter_grade ?? 'IP',
                    'grade_points' => $gradePoints,
                    'quality_points' => $qualityPoints,
                ];

                $termData['courses'][] = $courseData;

                // Update term totals
                if (!in_array($grade->letter_grade, ['W', 'I', 'AU', 'IP'])) {
                    $termData['term_credits_attempted'] += $course->credits;
                    $termData['credits_attempted'] += $course->credits; // For view compatibility
                    
                    if ($this->isPassingGrade($grade->letter_grade)) {
                        $termData['term_credits_earned'] += $course->credits;
                        $totalCreditsEarned += $course->credits;
                    }
                    
                    $termData['term_quality_points'] += $qualityPoints;
                    $termData['quality_points'] += $qualityPoints; // For view compatibility
                    $totalCreditsAttempted += $course->credits;
                    $totalQualityPoints += $qualityPoints;
                }
            }

            // Calculate term GPA
            if ($termData['term_credits_attempted'] > 0) {
                $termData['term_gpa'] = round($termData['term_quality_points'] / $termData['term_credits_attempted'], 2);
            }

            // Calculate cumulative GPA up to this term
            $termData['cumulative_gpa'] = $totalCreditsAttempted > 0 
                ? round($totalQualityPoints / $totalCreditsAttempted, 2) 
                : 0;
            $termData['cumulative_credits_earned'] = $totalCreditsEarned;

            $academicRecord[] = $termData;
        }

        // Get honors and awards - handle if table doesn't exist
        $honors = [];
        try {
            $honors = StudentHonor::where('student_id', $student->id)
                ->orderBy('awarded_date', 'desc')
                ->get();
        } catch (\Exception $e) {
            // Table might not exist yet
            Log::info('StudentHonor table not found');
        }

        // Add transfer credits placeholder
        $transferCredits = [
            'total_credits' => 0,
            'institutions' => []
        ];

        // Get degree information if graduated
        $degreeInfo = null;
        if ($student->enrollment_status === 'graduated') {
            $degreeInfo = [
                'degree_type' => $student->program->degree_type ?? 'Bachelor of Science',
                'major' => $student->program->name ?? $student->major,
                'minor' => $student->minor,
                'graduation_date' => $student->graduation_date 
                    ? $student->graduation_date->format('F d, Y') 
                    : 'Date not specified',
                'honors' => $this->getGraduationHonors($totalQualityPoints / max($totalCreditsAttempted, 1)),
            ];
        }

        // Add total quality points for summary
        $totalQualityPointsForSummary = $totalQualityPoints;

        return [
            'type' => $type,
            'generated_at' => now(),
            'institution' => [
                'name' => config('app.institution_name', 'IntelliCampus University'),
                'address' => config('app.institution_address', '123 University Ave'),
                'city' => config('app.institution_city', 'City'),
                'state' => config('app.institution_state', 'State'),
                'zip' => config('app.institution_zip', '12345'),
                'phone' => config('app.institution_phone', '(555) 123-4567'),
                'registrar' => config('app.registrar_name', 'Jane Doe'),
                'registrar_title' => config('app.registrar_title', 'University Registrar'),
            ],
            'student' => [
                'name' => $student->user->name ?? 'Unknown',
                'student_id' => $student->student_id,
                'date_of_birth' => $student->date_of_birth 
                    ? $student->date_of_birth->format('F d, Y') 
                    : 'Not provided',
                'program' => $student->program->name ?? $student->major ?? 'Undeclared',
                'major' => $student->major ?? 'Undeclared',
                'minor' => $student->minor,
                'enrollment_date' => $student->admission_date 
                    ? $student->admission_date->format('F d, Y') 
                    : 'Not specified',
                'status' => ucfirst($student->enrollment_status),
            ],
            'academic_record' => $academicRecord,
            'summary' => [
                'total_credits_attempted' => $totalCreditsAttempted,
                'total_credits_earned' => $totalCreditsEarned,
                'total_quality_points' => $totalQualityPointsForSummary,
                'cumulative_gpa' => $totalCreditsAttempted > 0 
                    ? round($totalQualityPoints / $totalCreditsAttempted, 2) 
                    : 0,
                'academic_standing' => $this->getAcademicStanding($totalQualityPoints / max($totalCreditsAttempted, 1)),
            ],
            'honors' => $honors,
            'transfer_credits' => $transferCredits,
            'degree' => $degreeInfo,
            'verification_code' => null,
            'qr_code' => null,
        ];
    }

    /**
     * Create verification record
     */
    protected function createVerification(Student $student, $type)
    {
        $verificationCode = strtoupper(Str::random(4) . '-' . $student->id . '-' . Str::random(4));
        
        return TranscriptVerification::create([
            'student_id' => $student->id,
            'verification_code' => $verificationCode,
            'type' => $type,
            'generated_at' => now(),
            'expires_at' => now()->addDays(90),
            'generated_by' => Auth::id(),
        ]);
    }

    /**
     * Generate QR code for transcript verification
     */
    protected function generateQRCode($verificationCode)
    {
        $verificationUrl = route('transcripts.verify', ['code' => $verificationCode]);
        
        try {
            return base64_encode(
                QrCode::format('png')
                    ->size(150)
                    ->generate($verificationUrl)
            );
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log transcript action
     */
    protected function logTranscriptAction($student, $action, $type, $purpose = null, $requestId = null)
    {
        try {
            TranscriptLog::create([
                'student_id' => $student->id,
                'transcript_request_id' => $requestId,
                'action' => $action,
                'type' => $type,
                'purpose' => $purpose,
                'performed_by' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log transcript action: ' . $e->getMessage());
        }
    }

    /**
     * Check student holds
     */
    protected function checkStudentHolds(Student $student)
    {
        $holds = [];

        // Check financial hold - check if studentAccount relationship exists
        if ($student->studentAccount && $student->studentAccount->balance > 100) {
            $holds[] = 'Financial Hold';
        }

        // Check academic hold - check if field exists
        if (isset($student->has_academic_hold) && $student->has_academic_hold) {
            $holds[] = 'Academic Hold';
        }

        // Check registration hold - check if field exists
        if (isset($student->has_registration_hold) && $student->has_registration_hold) {
            $holds[] = 'Registration Hold';
        }

        return $holds;
    }

    /**
     * Calculate transcript fee
     */
    protected function calculateTranscriptFee($type, $copies, $deliveryMethod, $rushOrder)
    {
        $baseFee = $type === 'official' ? 10 : 0;
        $perCopyFee = $type === 'official' ? 5 : 0;
        $deliveryFee = [
            'electronic' => 0,
            'mail' => 10,
            'pickup' => 0,
        ][$deliveryMethod] ?? 0;
        $rushFee = $rushOrder ? 25 : 0;

        return $baseFee + ($perCopyFee * ($copies - 1)) + $deliveryFee + $rushFee;
    }

    /**
     * Generate unique request number
     */
    protected function generateRequestNumber()
    {
        $count = TranscriptRequest::whereYear('created_at', date('Y'))->count() + 1;
        return 'TR' . date('Y') . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get grade points for a letter grade
     */
    protected function getGradePoints($letterGrade)
    {
        $gradeScale = [
            'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0,
            'F' => 0.0,
            'P' => 0.0, 'NP' => 0.0,
            'W' => 0.0, 'I' => 0.0, 'AU' => 0.0, 'IP' => 0.0,
        ];

        return $gradeScale[$letterGrade] ?? 0.0;
    }

    /**
     * Check if grade is passing
     */
    protected function isPassingGrade($letterGrade)
    {
        return in_array($letterGrade, ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'P']);
    }

    /**
     * Get academic standing based on GPA
     */
    protected function getAcademicStanding($gpa)
    {
        if ($gpa >= 3.5) return 'Dean\'s List';
        if ($gpa >= 3.0) return 'Good Standing';
        if ($gpa >= 2.0) return 'Satisfactory';
        if ($gpa >= 1.5) return 'Academic Warning';
        return 'Academic Probation';
    }

    /**
     * Get graduation honors based on GPA
     */
    protected function getGraduationHonors($gpa)
    {
        if ($gpa >= 3.9) return 'Summa Cum Laude';
        if ($gpa >= 3.7) return 'Magna Cum Laude';
        if ($gpa >= 3.5) return 'Cum Laude';
        return null;
    }

    /**
     * Verify transcript (public method for QR code verification)
     */
    public function verify($code)
    {
        try {
            $verification = TranscriptVerification::where('verification_code', $code)
                ->where('expires_at', '>', now())
                ->first();

            if (!$verification) {
                return view('transcripts.verify-failed');
            }

            // Update verification count
            $verification->increment('verification_count');
            $verification->update(['last_verified_at' => now()]);

            // Log verification
            $this->logTranscriptAction(
                $verification->student, 
                'verified', 
                $verification->type
            );

            $student = $verification->student;

            return view('transcripts.verify-success', compact('student', 'verification'));
        } catch (\Exception $e) {
            Log::error('Verification failed: ' . $e->getMessage());
            return view('transcripts.verify-failed');
        }
    }
}