<?php

namespace App\Services;

use App\Models\Trail;
use App\Models\Section;
use App\Models\Point;
use App\Models\PointType;
use App\Enums\Difficulty;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class XMLTrailImporter
{
    protected Trail $currentTrail;
    protected ?Section $currentSection = null;
    protected ?Point $currentPoint = null;
    protected string $characterDataBuffer = '';
    protected array $trailPoints = [];

    public function import(string $filePath): Trail
    {
        $this->parseXMLFile($filePath);
        return $this->currentTrail;
    }

    protected function parseXMLFile($filePath)
    {
        $parser = xml_parser_create('UTF-8');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, [$this, "startElement"], [$this, "endElement"]);
        xml_set_character_data_handler($parser, [$this, "characterData"]);

        if (!Storage::exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        $fileContent = Storage::get($filePath);
        if (!xml_parse($parser, $fileContent)) {
            throw new \Exception(sprintf("XML error: %s at line %d in file %s",
                xml_error_string(xml_get_error_code($parser)),
                xml_get_current_line_number($parser),
                $filePath));
        }

        xml_parser_free($parser);
    }

    protected function startElement($parser, $name, $attrs)
    {
        $this->characterDataBuffer = '';

        switch ($name) {
            case 'szlak':
                $this->currentTrail = new Trail([
                    'river_name' => $attrs['nazwa'] ?? 'Unnamed River',
                    'trail_name' => $attrs['nazwa'] ?? 'Unnamed Trail',
                    'slug' => Str::slug($attrs['nazwa'] ?? 'Unnamed Trail'),
                    'description' => '',
                    'difficulty' => $this->mapDifficulty($attrs['trudnosc'] ?? 'umiarkowany'),
                    'scenery' => 0,
                    'rating' => 0.0,
                    'author' => $attrs['autor'] ?? 'Unknown',
                    'start_lat' => 0,
                    'start_lng' => 0,
                    'end_lat' => 0,
                    'end_lng' => 0,
                    'trail_length' => 0,
                ]);
                $this->currentTrail->save();  // Zapisanie szlaku
                break;

            case 'odcinek':
                if (!isset($this->currentTrail->id)) {
                    throw new \Exception("Trail must be saved before adding sections");
                }

                $this->currentSection = new Section([
                    'trail_id' => $this->currentTrail->id,  // Użycie trail_id
                    'name' => $attrs['nazwa'] ?? 'Unnamed Section',
                    'description' => '',
                    'scenery' => $this->convertSceneryToNumber($attrs['malowniczosc'] ?? ''),
                    'polygon_coordinates' => '[]',
                    'order' => $attrs['kolejnosc'] ?? 0,
                ]);
                $this->currentSection->save();  // Zapisanie odcinka przed dodaniem punktów
                break;

            case 'punkt':
                if (!isset($this->currentSection->id)) {
                    throw new \Exception("Section must be saved before adding points");
                }

                $this->currentPoint = new Point([
                    'trail_id' => $this->currentTrail->id,  // Użycie trail_id
                    'section_id' => $this->currentSection->id,  // Użycie section_id
                    'name' => $attrs['miejscowosc'] ?? 'Unknown Point',
                    'description' => '',
                    'lat' => $attrs['ns'] ?? 0,
                    'lng' => $attrs['we'] ?? 0,
                    'point_type_id' => PointType::firstOrCreate(['type' => $attrs['typ'] ?? 'Other'])->id,
                ]);
                $this->currentPoint->save();  // Zapisanie punktu
                $this->trailPoints[] = $this->currentPoint;
                break;
        }
    }

    protected function endElement($parser, $name)
    {
        switch ($name) {
            case 'szlak':
                $this->currentTrail->description = trim($this->characterDataBuffer);
                $this->updateTrailData();
                $this->currentTrail->save();
                break;

            case 'punkt':
                if ($this->currentPoint) {
                    $this->currentPoint->description = trim($this->characterDataBuffer);
                    $this->currentPoint->save();
                }
                break;

            case 'odcinek':
                if ($this->currentSection) {
                    $this->currentSection->description = trim($this->characterDataBuffer);
                    $this->currentSection->save();
                }
                break;


        }

        $this->characterDataBuffer = '';
    }

    protected function characterData($parser, $data)
    {
        $this->characterDataBuffer .= $data;
    }

    protected function mapDifficulty($difficultyString): Difficulty
    {
        return match (Str::lower($difficultyString)) {
            'łatwy', 'latwy', 'zwa' => Difficulty::EASY,
            'trudny', 'zwc' => Difficulty::HARD,
            default => Difficulty::MODERATE,
        };
    }

    protected function convertSceneryToNumber($scenery): int
    {
        return strlen($scenery); // Example logic, can be adjusted
    }

    protected function updateTrailData()
    {
        if (!empty($this->trailPoints)) {
            $this->currentTrail->start_lat = $this->trailPoints[0]->lat;
            $this->currentTrail->start_lng = $this->trailPoints[0]->lng;
            $this->currentTrail->end_lat = end($this->trailPoints)->lat;
            $this->currentTrail->end_lng = end($this->trailPoints)->lng;
            $this->currentTrail->trail_length = $this->calculateTotalLength();
            $this->currentTrail->scenery = $this->calculateAverageScenery();
            $this->currentTrail->rating = $this->calculateRating();
        }
    }


    protected function calculateTotalLength(): int
    {
        $totalLength = 0;

        for ($i = 1; $i < count($this->trailPoints); $i++) {
            $lat1 = (float) $this->trailPoints[$i-1]->lat;  // Konwersja na float
            $lng1 = (float) $this->trailPoints[$i-1]->lng;  // Konwersja na float
            $lat2 = (float) $this->trailPoints[$i]->lat;    // Konwersja na float
            $lng2 = (float) $this->trailPoints[$i]->lng;    // Konwersja na float

            $totalLength += GeodataService::calculateDistance($lat1, $lng1, $lat2, $lng2);
        }

        return $totalLength; // Konwersja na metry
    }


    protected function calculateAverageScenery(): int
    {
        $totalScenery = array_sum(array_map('intval', array_column($this->currentTrail->sections->toArray(), 'scenery')));
        $sectionCount = $this->currentTrail->sections->count();

        return $sectionCount > 0 ? round($totalScenery / $sectionCount) : 0;
    }

    protected function calculateRating(): float
    {
        $averageScenery = (int) $this->calculateAverageScenery();  // Konwersja na int
        $averageDifficulty = (int) $this->currentTrail->difficulty->value;  // Konwersja na int
        $averageInconvenience = (int) $this->convertInconvenienceToNumber($this->currentTrail->inconvenience ?? 'U2');  // Konwersja uciążliwości

        // Przykładowa formuła: uwzględnia malowniczość, trudność i uciążliwość
        return min(max((10/$averageScenery  - $averageDifficulty - $averageInconvenience) / 2, 0), 5);
    }

    protected function convertInconvenienceToNumber($inconvenience): int
    {
        return match (strtoupper($inconvenience)) {
            'U1' => 1,
            'U2' => 2,
            'U3' => 3,
            'U4' => 4,
            'U5' => 5,
            'U6' => 6,
            default => 2,  // Domyślna wartość, jeśli brak danych
        };
    }

}
