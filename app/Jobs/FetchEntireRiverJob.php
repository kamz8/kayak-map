<?php

namespace App\Jobs;

use App\Models\River;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Kamz8\LaravelOverpass\Facades\Overpass;

class FetchEntireRiverJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected string $riverName;

    /**
     * Create a new job instance.
     *
     * @param string $riverName
     */
    public function __construct(string $riverName)
    {
        $this->riverName = $riverName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Wykonaj zapytanie do Overpass API, aby pobrać całą rzekę o podanej nazwie
            $data = Overpass::query()
                ->way()
                ->where('waterway', 'river')
                ->where('name', $this->riverName)
                ->recurse()
                ->output('json')
                ->get();

            if (empty($data['elements'])) {
                Log::warning("Brak danych dla rzeki: {$this->riverName}");
                return;
            }

            // Przetwarzanie danych
            $nodes = [];
            $ways = [];

            foreach ($data['elements'] as $element) {
                if ($element['type'] === 'node') {
                    $nodes[$element['id']] = [$element['lon'], $element['lat']];
                } elseif ($element['type'] === 'way') {
                    $ways[] = $element['nodes'];
                }
            }

            if (empty($ways)) {
                Log::warning("Brak dróg dla rzeki: {$this->riverName}");
                return;
            }

            // Łączenie wszystkich dróg w jedną linię
            $lineStringPoints = [];

            foreach ($ways as $wayNodes) {
                foreach ($wayNodes as $nodeId) {
                    if (isset($nodes[$nodeId])) {
                        $lineStringPoints[] = $nodes[$nodeId];
                    }
                }
            }

            if (empty($lineStringPoints)) {
                Log::warning("Brak punktów ścieżki dla rzeki: {$this->riverName}");
                return;
            }

            // Konwersja punktów do WKT LineString
            $lineStringWKT = $this->convertToWKTLineString($lineStringPoints);

            // Zapisz rzekę w bazie danych
            River::updateOrCreate(
                ['name' => $this->riverName],
                ['path' => $lineStringWKT]
            );

            Log::info("Rzeka '{$this->riverName}' została zapisana w bazie danych.");

        } catch (\Exception $e) {
            Log::error("Błąd podczas pobierania rzeki '{$this->riverName}': " . $e->getMessage());
        }
    }

    /**
     * Konwertuje punkty do formatu WKT LineString.
     *
     * @param array $points
     * @return string
     */
    protected function convertToWKTLineString(array $points): string
    {
        $coordinates = array_map(function ($point) {
            return implode(' ', $point);
        }, $points);

        return 'LINESTRING(' . implode(', ', $coordinates) . ')';
    }
}
