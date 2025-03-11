<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the application is healthy';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Check database connection
            DB::connection()->getPdo();

            // Check Redis connection
            Redis::ping();

            // Check storage is writable
            if (!is_writable(storage_path())) {
                throw new \Exception('Storage directory is not writable');
            }

            // Check if cache is working
            $key = 'health_check_' . time();
            cache()->put($key, true, 10);
            if (!cache()->get($key)) {
                throw new \Exception('Cache is not working');
            }

            $this->info('Application is healthy.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Application is not healthy: ' . $e->getMessage());
            return 1;
        }
    }
}
