<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Add missing columns to programs table
        Schema::table('programs', function (Blueprint $table) {
            if (!Schema::hasColumn('programs', 'delivery_mode')) {
                $table->string('delivery_mode')->default('on-campus')->after('career_prospects');
            }
            if (!Schema::hasColumn('programs', 'application_fee')) {
                $table->decimal('application_fee', 8, 2)->default(50.00)->after('current_enrollment');
            }
            if (!Schema::hasColumn('programs', 'accreditation_status')) {
                $table->string('accreditation_status')->nullable()->after('application_fee');
            }
            if (!Schema::hasColumn('programs', 'accreditation_date')) {
                $table->date('accreditation_date')->nullable()->after('accreditation_status');
            }
            if (!Schema::hasColumn('programs', 'next_review_date')) {
                $table->date('next_review_date')->nullable()->after('accreditation_date');
            }
        });

        // Add missing email column to applicants table (not really needed, but for the seeder)
        Schema::table('applicants', function (Blueprint $table) {
            if (!Schema::hasColumn('applicants', 'email')) {
                $table->string('email')->nullable()->after('last_name');
            }
        });
        
        // Add delivery_mode check constraint to programs
        DB::statement("ALTER TABLE programs DROP CONSTRAINT IF EXISTS programs_delivery_mode_check");
        DB::statement("ALTER TABLE programs ADD CONSTRAINT programs_delivery_mode_check 
                      CHECK (delivery_mode IN ('on-campus', 'online', 'hybrid', 'flexible'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['delivery_mode', 'application_fee', 'accreditation_status', 'accreditation_date', 'next_review_date']);
        });
        
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};