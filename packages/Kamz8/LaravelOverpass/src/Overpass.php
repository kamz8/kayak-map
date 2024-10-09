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
    protected Client $client;
    protected bool $throttle;
    protected ?int $throttleLimit;
    protected array $config;

    public function __construct()
    {
        // Pobieramy konfigurację raz i zapisujemy w zmiennej $config
        $this->config = config('overpass');

        // Ustawiamy throttling na podstawie konfiguracji (domyślnie true)
        $this->throttle = $this->config['throttle'] ?? true;

        // Ustawiamy limit throttlingu tylko, jeśli throttling jest włączony
        $this->throttleLimit = $this->throttle ? ($this->config['throttle_limit'] ?? 1) : null;

        // Tworzymy stos handlerów Guzzle, aby ewentualnie dodać middleware throttlingu
        $stack = HandlerStack::create();

        if ($this->throttle) {
            $stack->push(
                new ThrottleMiddleware($this->throttleLimit),
                'throttle'
            );
        }

        // Inicjalizujemy klienta Guzzle z odpowiednim stosunkiem middleware
        $this->client = new Client([
            'base_uri' => $this->config['endpoint'] ?? 'https://overpass-api.de/api/interpreter',
            'timeout'  => $this->config['timeout'] ?? 10,
            'handler'  => $stack,
        ]);
    }

    /**
     * Get the throttling limit.
     *
     * @return int|null Returns the limit if throttling is enabled, otherwise null.
     */
    public function getThrottleLimit(): ?int
    {
        return $this->throttleLimit;
    }

    /**
     * Get the Guzzle client's handler stack.
     *
     * This allows access to the handler stack for testing purposes,
     * particularly to verify whether the throttling middleware is attached.
     *
     * @return HandlerStack The handler stack used by the Guzzle client.
     */
    public function getHandler(): HandlerStack
    {
        return $this->client->getConfig('handler');
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
