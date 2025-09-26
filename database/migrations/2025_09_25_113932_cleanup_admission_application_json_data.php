<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CleanupAdmissionApplicationJsonData extends Migration
{
    public function up()
    {
        $applications = DB::table('admission_applications')->get();
        
        foreach ($applications as $app) {
            $updates = [];
            
            // Fix test_scores
            if ($app->test_scores && is_string($app->test_scores)) {
                $decoded = json_decode($app->test_scores, true);
                if ($decoded !== null) {
                    $updates['test_scores'] = json_encode($decoded);
                }
            }
            
            // Fix other JSON fields
            $jsonFields = [
                'extracurricular_activities',
                'awards_honors',
                'references',
                'custom_requirements',
                'activity_log'
            ];
            
            foreach ($jsonFields as $field) {
                if ($app->$field && is_string($app->$field)) {
                    // Check if it's double-encoded
                    $decoded = json_decode($app->$field, true);
                    if ($decoded !== null) {
                        $updates[$field] = json_encode($decoded);
                    }
                }
            }
            
            if (!empty($updates)) {
                DB::table('admission_applications')
                    ->where('id', $app->id)
                    ->update($updates);
            }
        }
    }
    
    public function down()
    {
        // Not reversible
    }
}