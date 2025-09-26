<?php

// ============================================
// database/seeders/PaymentGatewaySeeder.php
// ============================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentGatewaySeeder extends Seeder
{
    public function run()
    {
        // Insert Stripe gateway configuration
        DB::table('payment_gateways')->insert([
            'name' => 'Stripe',
            'provider' => 'stripe',
            'is_active' => true,
            'is_test_mode' => true,
            'settings' => json_encode([
                'webhook_url' => '/webhook/stripe',
                'supported_cards' => ['visa', 'mastercard', 'amex', 'discover'],
                'supported_countries' => ['US', 'CA', 'GB', 'AU']
            ]),
            'supported_currencies' => json_encode(['USD', 'EUR', 'GBP', 'CAD']),
            'transaction_fee_percent' => 2.9,
            'transaction_fee_fixed' => 0.30,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Insert PayPal gateway configuration (inactive for now)
        DB::table('payment_gateways')->insert([
            'name' => 'PayPal',
            'provider' => 'paypal',
            'is_active' => false,
            'is_test_mode' => true,
            'settings' => json_encode([
                'webhook_url' => '/webhook/paypal',
                'button_style' => 'gold'
            ]),
            'supported_currencies' => json_encode(['USD', 'EUR', 'GBP', 'CAD', 'AUD']),
            'transaction_fee_percent' => 3.49,
            'transaction_fee_fixed' => 0.49,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Sample fee structure if not exists
        if (DB::table('fee_structures')->count() == 0) {
            // Get first program and term
            $program = DB::table('academic_programs')->first();
            $term = DB::table('academic_terms')->where('is_current', true)->first();
            
            if ($program && $term) {
                DB::table('fee_structures')->insert([
                    [
                        'name' => 'Undergraduate Tuition',
                        'code' => 'UG_TUITION',
                        'type' => 'tuition',
                        'amount' => 500.00,
                        'per_credit_amount' => 500.00,
                        'frequency' => 'per_credit',
                        'program_id' => $program->id,
                        'term_id' => $term->id,
                        'is_mandatory' => true,
                        'is_active' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ],
                    [
                        'name' => 'Registration Fee',
                        'code' => 'REG_FEE',
                        'type' => 'registration',
                        'amount' => 50.00,
                        'frequency' => 'per_term',
                        'program_id' => $program->id,
                        'term_id' => $term->id,
                        'is_mandatory' => true,
                        'is_active' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ],
                    [
                        'name' => 'Technology Fee',
                        'code' => 'TECH_FEE',
                        'type' => 'technology',
                        'amount' => 100.00,
                        'frequency' => 'per_term',
                        'program_id' => $program->id,
                        'term_id' => $term->id,
                        'is_mandatory' => true,
                        'is_active' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ],
                    [
                        'name' => 'Student Activity Fee',
                        'code' => 'ACTIVITY_FEE',
                        'type' => 'activity',
                        'amount' => 75.00,
                        'frequency' => 'per_term',
                        'program_id' => $program->id,
                        'term_id' => $term->id,
                        'is_mandatory' => true,
                        'is_active' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ],
                    [
                        'name' => 'Health Services Fee',
                        'code' => 'HEALTH_FEE',
                        'type' => 'health',
                        'amount' => 150.00,
                        'frequency' => 'per_term',
                        'program_id' => $program->id,
                        'term_id' => $term->id,
                        'is_mandatory' => true,
                        'is_active' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]
                ]);
            }
        }

        $this->command->info('Payment gateways and fee structures seeded successfully!');
    }
}