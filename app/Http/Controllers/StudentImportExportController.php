<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Writer;

class StudentImportExportController extends Controller
{
    /**
     * Show import/export page
     */
    public function index()
    {
        $recentImports = DB::table('import_logs')
            ->where('type', 'students')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('students.import-export', compact('recentImports'));
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'first_name',
            'middle_name',
            'last_name',
            'preferred_name',
            'email',
            'secondary_email',
            'phone',
            'home_phone',
            'date_of_birth',
            'place_of_birth',
            'gender',
            'marital_status',
            'nationality',
            'national_id_number',
            'religion',
            'ethnicity',
            'address',
            'permanent_address',
            'program_name',
            'department',
            'major',
            'minor',
            'academic_level',
            'enrollment_status',
            'admission_date',
            'expected_graduation_year',
            'high_school_name',
            'high_school_graduation_year',
            'high_school_gpa',
            'guardian_name',
            'guardian_phone',
            'guardian_email',
            'emergency_contact_name',
            'emergency_contact_phone',
            'blood_group',
            'medical_conditions',
            'is_international',
            'passport_number',
            'visa_status',
            'visa_expiry'
        ];

        // Create sample data
        $sampleData = [
            [
                'John',
                'Michael',
                'Doe',
                'Johnny',
                'john.doe@example.com',
                'johndoe@gmail.com',
                '+1234567890',
                '+1234567891',
                '2000-01-15',
                'New York, USA',
                'male',
                'single',
                'American',
                '123456789',
                'Christianity',
                'Caucasian',
                '123 Main St, New York, NY 10001',
                '456 Home Ave, Boston, MA 02101',
                'Computer Science',
                'Computer Science',
                'Software Engineering',
                'Mathematics',
                'junior',
                'active',
                '2021-09-01',
                '2025',
                'Lincoln High School',
                '2021',
                '3.85',
                'Michael Doe',
                '+1234567892',
                'michael.doe@email.com',
                'Jane Doe',
                '+1234567893',
                'O+',
                '',
                '0',
                '',
                '',
                ''
            ],
            [
                'Jane',
                'Elizabeth',
                'Smith',
                '',
                'jane.smith@example.com',
                '',
                '+1234567894',
                '',
                '1999-05-20',
                'Toronto, Canada',
                'female',
                'single',
                'Canadian',
                '987654321',
                '',
                '',
                '789 College Ave, New York, NY 10002',
                '321 Maple St, Toronto, ON M5V 3A8',
                'Business Administration',
                'Business',
                'Finance',
                'Economics',
                'senior',
                'active',
                '2020-09-01',
                '2024',
                'Toronto Central High',
                '2020',
                '3.95',
                'Robert Smith',
                '+1234567895',
                'robert.smith@email.com',
                'Robert Smith',
                '+1234567895',
                'A+',
                '',
                '1',
                'CA1234567',
                'F-1',
                '2025-08-31'
            ]
        ];

        // Create CSV
        $csv = Writer::createFromString('');
        $csv->insertOne($headers);
        $csv->insertAll($sampleData);

        return response($csv->getContent(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ]);
    }

    /**
     * Import students from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            'update_existing' => 'nullable|boolean'
        ]);

        try {
            $file = $request->file('file');
            $updateExisting = $request->boolean('update_existing', false);
            
            // Read CSV
            $csv = Reader::createFromPath($file->getPathname(), 'r');
            $csv->setHeaderOffset(0);
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $rowNumber = 1;

            DB::beginTransaction();

            foreach ($csv as $row) {
                $rowNumber++;
                
                // Clean and validate data
                $validationResult = $this->validateRow($row, $rowNumber);
                
                if (!$validationResult['valid']) {
                    $errorCount++;
                    $errors[] = $validationResult['message'];
                    continue;
                }

                $data = $validationResult['data'];

                // Check if student exists
                $existingStudent = Student::where('email', $data['email'])->first();

                if ($existingStudent && !$updateExisting) {
                    $errorCount++;
                    $errors[] = "Row {$rowNumber}: Student with email {$data['email']} already exists";
                    continue;
                }

                try {
                    if ($existingStudent && $updateExisting) {
                        // Update existing student
                        $existingStudent->update($data);
                        $successCount++;
                    } else {
                        // Create new student with auto-generated ID
                        $year = date('y');
                        $lastStudent = Student::whereYear('created_at', date('Y'))
                            ->orderBy('id', 'desc')
                            ->lockForUpdate()
                            ->first();
                        
                        if ($lastStudent && substr($lastStudent->student_id, 0, 2) == $year) {
                            $sequence = intval(substr($lastStudent->student_id, 2)) + 1;
                        } else {
                            $sequence = 1;
                        }
                        
                        $data['student_id'] = $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);
                        
                        // Set defaults
                        $data['academic_standing'] = $data['academic_standing'] ?? 'good';
                        $data['admission_status'] = 'enrolled';
                        $data['current_gpa'] = $data['current_gpa'] ?? 0.00;
                        $data['cumulative_gpa'] = $data['cumulative_gpa'] ?? 0.00;
                        $data['credits_earned'] = $data['credits_earned'] ?? 0;
                        $data['credits_completed'] = $data['credits_completed'] ?? 0;
                        $data['credits_required'] = $data['credits_required'] ?? 120;
                        
                        Student::create($data);
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            // Log the import
            DB::table('import_logs')->insert([
                'type' => 'students',
                'filename' => $file->getClientOriginalName(),
                'total_rows' => $rowNumber - 1,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => json_encode($errors),
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            if ($errorCount > 0) {
                return back()->with('warning', "Import completed with issues: {$successCount} students imported successfully, {$errorCount} errors occurred.")
                    ->with('import_errors', $errors);
            }

            return back()->with('success', "Import successful! {$successCount} students imported.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export students to CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array'
        ]);

        $query = Student::query();

        // Apply filters
        if ($request->has('filters')) {
            if (!empty($request->filters['enrollment_status'])) {
                $query->where('enrollment_status', $request->filters['enrollment_status']);
            }
            if (!empty($request->filters['academic_level'])) {
                $query->where('academic_level', $request->filters['academic_level']);
            }
            if (!empty($request->filters['department'])) {
                $query->where('department', $request->filters['department']);
            }
            if (!empty($request->filters['date_from'])) {
                $query->whereDate('created_at', '>=', $request->filters['date_from']);
            }
            if (!empty($request->filters['date_to'])) {
                $query->whereDate('created_at', '<=', $request->filters['date_to']);
            }
        }

        $students = $query->get();

        // Default columns if none specified
        $columns = $request->columns ?? [
            'student_id',
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'phone',
            'date_of_birth',
            'gender',
            'nationality',
            'program_name',
            'department',
            'major',
            'academic_level',
            'enrollment_status',
            'academic_standing',
            'current_gpa',
            'cumulative_gpa',
            'credits_earned',
            'credits_required',
            'admission_date',
            'expected_graduation_year'
        ];

        // Create CSV
        $csv = Writer::createFromString('');
        
        // Add headers
        $csv->insertOne($columns);

        // Add data
        foreach ($students as $student) {
            $row = [];
            foreach ($columns as $column) {
                $value = $student->$column;
                
                // Format dates
                if (in_array($column, ['date_of_birth', 'admission_date', 'visa_expiry', 'insurance_expiry'])) {
                    $value = $value ? $value->format('Y-m-d') : '';
                }
                
                // Format booleans
                if (in_array($column, ['is_international', 'is_alumni'])) {
                    $value = $value ? '1' : '0';
                }
                
                $row[] = $value ?? '';
            }
            $csv->insertOne($row);
        }

        $filename = 'students_export_' . date('Y-m-d_His') . '.csv';

        return response($csv->getContent(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Bulk update students
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'action' => 'required|in:update_status,update_level,update_standing,delete',
            'value' => 'required_unless:action,delete'
        ]);

        try {
            DB::beginTransaction();

            $studentIds = $request->student_ids;
            $action = $request->action;
            $value = $request->value;
            $count = count($studentIds);

            switch ($action) {
                case 'update_status':
                    Student::whereIn('id', $studentIds)->update(['enrollment_status' => $value]);
                    $message = "{$count} students' enrollment status updated to {$value}";
                    break;

                case 'update_level':
                    Student::whereIn('id', $studentIds)->update(['academic_level' => $value]);
                    $message = "{$count} students' academic level updated to {$value}";
                    break;

                case 'update_standing':
                    Student::whereIn('id', $studentIds)->update(['academic_standing' => $value]);
                    $message = "{$count} students' academic standing updated to {$value}";
                    break;

                case 'delete':
                    Student::whereIn('id', $studentIds)->delete();
                    $message = "{$count} students deleted successfully";
                    break;

                default:
                    throw new \Exception('Invalid action');
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Bulk update failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate a CSV row
     */
    private function validateRow($row, $rowNumber)
    {
        $data = [
            'first_name' => $row['first_name'] ?? null,
            'middle_name' => $row['middle_name'] ?? null,
            'last_name' => $row['last_name'] ?? null,
            'preferred_name' => $row['preferred_name'] ?? null,
            'email' => $row['email'] ?? null,
            'secondary_email' => $row['secondary_email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'home_phone' => $row['home_phone'] ?? null,
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'place_of_birth' => $row['place_of_birth'] ?? null,
            'gender' => $row['gender'] ?? null,
            'marital_status' => $row['marital_status'] ?? null,
            'nationality' => $row['nationality'] ?? null,
            'national_id_number' => $row['national_id_number'] ?? null,
            'religion' => $row['religion'] ?? null,
            'ethnicity' => $row['ethnicity'] ?? null,
            'address' => $row['address'] ?? null,
            'permanent_address' => $row['permanent_address'] ?? null,
            'program_name' => $row['program_name'] ?? null,
            'department' => $row['department'] ?? null,
            'major' => $row['major'] ?? null,
            'minor' => $row['minor'] ?? null,
            'academic_level' => $row['academic_level'] ?? null,
            'enrollment_status' => $row['enrollment_status'] ?? null,
            'admission_date' => $row['admission_date'] ?? null,
            'expected_graduation_year' => $row['expected_graduation_year'] ?? null,
            'high_school_name' => $row['high_school_name'] ?? null,
            'high_school_graduation_year' => $row['high_school_graduation_year'] ?? null,
            'high_school_gpa' => $row['high_school_gpa'] ?? null,
            'guardian_name' => $row['guardian_name'] ?? null,
            'guardian_phone' => $row['guardian_phone'] ?? null,
            'guardian_email' => $row['guardian_email'] ?? null,
            'emergency_contact_name' => $row['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $row['emergency_contact_phone'] ?? null,
            'blood_group' => $row['blood_group'] ?? null,
            'medical_conditions' => $row['medical_conditions'] ?? null,
            'is_international' => isset($row['is_international']) ? ($row['is_international'] == '1' || strtolower($row['is_international']) == 'true') : false,
            'passport_number' => $row['passport_number'] ?? null,
            'visa_status' => $row['visa_status'] ?? null,
            'visa_expiry' => $row['visa_expiry'] ?? null,
        ];

        // Remove empty strings
        $data = array_map(function($value) {
            return $value === '' ? null : $value;
        }, $data);

        $validator = Validator::make($data, [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'program_name' => 'required|string|max:200',
            'department' => 'required|string|max:100',
            'academic_level' => 'required|in:freshman,sophomore,junior,senior,graduate',
            'enrollment_status' => 'required|in:active,inactive,suspended,graduated,withdrawn',
            'admission_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => "Row {$rowNumber}: " . implode(', ', $validator->errors()->all())
            ];
        }

        return [
            'valid' => true,
            'data' => $data
        ];
    }
}