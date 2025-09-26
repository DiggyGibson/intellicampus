<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\AcademicTerm;
use App\Models\Course;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class TranscriptService
{
    protected $gradeCalculationService;

    public function __construct(GradeCalculationService $gradeCalculationService)
    {
        $this->gradeCalculationService = $gradeCalculationService;
    }

    /**
     * Generate transcript data for a student
     *
     * @param int $studentId
     * @param string $type ('official' or 'unofficial')
     * @return array
     */
    public function generateTranscriptData($studentId, $type = 'unofficial')
    {
        $student = Student::with(['program', 'user'])->findOrFail($studentId);
        
        // Get all enrollments grouped by term
        $enrollments = Enrollment::with(['section.course', 'term'])
            ->where('student_id', $studentId)
            ->whereIn('enrollment_status', ['completed', 'withdrawn'])
            ->orderBy('term_id')
            ->get()
            ->groupBy('term_id');
        
        $transcriptData = [
            'type' => $type,
            'student' => $this->getStudentInfo($student),
            'institution' => $this->getInstitutionInfo(),
            'academic_record' => [],
            'summary' => [
                'total_credits_attempted' => 0,
                'total_credits_earned' => 0,
                'total_quality_points' => 0,
                'cumulative_gpa' => 0
            ],
            'generated_at' => now(),
            'verification_code' => $this->generateVerificationCode($studentId, $type)
        ];
        
        // Process each term
        foreach ($enrollments as $termId => $termEnrollments) {
            $term = AcademicTerm::find($termId);
            $termData = $this->processTermData($term, $termEnrollments);
            
            $transcriptData['academic_record'][] = $termData;
            
            // Update summary
            $transcriptData['summary']['total_credits_attempted'] += $termData['credits_attempted'];
            $transcriptData['summary']['total_credits_earned'] += $termData['credits_earned'];
            $transcriptData['summary']['total_quality_points'] += $termData['quality_points'];
        }
        
        // Calculate final cumulative GPA
        if ($transcriptData['summary']['total_credits_attempted'] > 0) {
            $transcriptData['summary']['cumulative_gpa'] = round(
                $transcriptData['summary']['total_quality_points'] / 
                $transcriptData['summary']['total_credits_attempted'], 
                2
            );
        }
        
        // Add transfer credits if any
        $transcriptData['transfer_credits'] = $this->getTransferCredits($studentId);
        
        // Add academic honors
        $transcriptData['honors'] = $this->getAcademicHonors($student);
        
        // Add degree information if graduated
        $transcriptData['degree'] = $this->getDegreeInfo($student);
        
        return $transcriptData;
    }

    /**
     * Generate PDF transcript
     *
     * @param int $studentId
     * @param string $type
     * @return \Illuminate\Http\Response
     */
    public function generatePDF($studentId, $type = 'unofficial')
    {
        $data = $this->generateTranscriptData($studentId, $type);
        
        // Add additional formatting for PDF
        $data['is_pdf'] = true;
        $data['watermark'] = $type === 'unofficial' ? 'UNOFFICIAL' : null;
        
        // Generate QR code for verification
        if ($type === 'official') {
            $data['qr_code'] = $this->generateQRCode($data['verification_code']);
        }
        
        // Load appropriate template
        $template = $type === 'official' ? 'transcripts.official' : 'transcripts.unofficial';
        
        $pdf = PDF::loadView($template, $data);
        
        // Configure PDF settings
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        
        // Add digital signature for official transcripts
        if ($type === 'official') {
            $this->addDigitalSignature($pdf, $data);
        }
        
        return $pdf;
    }

    /**
     * Get student information for transcript
     */
    protected function getStudentInfo($student)
    {
        return [
            'name' => $student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name,
            'student_id' => $student->student_id,
            'date_of_birth' => $student->date_of_birth ? Carbon::parse($student->date_of_birth)->format('F d, Y') : null,
            'program' => $student->program->name ?? 'Undeclared',
            'major' => $student->major->name ?? null,
            'minor' => $student->minor->name ?? null,
            'enrollment_date' => Carbon::parse($student->enrollment_date)->format('F Y'),
            'expected_graduation' => $student->expected_graduation_date ? 
                Carbon::parse($student->expected_graduation_date)->format('F Y') : null,
            'email' => $student->user->email,
            'status' => $student->enrollment_status
        ];
    }

    /**
     * Get institution information
     */
    protected function getInstitutionInfo()
    {
        return [
            'name' => config('app.institution_name', 'IntelliCampus University'),
            'address' => config('app.institution_address', '123 University Ave'),
            'city' => config('app.institution_city', 'City'),
            'state' => config('app.institution_state', 'State'),
            'zip' => config('app.institution_zip', '12345'),
            'phone' => config('app.institution_phone', '(555) 123-4567'),
            'registrar' => config('app.registrar_name', 'John Doe'),
            'registrar_title' => config('app.registrar_title', 'University Registrar'),
            'accreditation' => config('app.institution_accreditation', 'Regionally Accredited')
        ];
    }

    /**
     * Process term data for transcript
     */
    protected function processTermData($term, $enrollments)
    {
        $termData = [
            'term_name' => $term->name,
            'term_code' => $term->code,
            'academic_year' => $term->academic_year,
            'start_date' => Carbon::parse($term->start_date)->format('M Y'),
            'end_date' => Carbon::parse($term->end_date)->format('M Y'),
            'courses' => [],
            'credits_attempted' => 0,
            'credits_earned' => 0,
            'quality_points' => 0,
            'term_gpa' => 0
        ];
        
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->section->course;
            $gradePoints = $this->gradeCalculationService->getGradePoints($enrollment->grade);
            $qualityPoints = $gradePoints * $course->credits;
            
            $courseData = [
                'code' => $course->code,
                'title' => $course->title,
                'credits' => $course->credits,
                'grade' => $enrollment->grade ?? 'IP', // IP = In Progress
                'grade_points' => $gradePoints,
                'quality_points' => $qualityPoints,
                'status' => $enrollment->enrollment_status
            ];
            
            // Add repeat indicator if applicable
            if ($this->isRepeatCourse($enrollment->student_id, $course->id, $enrollment->id)) {
                $courseData['repeat'] = true;
            }
            
            $termData['courses'][] = $courseData;
            
            // Update term totals
            if (!in_array($enrollment->grade, ['W', 'I', 'IP', null])) {
                $termData['credits_attempted'] += $course->credits;
                
                if ($enrollment->grade !== 'F') {
                    $termData['credits_earned'] += $course->credits;
                }
                
                $termData['quality_points'] += $qualityPoints;
            }
        }
        
        // Calculate term GPA
        if ($termData['credits_attempted'] > 0) {
            $termData['term_gpa'] = round($termData['quality_points'] / $termData['credits_attempted'], 2);
        }
        
        // Add Dean's List notation if applicable
        if ($termData['term_gpa'] >= 3.5 && $termData['credits_attempted'] >= 12) {
            $termData['honors'] = "Dean's List";
        }
        
        return $termData;
    }

    /**
     * Get transfer credits
     */
    protected function getTransferCredits($studentId)
    {
        $transferCredits = DB::table('transfer_credits')
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->get();
        
        $totalTransferCredits = 0;
        $transferInstitutions = [];
        
        foreach ($transferCredits as $credit) {
            $totalTransferCredits += $credit->credits;
            
            if (!in_array($credit->institution, $transferInstitutions)) {
                $transferInstitutions[] = $credit->institution;
            }
        }
        
        return [
            'total_credits' => $totalTransferCredits,
            'institutions' => $transferInstitutions,
            'details' => $transferCredits
        ];
    }

    /**
     * Get academic honors
     */
    protected function getAcademicHonors($student)
    {
        $honors = [];
        
        // Dean's List
        $deansListTerms = DB::table('deans_list')
            ->join('academic_terms', 'deans_list.term_id', '=', 'academic_terms.id')
            ->where('student_id', $student->id)
            ->select('academic_terms.name', 'deans_list.gpa')
            ->get();
        
        foreach ($deansListTerms as $term) {
            $honors[] = [
                'type' => "Dean's List",
                'term' => $term->name,
                'details' => 'GPA: ' . $term->gpa
            ];
        }
        
        // Latin Honors (for graduates)
        if ($student->graduation_date) {
            $cumulativeGPA = $student->cumulative_gpa;
            
            if ($cumulativeGPA >= 3.9) {
                $honors[] = ['type' => 'Summa Cum Laude', 'details' => 'Highest Distinction'];
            } elseif ($cumulativeGPA >= 3.7) {
                $honors[] = ['type' => 'Magna Cum Laude', 'details' => 'High Distinction'];
            } elseif ($cumulativeGPA >= 3.5) {
                $honors[] = ['type' => 'Cum Laude', 'details' => 'Distinction'];
            }
        }
        
        // Other honors (scholarships, awards, etc.)
        $otherHonors = DB::table('student_honors')
            ->where('student_id', $student->id)
            ->get();
        
        foreach ($otherHonors as $honor) {
            $honors[] = [
                'type' => $honor->honor_type,
                'details' => $honor->description,
                'date' => Carbon::parse($honor->awarded_date)->format('F Y')
            ];
        }
        
        return $honors;
    }

    /**
     * Get degree information for graduated students
     */
    protected function getDegreeInfo($student)
    {
        if (!$student->graduation_date) {
            return null;
        }
        
        return [
            'degree_type' => $student->degree_type ?? 'Bachelor of Science',
            'major' => $student->major->name ?? 'General Studies',
            'minor' => $student->minor->name ?? null,
            'graduation_date' => Carbon::parse($student->graduation_date)->format('F d, Y'),
            'honors' => $this->getGraduationHonors($student->cumulative_gpa)
        ];
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
     * Check if course is a repeat
     */
    protected function isRepeatCourse($studentId, $courseId, $currentEnrollmentId)
    {
        return Enrollment::where('student_id', $studentId)
            ->whereHas('section', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('id', '<', $currentEnrollmentId)
            ->exists();
    }

    /**
     * Generate verification code
     */
    protected function generateVerificationCode($studentId, $type)
    {
        $timestamp = now()->timestamp;
        $data = $studentId . '-' . $type . '-' . $timestamp;
        return strtoupper(substr(md5($data), 0, 10));
    }

    /**
     * Generate QR code for verification
     */
    protected function generateQRCode($verificationCode)
    {
        $url = config('app.url') . '/transcript/verify/' . $verificationCode;
        return base64_encode(QrCode::format('png')->size(150)->generate($url));
    }

    /**
     * Add digital signature to PDF
     */
    protected function addDigitalSignature($pdf, $data)
    {
        // This would implement actual digital signature
        // For now, we'll add metadata
        $pdf->setOptions([
            'info' => [
                'Title' => 'Official Transcript',
                'Author' => $data['institution']['registrar'],
                'Subject' => 'Academic Transcript for ' . $data['student']['name'],
                'Keywords' => 'transcript, official, academic',
                'Creator' => 'IntelliCampus',
                'Producer' => 'IntelliCampus Transcript System'
            ]
        ]);
    }

    /**
     * Verify transcript authenticity
     */
    public function verifyTranscript($verificationCode)
    {
        // Look up verification code in database
        $record = DB::table('transcript_verifications')
            ->where('code', $verificationCode)
            ->first();
        
        if (!$record) {
            return [
                'valid' => false,
                'message' => 'Invalid verification code'
            ];
        }
        
        // Check if code has expired (valid for 1 year)
        if (Carbon::parse($record->created_at)->addYear()->isPast()) {
            return [
                'valid' => false,
                'message' => 'Verification code has expired'
            ];
        }
        
        return [
            'valid' => true,
            'student_id' => $record->student_id,
            'generated_at' => $record->created_at,
            'type' => $record->type
        ];
    }

    /**
     * Log transcript generation
     */
    public function logTranscriptGeneration($studentId, $type, $requestedBy)
    {
        DB::table('transcript_logs')->insert([
            'student_id' => $studentId,
            'type' => $type,
            'requested_by' => $requestedBy,
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);
    }
}