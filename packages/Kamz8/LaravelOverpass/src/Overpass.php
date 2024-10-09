<?php

namespace Kamz8\LaravelOverpass;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Kamz8\LaravelOverpass\Helpers\ThrottleMiddleware;

class Overpass
{
    public Client $client;
    protected string $endpoint;
    protected int $timeout;
    protected bool $throttle;
    protected int $throttleLimit;
    protected string $appName;
    protected string $appAuthor;

    public function __construct()
    {
        $this->endpoint = config('overpass.endpoint');
        $this->timeout = config('overpass.timeout');
        $this->throttle = config('overpass.throttle');
        $this->throttleLimit = config('overpass.throttle_limit');
        $this->appName = config('overpass.app_name');
        $this->appAuthor = config('overpass.app_author');

        $stack = HandlerStack::create();

        if ($this->throttle) {
            $stack->push(
                new ThrottleMiddleware($this->throttleLimit),
                'throttle'
            );
        }

        $this->client = new Client([
            'base_uri' => $this->endpoint,
            'timeout'  => $this->timeout,
            'handler'  => $stack,
            'headers'  => [
                'User-Agent' => "{$this->appName} ({$this->appAuthor})",
            ],
        ]);
    }

    public function query(): OverpassQueryBuilder
    {
        return new OverpassQueryBuilder($this->client);
    }

    public function raw(string $query): OverpassQueryBuilder
    {
        return (new OverpassQueryBuilder($this->client))->raw($query);
    }
}
