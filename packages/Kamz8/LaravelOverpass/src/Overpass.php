<?php

namespace Kamz8\LaravelOverpass;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

/**
 * Class Overpass
 *
 * This class manages Overpass API interactions and creates HTTP client instances.
 *
 * @package Kamz8\LaravelOverpass
 */
class Overpass
{
    protected array $config;
    protected ?float $lastRequestTime = null;

    public function __construct()
    {
        // Load configuration from Laravel config file 'overpass.php' once
        $this->config = config('overpass');

        // Validate configuration keys
        $this->validateConfig($this->config);
    }

    /**
     * Get the Laravel HTTP client configured for the Overpass API.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public function getClient()
    {
        $client = Http::baseUrl($this->config['endpoint'])
            ->timeout($this->config['timeout'])
            ->withHeaders([
                'User-Agent' => sprintf('%s (%s)', $this->config['app_name'], $this->config['app_author'])
            ]);

        // If throttling is enabled, we implement delay logic before the request
        if ($this->config['throttle']) {
            $client->beforeSending(function () {
                $this->applyThrottle();
            });
        }

        return $client;
    }

    /**
     * Apply throttling logic based on the configuration.
     */
    protected function applyThrottle()
    {
        $limit = $this->config['throttle_limit'];
        $interval = 1 / $limit; // Seconds per request

        if ($this->lastRequestTime !== null) {
            $elapsed = microtime(true) - $this->lastRequestTime;
            $waitTime = max(0, $interval - $elapsed);
            if ($waitTime > 0) {
                usleep((int) ($waitTime * 1e6)); // Convert to microseconds
            }
        }

        // Update the time of the last request
        $this->lastRequestTime = microtime(true);
    }

    /**
     * Validate configuration to ensure all required keys are present
     *
     * @param array $config
     * @throws \InvalidArgumentException
     */
    protected function validateConfig(array $config)
    {
        $requiredKeys = ['throttle', 'throttle_limit', 'endpoint', 'timeout', 'app_name', 'app_author'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $config)) {
                throw new \InvalidArgumentException("Missing required config key: {$key}");
            }
        }
    }

    /**
     * Create a new query builder instance.
     *
     * @return OverpassQueryBuilder
     */
    public function query(): OverpassQueryBuilder
    {
        return new OverpassQueryBuilder($this->getClient(), $this->config);
    }

    /**
     * Create a new query builder instance with a raw query.
     *
     * @param string $query The raw Overpass query
     * @return OverpassQueryBuilder
     */
    public function raw(string $query): OverpassQueryBuilder
    {
        return (new OverpassQueryBuilder($this->getClient(), $this->config))->raw($query);
    }
}
