<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportProductionData extends Command
{
    protected $signature = 'db:import-production';
    protected $description = 'Import production data from SQL file';

    public function handle()
    {
        if (!app()->environment('production')) {
            $this->error('This command only runs in production');
            return 1;
        }

        $sqlFile = database_path('production/intellicampus_data_only.sql');
        
        if (!file_exists($sqlFile)) {
            $this->error('SQL file not found');
            return 1;
        }

        $sql = file_get_contents($sqlFile);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $this->info('Starting import...');
        $bar = $this->output->createProgressBar(count($statements));
        
        $imported = 0;
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    DB::unprepared($statement . ';');
                    $imported++;
                } catch (\Exception $e) {
                    // Skip errors silently
                }
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nImported {$imported} statements successfully");
        return 0;
    }
}