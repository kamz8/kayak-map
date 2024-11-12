<?php

namespace App\Jobs;

use App\Events\GpxProcessingCompleted;
use App\Models\GpxProcessingStatus;
use App\Models\Trail;
use App\Models\RiverTrack;
use App\Services\GpxProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessGpxFileJob implements ShouldQueue
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
        // 1. Dodaj szczegółowe logowanie na początku joba
        Log::info('Starting GPX processing', [
            'file_path' => $this->filePath,
            'trail_id' => $this->trailId,
            'status_id' => $this->statusId
        ]);
        // Sprawdź, czy plik jest w katalogu tymczasowym
        $isTemporaryFile = Str::contains($this->filePath, '/temp/');

        try {
            $status = GpxProcessingStatus::findOrFail($this->statusId);
            $trail = Trail::findOrFail($this->trailId);

            DB::beginTransaction();
            DB::enableQueryLog();
            try {
                // Przetwórz plik GPX
                $processedData = $gpxProcessor->process(Storage::path($this->filePath));

                // Aktualizuj trail standardowymi współrzędnymi
                $trail->update([
                    'start_lat' => $processedData['start_lat'],
                    'start_lng' => $processedData['start_lng'],
                    'end_lat' => $processedData['end_lat'],
                    'end_lng' => $processedData['end_lng'],
                    'trail_length' => (int)$processedData['distance']
                ]);

                if (isset($processedData['path']) && !empty($processedData['path']->getCoordinates())) {

                    $riverTrack = RiverTrack::updateOrCreate(
                        ['trail_id' => $trail->id],
                        ['track_points' => $processedData['path']]
                    );

                } else {
                    Log::warning('No valid path data in GPX file', [
                        'trail_id' => $trail->id,
                        'file' => $this->filePath
                    ]);
                    throw new \Exception('No valid path data found in GPX file');
                }

                DB::commit();

                // Aktualizuj status
                $status->update([
                    'status' => 'completed',
                    'message' => 'GPX successfully processed',
                    'processed_at' => now()
                ]);

                // Wyślij powiadomienie
                event(new GpxProcessingCompleted($trail->id));

                // Wyczyść cache jeśli jest używany
                //Cache::tags(['trails', 'trail-'.$trail->id])->flush();




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

            GpxProcessingStatus::find($this->statusId)?->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'processed_at' => now()
            ]);

            throw $e;
        } finally {
            // Usuń plik tylko jeśli jest w katalogu tymczasowym
            if ($isTemporaryFile && Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GPX Processing Failed', [
            'trail_id' => $this->trailId,
            'file' => $this->filePath,
            'error' => $exception->getMessage()
        ]);

        // Aktualizuj status w przypadku błędu
        GpxProcessingStatus::find($this->statusId)?->update([
            'status' => 'failed',
            'message' => $exception->getMessage(),
            'processed_at' => now()
        ]);

        // Usuń plik tymczasowy w przypadku błędu, jeśli istnieje
        if (Str::contains($this->filePath, '/temp/') && Storage::exists($this->filePath)) {
            Storage::delete($this->filePath);
        }
    }
}
