<?php

namespace Kamz8\LaravelOverpass\Middleware;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class ThrottleMiddleware
{
    protected int $limit; // Max requests per second
    protected float $lastRequestTime = 0.0;

    public function __construct(int $limit)
    {
        $this->limit = $limit;

    }

    public function __invoke(callable $handler): callable
    {
        return function (Request $request, array $options) use ($handler): PromiseInterface {
            $request = $request->withHeader('X-Middleware', 'ThrottleMiddleware');
            $now = microtime(true);
            $elapsed = $now - $this->lastRequestTime;
            $waitTime = max(0, (1 / $this->limit) - $elapsed);

            if ($waitTime > 0) {
                usleep((int) ($waitTime * 1e6));
            }

            $this->lastRequestTime = microtime(true);

            return $handler($request, $options);
        };
    }
}
