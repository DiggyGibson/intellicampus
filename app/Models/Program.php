<?php

namespace App\Models;

/**
 * Program Model - Alias to AcademicProgram
 * 
 * This model serves as an alias to AcademicProgram for backward compatibility.
 * All functionality is inherited from AcademicProgram.
 * 
 * Use this when controllers or other code references Program::class
 * but the actual table is academic_programs.
 */
class Program extends AcademicProgram
{
    // Everything is inherited from AcademicProgram
    // No additional code needed
}