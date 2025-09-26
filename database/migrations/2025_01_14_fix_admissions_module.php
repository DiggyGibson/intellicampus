<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to fix all missing columns and issues
     */
    public function up(): void
    {
        // ============================================
        // FIX 1: Add missing columns to academic_terms
        // ============================================
        Schema::table('academic_terms', function (Blueprint $table) {
            // Admission-related columns
            if (!Schema::hasColumn('academic_terms', 'is_admission_open')) {
                $table->boolean('is_admission_open')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('academic_terms', 'admission_deadline')) {
                $table->date('admission_deadline')->nullable()->after('is_admission_open');
            }
            if (!Schema::hasColumn('academic_terms', 'admission_start_date')) {
                $table->date('admission_start_date')->nullable()->after('admission_deadline');
            }
            if (!Schema::hasColumn('academic_terms', 'early_admission_deadline')) {
                $table->date('early_admission_deadline')->nullable()->after('admission_start_date');
            }
            if (!Schema::hasColumn('academic_terms', 'admission_notification_date')) {
                $table->date('admission_notification_date')->nullable()->after('early_admission_deadline');
            }
            if (!Schema::hasColumn('academic_terms', 'total_spots')) {
                $table->integer('total_spots')->nullable()->after('admission_notification_date');
            }
        });

        // ============================================
        // FIX 2: Add missing columns to academic_programs
        // ============================================
        Schema::table('academic_programs', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_programs', 'accepts_applications')) {
                $table->boolean('accepts_applications')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('academic_programs', 'application_fee')) {
                $table->decimal('application_fee', 8, 2)->default(50.00)->after('accepts_applications');
            }
            if (!Schema::hasColumn('academic_programs', 'degree_type')) {
                $table->string('degree_type', 50)->nullable()->after('level');
            }
            if (!Schema::hasColumn('academic_programs', 'program_type')) {
                $table->enum('program_type', ['undergraduate', 'graduate', 'certificate', 'diploma'])
                    ->default('undergraduate')->after('degree_type');
            }
            if (!Schema::hasColumn('academic_programs', 'delivery_mode')) {
                $table->enum('delivery_mode', ['on-campus', 'online', 'hybrid', 'flexible'])
                    ->default('on-campus')->after('program_type');
            }
            if (!Schema::hasColumn('academic_programs', 'enrollment_capacity')) {
                $table->integer('enrollment_capacity')->nullable()->after('delivery_mode');
            }
            if (!Schema::hasColumn('academic_programs', 'application_types')) {
                $table->json('application_types')->nullable()->after('enrollment_capacity')
                    ->comment('JSON array of supported application types');
            }
        });

        // ============================================
        // FIX 3: Create countries table if it doesn't exist
        // ============================================
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('code', 2)->unique();
                $table->string('code3', 3)->unique();
                $table->string('name', 100);
                $table->string('capital', 100)->nullable();
                $table->string('region', 100)->nullable();
                $table->string('subregion', 100)->nullable();
                $table->string('phone_code', 10)->nullable();
                $table->string('currency_code', 3)->nullable();
                $table->string('currency_name', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('name');
                $table->index('is_active');
            });
            
            // Insert some sample countries
            DB::table('countries')->insert([
                ['code' => 'US', 'code3' => 'USA', 'name' => 'United States', 'phone_code' => '+1', 'currency_code' => 'USD', 'currency_name' => 'US Dollar', 'is_active' => true],
                ['code' => 'GB', 'code3' => 'GBR', 'name' => 'United Kingdom', 'phone_code' => '+44', 'currency_code' => 'GBP', 'currency_name' => 'Pound Sterling', 'is_active' => true],
                ['code' => 'CA', 'code3' => 'CAN', 'name' => 'Canada', 'phone_code' => '+1', 'currency_code' => 'CAD', 'currency_name' => 'Canadian Dollar', 'is_active' => true],
                ['code' => 'AU', 'code3' => 'AUS', 'name' => 'Australia', 'phone_code' => '+61', 'currency_code' => 'AUD', 'currency_name' => 'Australian Dollar', 'is_active' => true],
                ['code' => 'IN', 'code3' => 'IND', 'name' => 'India', 'phone_code' => '+91', 'currency_code' => 'INR', 'currency_name' => 'Indian Rupee', 'is_active' => true],
                ['code' => 'CN', 'code3' => 'CHN', 'name' => 'China', 'phone_code' => '+86', 'currency_code' => 'CNY', 'currency_name' => 'Yuan', 'is_active' => true],
                ['code' => 'JP', 'code3' => 'JPN', 'name' => 'Japan', 'phone_code' => '+81', 'currency_code' => 'JPY', 'currency_name' => 'Yen', 'is_active' => true],
                ['code' => 'DE', 'code3' => 'DEU', 'name' => 'Germany', 'phone_code' => '+49', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'is_active' => true],
                ['code' => 'FR', 'code3' => 'FRA', 'name' => 'France', 'phone_code' => '+33', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'is_active' => true],
                ['code' => 'NG', 'code3' => 'NGA', 'name' => 'Nigeria', 'phone_code' => '+234', 'currency_code' => 'NGN', 'currency_name' => 'Naira', 'is_active' => true],
            ]);
        }

        // ============================================
        // FIX 4: Create states table if it doesn't exist
        // ============================================
        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained('countries');
                $table->string('code', 10)->nullable();
                $table->string('name', 100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['country_id', 'name']);
                $table->index('is_active');
            });
        }

        // ============================================
        // FIX 5: Create cities table if it doesn't exist
        // ============================================
        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->nullable()->constrained('states');
                $table->foreignId('country_id')->constrained('countries');
                $table->string('name', 100);
                $table->boolean('is_capital')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['country_id', 'state_id']);
                $table->index('name');
                $table->index('is_active');
            });
        }

        // ============================================
        // FIX 6: Create missing tables for additional models
        // ============================================
        
        // Application Status History
        if (!Schema::hasTable('application_status_histories')) {
            Schema::create('application_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50);
                $table->text('notes')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users');
                $table->timestamps();
                
                $table->index(['application_id', 'created_at']);
            });
        }

        // Application Notes
        if (!Schema::hasTable('application_notes')) {
            Schema::create('application_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users');
                $table->text('note');
                $table->enum('visibility', ['public', 'internal', 'private'])->default('internal');
                $table->boolean('is_pinned')->default(false);
                $table->timestamps();
                
                $table->index(['application_id', 'visibility']);
            });
        }

        // Recommendation Letters
        if (!Schema::hasTable('recommendation_letters')) {
            Schema::create('recommendation_letters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id')->constrained('admission_applications')->onDelete('cascade');
                $table->string('recommender_name', 200);
                $table->string('recommender_email');
                $table->string('recommender_title', 100)->nullable();
                $table->string('recommender_institution', 255)->nullable();
                $table->string('recommender_phone', 20)->nullable();
                $table->string('relationship', 100)->nullable();
                $table->string('request_token', 100)->unique();
                $table->enum('status', [
                    'pending',
                    'sent',
                    'opened',
                    'submitted',
                    'declined',
                    'expired'
                ])->default('pending');
                $table->text('letter_content')->nullable();
                $table->string('letter_file_path', 500)->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('reminder_sent_at')->nullable();
                $table->integer('reminder_count')->default(0);
                $table->timestamps();
                
                $table->index(['application_id', 'status']);
                $table->index('request_token');
            });
        }

        // ============================================
        // FIX 7: Update existing data with defaults
        // ============================================
        
        // Update current term to have admission open
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        if ($currentTerm) {
            DB::table('academic_terms')->where('id', $currentTerm->id)->update([
                'is_admission_open' => true,
                'admission_deadline' => now()->addMonths(2),
                'admission_start_date' => now()->subMonth(),
                'admission_notification_date' => now()->addMonths(3),
                'total_spots' => 500
            ]);
        }

        // Update programs to accept applications
        DB::table('academic_programs')->where('is_active', true)->update([
            'accepts_applications' => true,
            'degree_type' => DB::raw("CASE 
                WHEN level = 'bachelor' THEN 'BS'
                WHEN level = 'master' THEN 'MS'
                WHEN level = 'doctorate' THEN 'PhD'
                ELSE 'Certificate'
            END"),
            'program_type' => DB::raw("CASE 
                WHEN level IN ('bachelor', 'associate') THEN 'undergraduate'
                WHEN level IN ('master', 'doctorate') THEN 'graduate'
                ELSE 'certificate'
            END"),
            'application_types' => json_encode(['freshman', 'transfer', 'international']),
            'enrollment_capacity' => 200
        ]);

        // ============================================
        // FIX 8: Add missing indexes for performance
        // ============================================
        Schema::table('admission_applications', function (Blueprint $table) {
            if (!$this->indexExists('admission_applications', 'idx_user_email')) {
                $table->index(['user_id', 'email'], 'idx_user_email');
            }
            if (!$this->indexExists('admission_applications', 'idx_term_program')) {
                $table->index(['term_id', 'program_id'], 'idx_term_program');
            }
        });
    }

    /**
     * Check if an index exists
     */
    private function indexExists($table, $indexName): bool
    {
        $indexes = DB::select("
            SELECT indexname 
            FROM pg_indexes 
            WHERE tablename = ? AND indexname = ?
        ", [$table, $indexName]);
        
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('recommendation_letters');
        Schema::dropIfExists('application_notes');
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');

        // Remove added columns
        Schema::table('academic_terms', function (Blueprint $table) {
            $columns = [
                'is_admission_open',
                'admission_deadline',
                'admission_start_date',
                'early_admission_deadline',
                'admission_notification_date',
                'total_spots'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('academic_terms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('academic_programs', function (Blueprint $table) {
            $columns = [
                'accepts_applications',
                'application_fee',
                'degree_type',
                'program_type',
                'delivery_mode',
                'enrollment_capacity',
                'application_types'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('academic_programs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};