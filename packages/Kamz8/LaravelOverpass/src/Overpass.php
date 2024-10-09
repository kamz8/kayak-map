<?php

namespace Kamz8\LaravelOverpass;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kamz8\LaravelOverpass\Middleware\ThrottleMiddleware;
use Illuminate\Support\Facades\Config;

/**
 * Class Overpass
 *
 * This class is the main entry point for interacting with the Overpass API.
 * It manages the HTTP client and provides methods to create queries.
 *
 * @package Kamz8\LaravelOverpass
 */
class Overpass
{
    /** @var Client The HTTP client used for API requests */
    public Client $client;

    /** @var array The configuration array for the Overpass client */
    protected array $config;

    /**
     * Overpass constructor.
     *
     * @param array|null $config Optional configuration array to override default settings
     */
    public function __construct(?array $config = null)
    {
        $this->config = $config ?? Config::get('overpass');

        $stack = HandlerStack::create();

        if ($this->config['throttle']) {
            $stack->push(
                new ThrottleMiddleware($this->config['throttle_limit']),
                'throttle'
            );
        }

        $this->client = new Client([
            'base_uri' => $this->config['endpoint'],
            'timeout'  => $this->config['timeout'],
            'handler'  => $stack,
            'headers'  => [
                'User-Agent' => "{$this->config['app_name']} ({$this->config['app_author']})",
            ],
        ]);
    }

    /**
     * Create a new query builder instance.
     *
     * @return OverpassQueryBuilder
     */
    public function query(): OverpassQueryBuilder
    {
        return new OverpassQueryBuilder($this->client, $this->config);
    }

    /**
     * Create a new query builder instance with a raw query.
     *
     * @param string $query The raw Overpass query
     * @return OverpassQueryBuilder
     */
    public function raw(string $query): OverpassQueryBuilder
    {
        return (new OverpassQueryBuilder($this->client, $this->config))->raw($query);
    }
}
