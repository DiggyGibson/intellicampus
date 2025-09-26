<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    /**
     * Show enrollment history for a student
     */
    public function history(Student $student)
    {
        $enrollments = DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->join('academic_terms as at', 'e.term_id', '=', 'at.id')
            ->where('e.student_id', $student->id)
            ->select(
                'e.*',
                'cs.section_number',
                'cs.crn',
                'c.code as course_code',
                'c.title as course_title',
                'c.credits',
                'at.name as term_name',
                'at.academic_year'
            )
            ->orderBy('at.start_date', 'desc')
            ->orderBy('c.code')
            ->get();
        
        return view('students.enrollment.history', compact('student', 'enrollments'));
    }
    
    /**
     * Manage student enrollment status (based on your actual view)
     */
    public function manage(Student $student)
    {
        // Get enrollment history
        $enrollmentHistory = DB::table('student_status_changes')
            ->where('student_id', $student->id)
            ->orderBy('changed_at', 'desc')
            ->limit(10)
            ->get();
        
        // Add user names for who made the changes
        foreach ($enrollmentHistory as $history) {
            if ($history->changed_by) {
                $user = DB::table('users')->find($history->changed_by);
                $history->changed_by_name = $user ? $user->name : 'Unknown';
            }
        }
        
        return view('students.enrollment.manage', compact('student', 'enrollmentHistory'));
    }
    
    /**
     * Process leave of absence
     */
    public function leave(Request $request, Student $student)
    {
        $request->validate([
            'reason' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        try {
            // Record status change
            DB::table('student_status_changes')->insert([
                'student_id' => $student->id,
                'previous_status' => $student->enrollment_status,
                'new_status' => 'inactive',
                'reason' => $request->reason,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update student status
            $student->update([
                'enrollment_status' => 'inactive',
                'leave_start_date' => $request->start_date,
                'leave_end_date' => $request->end_date,
                'leave_reason' => $request->reason
            ]);
            
            DB::commit();
            return redirect()->route('students.enrollment.manage', $student)
                ->with('success', 'Leave of absence processed successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process leave of absence.');
        }
    }
    
    /**
     * Process withdrawal
     */
    public function withdraw(Request $request, Student $student)
    {
        $request->validate([
            'reason' => 'required|string',
            'effective_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        try {
            // Record status change
            DB::table('student_status_changes')->insert([
                'student_id' => $student->id,
                'previous_status' => $student->enrollment_status,
                'new_status' => 'withdrawn',
                'reason' => $request->reason,
                'changed_by' => auth()->id(),
                'changed_at' => $request->effective_date ?? now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update student status
            $student->update([
                'enrollment_status' => 'withdrawn',
                'withdrawal_date' => $request->effective_date ?? now(),
                'withdrawal_reason' => $request->reason
            ]);
            
            DB::commit();
            return redirect()->route('students.enrollment.manage', $student)
                ->with('success', 'Student withdrawal processed successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process withdrawal.');
        }
    }
    
    /**
     * Return from leave
     */
    public function return(Request $request, Student $student)
    {
        $request->validate([
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        try {
            // Record status change
            DB::table('student_status_changes')->insert([
                'student_id' => $student->id,
                'previous_status' => $student->enrollment_status,
                'new_status' => 'active',
                'reason' => 'Return from leave',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update student status
            $student->update([
                'enrollment_status' => 'active',
                'leave_start_date' => null,
                'leave_end_date' => null,
                'leave_reason' => null
            ]);
            
            DB::commit();
            return redirect()->route('students.enrollment.manage', $student)
                ->with('success', 'Student returned to active status.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process return from leave.');
        }
    }
    
    /**
     * Process readmission
     */
    public function readmit(Request $request, Student $student)
    {
        $request->validate([
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        try {
            // Record status change
            DB::table('student_status_changes')->insert([
                'student_id' => $student->id,
                'previous_status' => $student->enrollment_status,
                'new_status' => 'active',
                'reason' => 'Readmission',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update student status
            $student->update([
                'enrollment_status' => 'active',
                'readmission_date' => now()
            ]);
            
            DB::commit();
            return redirect()->route('students.enrollment.manage', $student)
                ->with('success', 'Student readmitted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process readmission.');
        }
    }
    
    /**
     * Process graduation
     */
    public function graduate(Request $request, Student $student)
    {
        $request->validate([
            'degree_awarded' => 'required|string',
            'graduation_date' => 'nullable|date'
        ]);
        
        DB::beginTransaction();
        try {
            // Record status change
            DB::table('student_status_changes')->insert([
                'student_id' => $student->id,
                'previous_status' => $student->enrollment_status,
                'new_status' => 'graduated',
                'reason' => 'Graduation - ' . $request->degree_awarded,
                'changed_by' => auth()->id(),
                'changed_at' => $request->graduation_date ?? now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update student status
            $student->update([
                'enrollment_status' => 'graduated',
                'graduation_date' => $request->graduation_date ?? now(),
                'degree_awarded' => $request->degree_awarded
            ]);
            
            DB::commit();
            return redirect()->route('students.enrollment.manage', $student)
                ->with('success', 'Student graduation processed successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process graduation.');
        }
    }
    
    /**
     * Process suspension
     */
    public function suspend(Request $request, Student $student)
    {
        $request->validate([
            'reason' => 'required|string',
            'effective_date' => 'nullable|date'
        ]);
        
        DB::beginTransaction();
        try {
            // Record status change
            DB::table('student_status_changes')->insert([
                'student_id' => $student->id,
                'previous_status' => $student->enrollment_status,
                'new_status' => 'suspended',
                'reason' => $request->reason,
                'changed_by' => auth()->id(),
                'changed_at' => $request->effective_date ?? now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update student status
            $student->update([
                'enrollment_status' => 'suspended',
                'suspension_date' => $request->effective_date ?? now(),
                'suspension_reason' => $request->reason
            ]);
            
            DB::commit();
            return redirect()->route('students.enrollment.manage', $student)
                ->with('success', 'Student suspended successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process suspension.');
        }
    }
    
    /**
     * Enroll student in a course section
     */
    public function enroll(Request $request, Student $student)
    {
        $request->validate([
            'section_id' => 'required|exists:course_sections,id'
        ]);
        
        // For now, redirect to registration system
        return redirect()
            ->route('registration.catalog')
            ->with('info', 'Please use the Registration system to enroll in courses.');
    }
    
    /**
     * Drop a course for the student
     */
    public function drop(Request $request, Student $student)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id'
        ]);
        
        // For now, redirect to registration system
        return redirect()
            ->route('registration.schedule')
            ->with('info', 'Please use the Registration system to drop courses.');
    }
}