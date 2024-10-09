<?php

use Kamz8\LaravelOverpass\Overpass;
use Kamz8\LaravelOverpass\Middleware\ThrottleMiddleware;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\Config;
use Kamz8\LaravelOverpass\Tests\TestCase;



uses(TestCase::class);

it('initializes the Overpass class correctly', function () {
    // Set the mock configuration using the Config facade
    Config::set('overpass', [
        'throttle' => true,
        'throttle_limit' => 2,
        'endpoint' => 'https://overpass-api.de/api/interpreter',
        'timeout' => 10,
    ]);

    // Create an instance of the Overpass class
    $overpass = new Overpass();

    // Verify that the instance is created properly and the throttle limit is set correctly
    expect($overpass)
        ->toBeInstanceOf(Overpass::class)
        ->and($overpass->getThrottleLimit())->toBe(2);
});

it('adds the throttle middleware if throttle is enabled', function () {
    // Set the mock configuration using the Config facade
    Config::set('overpass', [
        'throttle' => true,
        'throttle_limit' => 3,
        'endpoint' => 'https://overpass-api.de/api/interpreter',
        'timeout' => 10,
    ]);

    // Create an instance of the Overpass class
    $overpass = new Overpass();

    // Get the handler stack through the public getHandler() method
    $handler = $overpass->getHandler();

    // Assert that the handler stack is an instance of HandlerStack
    expect($handler)->toBeInstanceOf(HandlerStack::class);

    // Check if the throttle middleware is present in the handler stack
    $found = false;
    foreach ($handler as $middleware) {
        if ($middleware instanceof ThrottleMiddleware) {
            $found = true;
            break;
        }
    }
    expect($found)->toBeTrue();
});

it('does not add the throttle middleware if throttle is disabled', function () {
    // Set the mock configuration using the Config facade
    Config::set('overpass', [
        'throttle' => false,
        'endpoint' => 'https://overpass-api.de/api/interpreter',
        'timeout' => 10,
    ]);

    // Create an instance of the Overpass class
    $overpass = new Overpass();

    // Get the handler stack through the public getHandler() method
    $handler = $overpass->getHandler();

    // Assert that the handler stack is an instance of HandlerStack
    expect($handler)->toBeInstanceOf(HandlerStack::class);

    // Check if the throttle middleware is not present in the handler stack
    $found = false;
    foreach ($handler as $middleware) {
        if ($middleware instanceof ThrottleMiddleware) {
            $found = true;
            break;
        }
    }
    expect($found)->toBeFalse();
});
