// The new migration file will be created with a timestamp prefix
// Edit it to contain:

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->decimal('per_credit_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('flat_amount', 10, 2)->nullable()->after('per_credit_amount');
            $table->string('student_type')->nullable()->after('academic_level');
            $table->string('category')->nullable()->after('type');
            $table->string('applies_to')->nullable()->after('category');
            $table->date('effective_date')->nullable()->after('effective_from');
        });
    }

    public function down()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn([
                'per_credit_amount',
                'flat_amount', 
                'student_type',
                'category',
                'applies_to',
                'effective_date'
            ]);
        });
    }
};