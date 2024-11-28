<?php

namespace App\Console\Commands;

use App\Jobs\ProcessGpxFileJob;
use App\Models\GpxProcessingStatus;
use App\Models\Trail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessGpxFileCommand extends Command
{
    protected $signature = 'gpx:process
                          {filename : Nazwa pliku GPX w storage/app/gpx}
                          {trail_id : ID szlaku}
                          {--force : Wymuś ponowne przetworzenie, nawet jeśli plik był już przetwarzany}';

    protected $description = 'Dodaje plik GPX do kolejki przetwarzania i przypisuje go do szlaku';

    public function handle(): int
    {
        $filename = $this->argument('filename');
        $trailId = $this->argument('trail_id');
        $force = $this->option('force');

        try {

            $trail = Trail::findOrFail($trailId);

            // Przygotuj ścieżkę do pliku
            $filePath = 'gpx/' . $filename;

            if (!Storage::exists($filePath)) {
                $this->error("Plik nie istnieje: storage/app/{$filePath}");
                return Command::FAILURE;
            }

            if (!Str::endsWith(strtolower($filename), '.gpx')) {
                $this->error('Plik musi mieć rozszerzenie .gpx');
                return Command::FAILURE;
            }

            $existingStatus = GpxProcessingStatus::query()
                ->where('trail_id', $trailId)
                ->where('file_path', $filePath)
                ->where('status', 'completed')
                ->exists();

            if ($existingStatus && !$force) {
                $this->error('Ten plik był już przetworzony dla tego szlaku. Użyj --force aby przetworzyć ponownie.');
                return Command::FAILURE;
            }

            // Utwórz nowy status przetwarzania
            $status = GpxProcessingStatus::create([
                'trail_id' => $trailId,
                'file_path' => $filePath,
                'status' => 'pending',
                'message' => 'Queued for processing'
            ]);

            // Dodaj job do kolejki
            ProcessGpxFileJob::dispatch($filePath, $trailId, $status->id);

            $this->info('Plik GPX został dodany do kolejki przetwarzania.');
            $this->info("ID statusu przetwarzania: {$status->id}");

            // Pokaż informacje o kolejce
            $this->newLine();
            $this->info('Aby uruchomić kolejkę, wykonaj:');
            $this->line('php artisan queue:work');

            return Command::SUCCESS;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("Szlak o ID {$trailId} nie istnieje.");
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error("Wystąpił błąd: {$e->getMessage()}");
            $this->newLine();
            $this->error("Stack trace:");
            $this->line($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
