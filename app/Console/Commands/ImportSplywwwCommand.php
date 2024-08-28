<?php

namespace App\Console\Commands;

use App\Enums\Difficulty;
use App\Models\Point;
use App\Models\PointDescription;
use App\Models\Section;
use App\Models\Trail;
use App\Services\GeodataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\XMLPathreader;
use App\Models\Szlak as SzlakModel;
use App\Models\Odcinek as OdcinekModel;
use App\Models\Punkt as PunktModel;
use App\Models\OpisPunktu as OpisPunktuModel;
use Illuminate\Support\Str;

class ImportSplywwwCommand extends Command
{
    protected $signature = 'import:xml';
    protected $description = 'Parse XML path data from files in the storage/app/paths directory using XMLPathreader';

    public function handle()
    {
        $this->info('Starting XML path data parsing...');

        try {
            $files = Storage::files('public/path');

            $progressBar = $this->output->createProgressBar(count($files));
            $progressBar->start();
            $this->line(collect($files)->count());

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
                    $fullPath = Storage::path($file);
                    $parser = new XMLPathreader($fullPath);
                    $szlak = $parser->szlak;

                    $this->saveSzlakData($szlak);
                    $this->line("\nPath data successfully loaded: " . $szlak->nazwa . " " . $fullPath);
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);
            $this->info("Successfully parsed all XML files");
        } catch (\Exception $e) {
            $this->error("Failed to parse data properly!");
            $this->error($e->getMessage());
            Log::error('XML Parsing Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    protected function saveSzlakData($szlak)
    {

        $szlakModel = Trail::create([
            'trail_name' => $szlak->nazwa,
            'river_name' => $szlak->nazwa,
            'slug' => Str::slug($szlak->nazwa),
            'description' => $szlak->opis,
            'start_lat' => collect($szlak->punkty)->first()->ns,
            'start_lng' => collect($szlak->punkty)->first()->ws,
            'end_lat' => collect($szlak->punkty)->last()->ns,
            'end_lng' => collect($szlak->punkty)->last()->we,
            'trail_length' => GeodataService::calculateDistance(collect($szlak->punkty)->first()->ns, collect($szlak->punkty)->first()->ws, collect($szlak->punkty)->last()->ns, collect($szlak->punkty)->last()->we),
            'author'=>'',
            'difficulty'=> Difficulty::MODERATE,
            'scenery' => 0,
            'rating' => 0
        ]);

        foreach ($szlak->odcinki as $odcinek) {
            $odcinekModel = Section::create([
                'trail_id' => $szlak->id,
                'name' => $odcinek->nazwa,
                'description' => $odcinek->opis,
                'polygon_coordinates' => $odcinek->punkty,
                'scenery' => $odcinek->mal,
                'difficulty' =>$odcinek->tru,
                'nuisance' => $odcinek->uci,
                'cleanliness' => $odcinek->czy,
            ]);
        }

        foreach ($szlak->punkty as $punkt) {
            $punktModel = Point::create([
                'trail_id' => $szlakModel->id,

                'km' => $punkt->km,
                'name' => $punkt->miejscowosc,
                'id_punktu' => $punkt->id,
                'lat' => $punkt->ns,
                'lng' => $punkt->we,
                'order' => $punkt->kolejnosc,
            ]);

            foreach ($punkt->opisypunktu as $opis) {

                PointDescription::create([
                    'point_id'=> $punktModel->id,
                    'point_type' => $opis->typpunktu,
                    'description' => $opis->opis,
                ]);
            }
        }
    }
}
