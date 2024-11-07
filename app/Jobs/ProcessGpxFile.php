<?php
namespace App\Jobs;

use App\Models\GpxProcessingStatus;
use App\Models\Trail;
use App\Models\RiverTrack;
use App\Services\GpxProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessGpxFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected string $filePath;
    protected int $trailId;
    protected int $statusId;

    public function __construct(string $filePath, int $trailId, int $statusId)
    {
        $this->filePath = $filePath;
        $this->trailId = $trailId;
        $this->statusId = $statusId;
    }

    public function handle(GpxProcessor $gpxProcessor): void
    {
        try {
            $status = GpxProcessingStatus::findOrFail($this->statusId);
            $trail = Trail::findOrFail($this->trailId);

            DB::beginTransaction();

            try {
                // Przetwórz plik GPX
                $processedData = $gpxProcessor->process(Storage::path($this->filePath));

                // Aktualizuj trail
                $trail->update([
                    'start_point' => $processedData['start_point'],
                    'end_point' => $processedData['end_point'],
                    'trail_length' => (int)$processedData['distance']
                ]);

                // Utwórz lub zaktualizuj river_track
                RiverTrack::updateOrCreate(
                    ['trail_id' => $trail->id],
                    ['track_line' => $processedData['track_line']]
                );

                DB::commit();

                // Aktualizuj status
                $status->update([
                    'status' => 'completed',
                    'message' => 'GPX successfully processed',
                    'processed_at' => now()
                ]);

                // Wyślij powiadomienie
                event(new GpxProcessingCompleted($trail->id));
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('GPX Processing Error', [
                'trail_id' => $this->trailId,
                'file' => $this->filePath,
                'error' => $e->getMessage()
            ]);

            $status->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'processed_at' => now()
            ]);

            throw $e;
        } finally {
            // Wyczyść tymczasowy plik
            Storage::delete($this->filePath);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GPX Processing Failed', [
            'trail_id' => $this->trailId,
            'file' => $this->filePath,
            'error' => $exception->getMessage()
        ]);
    }
}
