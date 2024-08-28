<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\XMLPathreader;
use App\Models\Szlak as SzlakModel;
use App\Models\Odcinek as OdcinekModel;
use App\Models\Punkt as PunktModel;
use App\Models\OpisPunktu as OpisPunktuModel;

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
                    $this->line("\nPath data successfully loaded: " . $szlak->nazwa. " ".$fullPath);
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
        $szlakModel = SzlakModel::create([
            'nazwa' => $szlak->nazwa,
            'id_szlaku' => $szlak->id,
            'wersja' => $szlak->wersja,
            'opis' => $szlak->opis,
        ]);

        foreach ($szlak->odcinki as $odcinek) {
            $odcinekModel = OdcinekModel::create([
                'szlak_id' => $szlakModel->id,
                'id_odcinka' => $odcinek->id,
                'typ' => $odcinek->typ,
                'nazwa' => $odcinek->nazwa,
                'trudnosc' => $odcinek->tru,
                'uciazliwosc' => $odcinek->uci,
                'malowniczosc' => $odcinek->mal,
                'czystosc' => $odcinek->czy,
                'kolejnosc' => $odcinek->kolejnosc,
                'opis' => $odcinek->opis,
            ]);
        }

        foreach ($szlak->punkty as $punkt) {
            $punktModel = PunktModel::create([
                'szlak_id' => $szlakModel->id,
                'etykieta' => $punkt->etykieta,
                'km' => $punkt->km,
                'miejscowosc' => $punkt->miejscowosc,
                'id_punktu' => $punkt->id,
                'ns' => $punkt->ns,
                'we' => $punkt->we,
                'kolejnosc' => $punkt->kolejnosc,
                'id_wewn' => $punkt->idwewo,
            ]);

            foreach ($punkt->opisypunktu as $opis) {
                OpisPunktuModel::create([
                    'punkt_id' => $punktModel->id,
                    'typ_punktu' => $opis->typpunktu,
                    'kolejnosc' => $opis->kolejnosc,
                    'opis' => $opis->opis,
                ]);
            }
        }
    }
}
