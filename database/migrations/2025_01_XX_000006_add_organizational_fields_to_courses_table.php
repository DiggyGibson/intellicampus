<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // Primary ownership
            $table->foreignId('department_id')->nullable()->after('department')
                  ->constrained('departments')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->after('department_id')
                  ->constrained('divisions')->nullOnDelete();
            
            // Course coordinator/owner
            $table->foreignId('coordinator_id')->nullable()->after('division_id')
                  ->constrained('users')->nullOnDelete();
            
            // Cross-listing support
            $table->json('cross_listed_departments')->nullable()->after('coordinator_id');
            
            // Approval tracking
            $table->foreignId('approved_by')->nullable()->after('cross_listed_departments')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Indexes
            $table->index('department_id');
            $table->index('division_id');
            $table->index('coordinator_id');
            $table->index(['department_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['division_id']);
            $table->dropForeign(['coordinator_id']);
            $table->dropForeign(['approved_by']);
            
            $table->dropColumn([
                'department_id',
                'division_id',
                'coordinator_id',
                'cross_listed_departments',
                'approved_by',
                'approved_at'
            ]);
        });
    }
};