<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganizationalFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Primary organizational affiliation
            $table->foreignId('college_id')->nullable()->after('department')
                  ->constrained('colleges')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->after('college_id')
                  ->constrained('schools')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('school_id')
                  ->constrained('departments')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->after('department_id')
                  ->constrained('divisions')->nullOnDelete();
            
            // Additional fields for scope management
            $table->string('organizational_role')->nullable()->after('division_id');
            // Values: dean, associate_dean, director, department_head, division_coordinator, faculty, staff
            
            $table->json('secondary_departments')->nullable()->after('organizational_role');
            // For faculty teaching across departments
            
            $table->boolean('has_administrative_role')->default(false)->after('secondary_departments');
            
            // Indexes for performance
            $table->index('college_id');
            $table->index('school_id');
            $table->index('department_id');
            $table->index('division_id');
            $table->index('organizational_role');
            $table->index(['department_id', 'user_type']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropForeign(['school_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['division_id']);
            
            $table->dropColumn([
                'college_id',
                'school_id',
                'department_id',
                'division_id',
                'organizational_role',
                'secondary_departments',
                'has_administrative_role'
            ]);
        });
    }
}
