<?php

namespace App\Console\Commands;

use App\Services\SearchService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ClearSearchCache extends Command
{
    protected $signature = 'search:clear-cache {--type= : The type of search cache to clear (all, trail, or a RegionType value)}';

    protected $description = 'Clear the search cache, optionally for a specific search type';

    public function handle(SearchService $searchService): int
    {
        $type = $this->option('type');

        if ($type) {
            $this->info("Clearing search cache for type: {$type}...");
        } else {
            $this->info('Clearing all search cache...');
        }

        try {
            $searchService->clearSearchCache($type);
            $this->info('Search cache cleared successfully.');
            return CommandAlias::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred while clearing the search cache: ' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
