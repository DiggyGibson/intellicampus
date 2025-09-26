<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop existing tables if they exist (since we're starting fresh)
        Schema::dropIfExists('transcript_payments');
        Schema::dropIfExists('transcript_verifications');
        Schema::dropIfExists('transcript_logs');
        Schema::dropIfExists('transcript_requests');
        Schema::dropIfExists('student_honors');

        // 1. TRANSCRIPT REQUESTS - Main table for all transcript requests
        Schema::create('transcript_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('requested_by')->constrained('users');
            
            // Request Details
            $table->enum('type', ['official', 'unofficial']);
            $table->enum('delivery_method', ['electronic', 'mail', 'pickup']);
            $table->integer('copies')->default(1);
            
            // Recipient Information
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->text('mailing_address')->nullable();
            
            // Request Information
            $table->string('purpose');
            $table->boolean('rush_order')->default(false);
            $table->text('special_instructions')->nullable();
            
            // Fee and Payment
            $table->decimal('fee', 10, 2)->default(0);
            $table->enum('payment_status', ['not_required', 'pending', 'paid', 'waived', 'refunded'])
                ->default('not_required');
            $table->timestamp('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            
            // Processing Status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'cancelled',
                'on_hold'
            ])->default('pending');
            
            // Processing Information
            $table->timestamp('requested_at');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Delivery Information
            $table->string('tracking_number')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('file_path')->nullable();
            
            // Admin
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['student_id', 'status']);
            $table->index('request_number');
            $table->index('verification_code');
            $table->index(['status', 'payment_status']);
        });

        // 2. TRANSCRIPT VERIFICATIONS - For verifying official transcripts
        Schema::create('transcript_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('transcript_request_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('verification_code')->unique();
            $table->enum('type', ['official', 'unofficial'])->default('official');
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at');
            $table->timestamp('expires_at');
            $table->foreignId('generated_by')->constrained('users');
            $table->integer('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->json('metadata')->nullable(); // Store additional verification data
            $table->timestamps();
            
            $table->index('verification_code');
            $table->index(['student_id', 'expires_at']);
            $table->index('transcript_request_id');
        });

        // 3. TRANSCRIPT LOGS - Audit trail for all transcript activities
        Schema::create('transcript_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('transcript_request_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('action', [
                'viewed',
                'generated',
                'downloaded',
                'requested',
                'processed',
                'completed',
                'cancelled',
                'verified'
            ]);
            $table->enum('type', ['official', 'unofficial']);
            $table->string('purpose')->nullable();
            $table->foreignId('performed_by')->constrained('users');
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamp('performed_at');
            $table->timestamps();
            
            $table->index(['student_id', 'performed_at']);
            $table->index('performed_by');
            $table->index('transcript_request_id');
            $table->index('action');
        });

        // 4. STUDENT HONORS - Academic achievements and awards
        Schema::create('student_honors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('term_id')->nullable()->constrained('academic_terms');
            
            $table->enum('honor_type', [
                'deans_list',
                'presidents_list',
                'honor_roll',
                'cum_laude',
                'magna_cum_laude',
                'summa_cum_laude',
                'academic_achievement',
                'scholarship',
                'award',
                'other'
            ]);
            
            $table->string('honor_name');
            $table->text('description')->nullable();
            $table->string('academic_year')->nullable();
            $table->date('awarded_date');
            $table->string('awarded_by')->nullable();
            $table->decimal('gpa_earned', 3, 2)->nullable();
            $table->json('metadata')->nullable(); // Additional honor details
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['student_id', 'honor_type']);
            $table->index(['student_id', 'academic_year']);
            $table->index('term_id');
        });

        // 5. TRANSCRIPT PAYMENTS - Separate payment tracking (optional)
        Schema::create('transcript_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcript_request_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', [
                'credit_card',
                'debit_card',
                'bank_transfer',
                'cash',
                'check',
                'online',
                'waived'
            ]);
            $table->string('reference_number')->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded']);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('transcript_request_id');
            $table->index('status');
            $table->index('reference_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transcript_payments');
        Schema::dropIfExists('student_honors');
        Schema::dropIfExists('transcript_logs');
        Schema::dropIfExists('transcript_verifications');
        Schema::dropIfExists('transcript_requests');
    }
};