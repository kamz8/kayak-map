<?php

namespace Kamz\LaravelBRouter\Console;

use Illuminate\Console\Command;

class PrecacheWaterwaysCommand extends Command
{
    protected $signature = 'brouter:precache {bbox} {--name=}';
    protected $description = 'Precache waterways data for specific area';

    public function handle()
    {
        $bbox = $this->parseBBox($this->argument('bbox'));
        $name = $this->option('name');

        $this->info("Precaching waterways for bbox: " . implode(', ', $bbox));

        // Implementation here
        $this->info('Precaching completed!');
    }

    protected function parseBBox(string $bbox): array
    {
        // Parse bbox string like "51.0,16.0,52.0,17.0"
        return array_map('floatval', explode(',', $bbox));
    }
}
