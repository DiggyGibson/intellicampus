<?php

namespace App\ImportsExports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class GradeTemplateExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize, WithEvents
{
    protected $enrollments;
    protected $section;
    protected $component;

    public function __construct($enrollments, $section, $component)
    {
        $this->enrollments = $enrollments;
        $this->section = $section;
        $this->component = $component;
    }

    /**
     * Return collection of enrollments
     */
    public function collection()
    {
        return $this->enrollments;
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            'Enrollment ID',
            'Student ID',
            'Last Name',
            'First Name',
            'Middle Name',
            'Email',
            'Points Earned',
            'Max Points',
            'Percentage',
            'Letter Grade',
            'Comments'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($enrollment): array
    {
        // Check if grade already exists for this component
        $existingGrade = $enrollment->grades
            ->where('component_id', $this->component->id)
            ->first();

        return [
            $enrollment->id,
            $enrollment->student->student_id,
            $enrollment->student->last_name,
            $enrollment->student->first_name,
            $enrollment->student->middle_name,
            $enrollment->student->email,
            $existingGrade ? $existingGrade->points_earned : '',  // Empty for new grades
            $this->component->max_points,
            $existingGrade ? $existingGrade->percentage : '',
            $existingGrade ? $existingGrade->letter_grade : '',
            $existingGrade ? $existingGrade->comments : ''
        ];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Enrollment ID
            'B' => 12,  // Student ID
            'C' => 15,  // Last Name
            'D' => 15,  // First Name
            'E' => 15,  // Middle Name
            'F' => 25,  // Email
            'G' => 12,  // Points Earned
            'H' => 12,  // Max Points
            'I' => 12,  // Percentage
            'J' => 12,  // Letter Grade
            'K' => 30   // Comments
        ];
    }

    /**
     * Return the title for the sheet
     */
    public function title(): string
    {
        return substr($this->component->name, 0, 31); // Excel sheet names max 31 chars
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->enrollments->count() + 1;
        
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF005A8B']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            
            // All data cells
            "A2:K{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD3D3D3']
                    ]
                ]
            ],
            
            // Points columns alignment
            "G2:J{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ]
        ];
    }

    /**
     * Register events for additional worksheet configuration
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->enrollments->count() + 1;
                
                // Freeze the header row
                $sheet->freezePane('A2');
                
                // Protect certain columns (make them read-only)
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setPassword('gradesheet');
                $sheet->getProtection()->setSort(true);
                $sheet->getProtection()->setInsertRows(false);
                $sheet->getProtection()->setDeleteRows(false);
                
                // Unlock only the Points Earned and Comments columns for editing
                for ($row = 2; $row <= $lastRow; $row++) {
                    $sheet->getStyle("G{$row}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                    $sheet->getStyle("K{$row}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                }
                
                // Add data validation for Points Earned column
                for ($row = 2; $row <= $lastRow; $row++) {
                    $validation = $sheet->getCell("G{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_DECIMAL);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Invalid Entry');
                    $validation->setError('Points must be between 0 and ' . $this->component->max_points);
                    $validation->setPromptTitle('Enter Points');
                    $validation->setPrompt('Enter points earned (0-' . $this->component->max_points . ')');
                    $validation->setFormula1(0);
                    $validation->setFormula2($this->component->max_points);
                }
                
                // Add formulas for Percentage and Letter Grade columns
                for ($row = 2; $row <= $lastRow; $row++) {
                    // Percentage formula
                    $sheet->setCellValue("I{$row}", "=IF(G{$row}=\"\",\"\",ROUND((G{$row}/H{$row})*100,2))");
                    
                    // Letter grade formula
                    $formula = "=IF(I{$row}=\"\",\"\",IF(I{$row}>=93,\"A\",IF(I{$row}>=90,\"A-\",IF(I{$row}>=87,\"B+\",IF(I{$row}>=83,\"B\",IF(I{$row}>=80,\"B-\",IF(I{$row}>=77,\"C+\",IF(I{$row}>=73,\"C\",IF(I{$row}>=70,\"C-\",IF(I{$row}>=67,\"D+\",IF(I{$row}>=63,\"D\",\"F\"))))))))))";
                    $sheet->setCellValue("J{$row}", $formula);
                }
                
                // Add header information
                $sheet->insertNewRowBefore(1, 4);
                $sheet->mergeCells('A1:K1');
                $sheet->mergeCells('A2:K2');
                $sheet->mergeCells('A3:K3');
                
                $sheet->setCellValue('A1', $this->section->course->code . ' - ' . $this->section->course->title);
                $sheet->setCellValue('A2', 'Section: ' . $this->section->section_code . ' | Term: ' . $this->section->term->name);
                $sheet->setCellValue('A3', 'Component: ' . $this->component->name . ' (' . $this->component->weight . '% of total grade)');
                
                // Style the header information
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF0F8FF']
                    ]
                ]);
                
                // Add instructions
                $instructionRow = $lastRow + 6;
                $sheet->mergeCells("A{$instructionRow}:K" . ($instructionRow + 4));
                $instructions = "INSTRUCTIONS:\n";
                $instructions .= "1. Enter points earned in the 'Points Earned' column (Column G)\n";
                $instructions .= "2. Percentage and Letter Grade will be calculated automatically\n";
                $instructions .= "3. Add optional comments in the 'Comments' column (Column K)\n";
                $instructions .= "4. Save the file and upload it back to the system\n";
                $instructions .= "5. DO NOT modify Student ID or Enrollment ID columns";
                
                $sheet->setCellValue("A{$instructionRow}", $instructions);
                $sheet->getStyle("A{$instructionRow}")->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFFFFCC']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN
                        ]
                    ]
                ]);
                
                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension($instructionRow)->setRowHeight(80);
            }
        ];
    }
}