<?php

namespace App\Jobs;

use App\Events\BulkStatusChangeCompleted;
use App\Models\Trail;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BulkTrailStatusChangeJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Trail IDs to update
     *
     * @var array
     */
    protected array $trailIds;

    /**
     * New status value
     *
     * @var string
     */
    protected string $status;

    /**
     * User ID who initiated the bulk operation
     *
     * @var int
     */
    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $trailIds, string $status, int $userId)
    {
        $this->trailIds = $trailIds;
        $this->status = $status;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if batch is cancelled
        if ($this->batch()->cancelled()) {
            Log::info('Batch was cancelled', [
                'batch_id' => $this->batch()->id,
                'trail_ids' => $this->trailIds,
            ]);

            return;
        }

        Log::info('Starting bulk status change', [
            'batch_id' => $this->batch()->id,
            'trail_count' => count($this->trailIds),
            'new_status' => $this->status,
            'user_id' => $this->userId,
        ]);

        $successCount = 0;
        $failedCount = 0;
        $failedIds = [];

        // Process each trail with transaction
        foreach ($this->trailIds as $trailId) {
            try {
                DB::transaction(function () use ($trailId) {
                    $trail = Trail::lockForUpdate()->findOrFail($trailId);

                    $trail->update([
                        'status' => $this->status,
                    ]);
                });

                $successCount++;

                Log::debug('Trail status updated successfully', [
                    'trail_id' => $trailId,
                    'new_status' => $this->status,
                ]);
            } catch (Throwable $e) {
                $failedCount++;
                $failedIds[] = $trailId;

                Log::error('Failed to update trail status', [
                    'trail_id' => $trailId,
                    'new_status' => $this->status,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Don't throw - continue processing other trails
            }
        }

        Log::info('Bulk status change chunk completed', [
            'batch_id' => $this->batch()->id,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'failed_ids' => $failedIds,
        ]);

        // Fire event when batch is finished (last job in batch)
        if ($this->batch()->finished()) {
            $totalProcessed = $this->batch()->totalJobs;
            $totalFailed = $this->batch()->failedJobs;

            Log::info('Bulk status change batch completed', [
                'batch_id' => $this->batch()->id,
                'total_jobs' => $totalProcessed,
                'failed_jobs' => $totalFailed,
                'user_id' => $this->userId,
            ]);

            // Broadcast completion event
            event(new BulkStatusChangeCompleted(
                batchId: $this->batch()->id,
                userId: $this->userId,
                totalProcessed: $totalProcessed,
                successCount: $totalProcessed - $totalFailed,
                failedCount: $totalFailed,
                status: $this->status
            ));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Bulk status change job failed', [
            'batch_id' => $this->batch()?->id,
            'trail_ids' => $this->trailIds,
            'new_status' => $this->status,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Optionally notify user about failure
        // Could dispatch a notification job here
    }
}
