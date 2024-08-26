<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\XMLTrailImporter;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportTrailFileJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }
    public function handle(XMLTrailImporter $importer)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        try {
            $trail = $importer->import($this->filePath);

            Log::channel('trails_import')->info("File processed: {$this->filePath}", [
                'status' => 'success',
                'saved_to_db' => 'Yes',
                'trail_id' => $trail->id
            ]);
        } catch (Throwable $e) {
            Log::channel('trails_import')->error("Error processing file: {$this->filePath}", [
                'status' => 'error',
                'error' => $e->getMessage(),
                'saved_to_db' => 'No'
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception)
    {
        Log::channel('trails_import')->error("Job failed for file: {$this->filePath}", [
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'saved_to_db' => 'No'
        ]);
    }
}
