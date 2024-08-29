<?php

namespace App\Console\Commands;

use App\Enums\Difficulty;
use App\Models\Odcinek as OdcinekModel;
use App\Models\OpisPunktu as OpisPunktuModel;
use App\Models\Point;
use App\Models\PointDescription;
use App\Models\Punkt as PunktModel;
use App\Models\Region;
use App\Models\Section;
use App\Models\Szlak as SzlakModel;
use App\Models\Trail;
use App\Services\GeodataService;
use App\Services\PointTypeService;
use App\Services\TempImporterClasses\Odcinek;
use App\Services\XMLPathreader;
use App\Utils\DifficultyCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportSplywwwCommand extends Command
{
    protected $signature = 'import:xml';
    protected $description = 'Parse XML path data from files in the storage/app/paths directory using XMLPathreader';

    protected $pointTypeService;
    protected $successfulImports = 0;
    protected $failedImports = 0;
    protected $failedFiles = [];

    public function handle()
    {
        $this->info('Starting XML path data parsing...');

        $files = Storage::disk('local')->files('szlaki');

        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();
        $this->line(collect($files)->count());

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
                $fullPath = Storage::path($file);

                try {
                    $parser = new XMLPathreader($fullPath);
                    $szlak = $parser->szlak;

                    DB::beginTransaction();
                    $this->saveSzlakData($szlak);
                    DB::commit();

                    $this->successfulImports++;
//                    $this->line("\nPath data successfully loaded: " . $szlak->nazwa . " " . $fullPath);
                    Log::channel('import')->info("Successfully imported: " . $szlak->nazwa . " from " . $fullPath);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->failedImports++;
                    $this->failedFiles[] = $fullPath;
//                    $this->error("\nFailed to import: " . $fullPath);
                    Log::channel('import')->error("Failed to import from " . $fullPath, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->displayImportSummary();
    }

    protected function saveSzlakData($szlak)
    {
        $pointTypeService = new PointTypeService();
        $szlakModel = Trail::create([
            'trail_name' => $szlak->nazwa,
            'river_name' => $szlak->nazwa,
            'slug' => Str::slug($szlak->nazwa),
            'description' => $szlak->opis,
            'start_lat' => collect($szlak->punkty)->first()->ns,
            'start_lng' => collect($szlak->punkty)->first()->we,
            'end_lat' => collect($szlak->punkty)->last()->ns,
            'end_lng' => collect($szlak->punkty)->last()->we,
            'trail_length' => GeodataService::calculateDistance(collect($szlak->punkty)->first()->ns, collect($szlak->punkty)->first()->we, collect($szlak->punkty)->last()->ns, collect($szlak->punkty)->last()->we),
            'author' => '',
            'difficulty' => $this->avgDiffCast(collect($szlak->odcinki)->pluck('tru')),
            'difficulty_detailed' => collect($szlak->odcinki)->first()->tru ?? '',
            'scenery' => $this->avgScenery($szlak->odcinki),
            'rating' => 0
        ]);

        foreach ($szlak->odcinki as $odcinek) {
            Section::create([
                'trail_id' => $szlakModel->id,
                'name' => $odcinek->nazwa,
                'description' => $odcinek->opis ?? '',
                'polygon_coordinates' => $odcinek->punkty ?? [],
                'scenery' => $this->convertSceneryToNumber($odcinek->mal),
                'difficulty_detailed' => $odcinek->tru,
                'nuisance' => $odcinek->uci,
                'cleanliness' => $odcinek->czy,
            ]);
        }

        foreach ($szlak->punkty as $punkt) {
            $punktModel = Point::create([
                'trail_id' => $szlakModel->id,
                'km' => $punkt->km,
                'name' => $punkt->miejscowosc,
                'point_type_id' => $pointTypeService->findIdByType('inne'),
                'lat' => $punkt->ns,
                'lng' => $punkt->we,
                'order' => $punkt->kolejnosc,
            ]);

            foreach ($punkt->opisypunktu as $opis) {
                PointDescription::create([
                    'point_id' => $punktModel->id,
                    'point_type' => $opis->typpunktu,
                    'point_type_id' => $pointTypeService->findIdByType($opis->typpunktu),
                    'description' => $opis->opis,
                ]);
            }
        }
    }

    protected function displayImportSummary()
    {
        $this->info("Import process completed.");
        $this->info("Successfully imported files: " . $this->successfulImports);
        $this->info("Failed imports: " . $this->failedImports);

        if ($this->failedImports > 0) {
            $this->warn("The following files were not imported:");
            foreach ($this->failedFiles as $file) {
                $this->line(" - " . $file);
            }
        }
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
        return strlen($scenery)*3; // Example logic, can be adjusted
    }

    protected function avgDiffCast($val) {
        return DifficultyCalculator::castToDifficultyEnum(DifficultyCalculator::calculateAverageDifficulty($val));
    }

    protected function avgScenery(array $odcinek) : int {

        $sceneryCollect = collect($odcinek)->pluck('mal');

        $s = 0;
        $avg = 0;
        $s = $sceneryCollect->map(function($val) use (&$s) {
             return $this->convertSceneryToNumber($val) + $s;
        });
        if ($sceneryCollect->count()) {
            $avg = $s->sum()/$sceneryCollect->count();
        }
        return max(0, min(10, round($avg)));
    }

    protected function mapPointType() {
    }
}
