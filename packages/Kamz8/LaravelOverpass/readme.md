# Laravel Overpass ![Laravel](https://img.shields.io/badge/Laravel-11-red.svg) ![Version](https://img.shields.io/badge/version-0.1.0--alpha-blue.svg)

Laravel Overpass is a Laravel package that provides an Eloquent-like syntax for interacting with the Overpass API, allowing you to build and execute complex queries to OpenStreetMap data effortlessly. This package is compatible with Laravel 11.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Building Queries](#building-queries)
    - [Using Raw Queries](#using-raw-queries)
    - [Throttling Requests](#throttling-requests)
    - [Error Handling](#error-handling)
    - [Examples](#examples)
- [Helpers](#helpers)
    - [BoundingBoxHelper](#boundingboxhelper)
    - [RouteHelper](#routehelper)
- [Testing](#testing)
- [License](#license)

## Installation

Install the package via Composer:

```bash
composer require Kamz8/laravel-overpass
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Kamz8\LaravelOverpass\OverpassServiceProvider" --tag="overpass-config"
```

## Configuration

The configuration file `config/overpass.php` allows you to customize the package settings:

```php
return [
    'endpoint' => env('OVERPASS_API_ENDPOINT', 'https://overpass-api.de/api/interpreter'),
    'timeout' => env('OVERPASS_API_TIMEOUT', 60),
    'throttle' => env('OVERPASS_API_THROTTLE', true),
    'throttle_limit' => env('OVERPASS_API_THROTTLE_LIMIT', 1), // Max requests per second
    'app_name' => env('OVERPASS_API_APP_NAME', 'YourAppName'),
    'app_author' => env('OVERPASS_API_APP_AUTHOR', 'YourName'),
];
```

### Environment Variables

You can set these configurations in your `.env` file:

- `OVERPASS_API_ENDPOINT`: Overpass API endpoint URL.
- `OVERPASS_API_TIMEOUT`: Request timeout in seconds.
- `OVERPASS_API_THROTTLE`: Enable or disable throttling (`true` or `false`).
- `OVERPASS_API_THROTTLE_LIMIT`: Maximum number of requests per second.
- `OVERPASS_API_APP_NAME`: Your application name (sent in the `User-Agent` header).
- `OVERPASS_API_APP_AUTHOR`: Your name (sent in the `User-Agent` header).

## Usage

### Building Queries

You can build queries using an Eloquent-like syntax:

```php
use Kamz8\LaravelOverpass\Facades\Overpass;

$results = Overpass::query()
    ->node()
    ->where('amenity', 'cafe')
    ->bbox(51.5, -0.1, 51.6, 0.1)
    ->limit(10)
    ->get();
```

### Using Raw Queries

You can also use raw Overpass QL queries if needed:

```php
$query = '[out:json];node["amenity"="cafe"](51.5,-0.1,51.6,0.1);out body;';
$results = Overpass::raw($query)->get();
```

### Throttling Requests

Throttling can be enabled to avoid exceeding the Overpass API rate limits. By default, throttling is enabled with a limit of 1 request per second.

### Error Handling

The package provides robust error handling to capture Overpass API errors:

```php
try {
    $results = Overpass::query()->node()->where('amenity', 'cafe')->get();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

### Examples

#### Fetching Waterway Objects (e.g., Weirs)

```php
$weirs = Overpass::query()
    ->way()
    ->where('waterway', 'weir')
    ->bboxFromPoints($lat1, $lon1, $lat2, $lon2, marginPercent: 10)
    ->recurse()
    ->get();
```

#### Finding a Route from Point A to Point B

```php
use Kamz8\LaravelOverpass\Helpers\RouteHelper;

$routeHelper = new RouteHelper();
$route = $routeHelper->findRoute($latA, $lonA, $latB, $lonB);
```

## Helpers

### BoundingBoxHelper

The `BoundingBoxHelper` class helps generate a bounding box (BBox) with an optional margin percentage.

#### Generating a BBox with Margin

```php
$helper = new \Kamz8\LaravelOverpass\Helpers\BoundingBoxHelper();
$bbox = $helper->generateBBox($lat1, $lon1, $lat2, $lon2, $marginPercent = 10);
```

### RouteHelper

The `RouteHelper` class assists in finding a route between two points.

#### Finding a Waterway Route

```php
use Kamz8\LaravelOverpass\Helpers\RouteHelper;

$routeHelper = new RouteHelper();
$route = $routeHelper->findRoute($latA, $lonA, $latB, $lonB);
```

## Testing

The package includes unit tests to ensure functionality.

### Running Tests

Run the tests using PHPUnit:

```bash
vendor/bin/phpunit
```

### Sample Test

```php
public function testSimpleQuery()
{
    $data = Overpass::query()
        ->node()
        ->where('amenity', 'cafe')
        ->bbox(51.5, -0.1, 51.6, 0.1)
        ->limit(1)
        ->get();

    $this->assertIsArray($data);
    $this->assertArrayHasKey('elements', $data);
    $this->assertNotEmpty($data['elements']);
}
```

## Documentation

For more detailed documentation about the available methods and their usage, please refer to the [OverpassQueryBuilder Documentation](https://github.com/kamz8/LaravelOverpass/blob/dev/Doc/OverpassQueryBuilder.md).

## Compatibility

This package is compatible with **Laravel 11**.
## License

Laravel Overpass is open-source software licensed under the [MIT license](LICENSE).

**Author**: Kamil Żmijowski (kamzil2@gmail.com)


