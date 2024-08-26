<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class CheckImportStatus extends Command
{
    protected $signature = 'import:status {batchId : The ID of the batch to check}';
    protected $description = 'Check the status of an import batch';

    public function handle()
    {
        $batchId = $this->argument('batchId');
        $batch = Bus::findBatch($batchId);

        if (!$batch) {
            $this->error("Batch not found.");
            return 1;
        }

        $this->info("Batch Status: " . $batch->name);
        $this->info("Total Jobs: " . $batch->totalJobs);
        $this->info("Processed Jobs: " . $batch->processedJobs());
        $this->info("Failed Jobs: " . $batch->failedJobs);
        $this->info("Progress: " . $batch->progress() . "%");

        return 0;
    }
}
