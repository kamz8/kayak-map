<?php

namespace App\Console\Commands;

use App\Jobs\ImportTrailFileJob;
use Illuminate\Console\Command;
use App\Jobs\ImportTrailFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Batch;
use Throwable;
use Illuminate\Support\Facades\DB;

class ImportTrailsCommand extends Command
{
    protected $signature = 'import:trails {directory : The directory containing XML files}';
    protected $description = 'Import trails data from XML files using queues';

    public function handle()
    {
        $directory = $this->argument('directory');
        $files = Storage::files($directory);

        if (empty($files)) {
            $this->error("No XML files found in the specified directory.");
            Log::channel('trails_import')->warning("No XML files found in directory: {$directory}");
            return 1;
        }

        $totalFiles = count($files);
        $this->info("Found {$totalFiles} files to process.");
        Log::channel('trails_import')->info("Starting import process", ['total_files' => $totalFiles]);

        $jobs = array_map(function ($file) {
            return new ImportTrailFileJob($file);
        }, $files);

        $batch = Bus::batch($jobs)
            ->then(function (Batch $batch) use ($totalFiles) {
                $message = "All {$totalFiles} files have been processed successfully.";

                info($message);
                Log::channel('trails_import')->info($message);
            })
            ->catch(function (Batch $batch, Throwable $e) {
                $message = 'An error occurred during batch processing: ' . $e->getMessage();
                error($message);
                Log::channel('trails_import')->error($message);
            })
            ->finally(function (Batch $batch) {
                $message = 'The batch has finished executing.';
                info($message);
                Log::channel('trails_import')->info($message);
            })
            ->dispatch();

        $this->info('Import jobs have been dispatched to the queue.');
        $this->info('Batch ID: ' . $batch->id);
        Log::channel('trails_import')->info('Import jobs dispatched', ['batch_id' => $batch->id]);

        $this->monitorBatchProgress($batch->id, $totalFiles);

        return 0;
    }

    protected function monitorBatchProgress($batchId, $totalFiles)
    {
        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        while (true) {
            $batch = Bus::findBatch($batchId);

            if (!$batch) {
                $this->error("Batch not found.");
                break;
            }

            $bar->setProgress($batch->processedJobs());

            if ($batch->finished()) {
                break;
            }

            sleep(1); // Czekaj 1 sekundę przed kolejnym sprawdzeniem
        }

        $bar->finish();
        $this->newLine(2);

        $this->displayBatchSummary($batchId);
    }

    protected function displayBatchSummary($batchId)
    {
        $batch = Bus::findBatch($batchId);

        $this->info("Batch Summary:");
        $this->info("Total Jobs: " . $batch->totalJobs);
        $this->info("Processed Jobs: " . $batch->processedJobs());
        $this->info("Failed Jobs: " . $batch->failedJobs);

        if ($batch->failedJobs > 0) {
            $this->error("Failed Jobs Details:");
            $failedJobsJson = DB::table('job_batches')
                ->where('id', $batchId)
                ->value('failed_jobs');

            if ($failedJobsJson) {
                $failedJobs = json_decode($failedJobsJson, true);
                if (is_array($failedJobs)) {
                    foreach ($failedJobs as $failedJob) {
                        $this->error("File: " . ($failedJob['payload']['filePath'] ?? 'Unknown'));
                        $this->error("Error: " . ($failedJob['exception'] ?? 'No error message available'));
                        $this->newLine();
                    }
                } else {
                    $this->error("Failed to parse failed jobs data.");
                }
            } else {
                $this->error("No detailed information available for failed jobs.");
            }
        }
    }
}
