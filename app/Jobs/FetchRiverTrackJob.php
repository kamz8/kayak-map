<?php

namespace App\Jobs;

use App\Models\RiverTrack;
use App\Models\Trail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Kamz8\LaravelOverpass\Facades\Overpass;

class FetchRiverTrackJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected int $trailId;

    /**
     * Create a new job instance.
     *
     * @param int $trailId
     */
    public function __construct(int $trailId)
    {
        $this->trailId = $trailId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Pobierz trasę z bazy danych
        $trail = Trail::find($this->trailId);

        if (!$trail) {
            Log::error("Nie znaleziono trasy o ID: {$this->trailId}");
            return;
        }

        try {
            // Generuj bounding box z marginesem 10%
            $bboxHelper = new \Kamz8\LaravelOverpass\Helpers\BoundingBoxHelper();
            $bbox = $bboxHelper->generateBBox(
                $trail->start_lat,
                $trail->start_lng,
                $trail->end_lat,
                $trail->end_lng,
                marginPercent: 10
            );

            // Wykonaj zapytanie do Overpass API
            $data = Overpass::query()
                ->way()
                ->where('waterway', 'river')
                ->where('name', $trail->river_name)
                ->bboxFromPoints(
                    $trail->start_lat,
                    $trail->start_lng,
                    $trail->end_lat,
                    $trail->end_lng,
                    marginPercent: 10
                )
                ->recurse()
                ->output('json')
                ->get();

            if (empty($data['elements'])) {
                Log::warning("Brak danych rzeki dla trasy ID: {$this->trailId}");
                return;
            }

            // Przetwarzaj dane i wyodrębnij punkty ścieżki
            $trackPoints = [];

            foreach ($data['elements'] as $element) {
                if ($element['type'] === 'way' && isset($element['nodes'])) {
                    // Możemy przechowywać IDs węzłów, jeśli potrzebne
                }
                if ($element['type'] === 'node' && isset($element['lat'], $element['lon'])) {
                    $trackPoints[] = [
                        'lat' => $element['lat'],
                        'lon' => $element['lon'],
                    ];
                }
            }

            if (empty($trackPoints)) {
                Log::warning("Brak punktów ścieżki dla trasy ID: {$this->trailId}");
                return;
            }

            // Zapisz punkty ścieżki w bazie danych
            RiverTrack::updateOrCreate(
                ['trail_id' => $this->trailId],
                ['track_points' => json_encode($trackPoints, JSON_THROW_ON_ERROR)]
            );

            Log::info("Ścieżka rzeki zapisana dla trasy ID: {$this->trailId}");

        } catch (\Exception $e) {
            Log::error("Błąd podczas pobierania ścieżki rzeki dla trasy ID: {$this->trailId} - " . $e->getMessage());
        }
    }
}
