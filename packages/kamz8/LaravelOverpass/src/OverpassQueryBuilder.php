<?php

namespace Kamz8\LaravelOverpass;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Kamz8\LaravelOverpass\Helpers\BoundingBoxHelper;

class OverpassQueryBuilder
{
    protected Client $client;
    protected array $queryParts = [];
    protected array $filters = [];
    protected ?string $bbox = null;
    protected ?string $around = null;
    protected string $output = 'json';
    protected int $timeout;
    protected array $settings = [];
    protected bool $recursive = false;
    protected ?string $rawQuery = null;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->timeout = config('overpass.timeout');
        $this->settings[] = "[out:{$this->output}]";
        $this->settings[] = "[timeout:{$this->timeout}]";
    }

    public function raw(string $query): static
    {
        $this->rawQuery = $query;
        return $this;
    }

    // Metody do ustawiania typów elementów
    public function node(): static
    {
        $this->queryParts[] = 'node';
        return $this;
    }

    public function way(): static
    {
        $this->queryParts[] = 'way';
        return $this;
    }

    public function relation(): static
    {
        $this->queryParts[] = 'relation';
        return $this;
    }

    public function nwr(): static
    {
        $this->queryParts[] = 'node';
        $this->queryParts[] = 'way';
        $this->queryParts[] = 'relation';
        return $this;
    }

    // Metody do dodawania filtrów
    public function where(string $key, ?string $value = null): static
    {
        if (is_null($value)) {
            $this->filters[] = "['{$key}']";
        } else {
            $this->filters[] = "['{$key}'='{$value}']";
        }
        return $this;
    }

    public function orWhere(string $key, ?string $value = null): static
    {
        $lastFilter = array_pop($this->filters) ?? '';
        if (is_null($value)) {
            $this->filters[] = $lastFilter . "['{$key}']";
        } else {
            $this->filters[] = $lastFilter . "['{$key}'='{$value}']";
        }
        return $this;
    }

    // Metody do określania obszaru wyszukiwania
    public function bbox(float $south, float $west, float $north, float $east): static
    {
        $this->bbox = "({$south},{$west},{$north},{$east})";
        return $this;
    }

    public function bboxFromPoints(float $lat1, float $lon1, float $lat2, float $lon2, float $marginPercent = 10): static
    {
        $helper = new BoundingBoxHelper();
        $this->bbox = $helper->generateBBox($lat1, $lon1, $lat2, $lon2, $marginPercent);
        return $this;
    }

    public function around(float $radius, float $lat, float $lon): static
    {
        $this->around = "(around:{$radius},{$lat},{$lon})";
        return $this;
    }

    // Metody dodatkowe
    public function recurse(): static
    {
        $this->recursive = true;
        return $this;
    }

    public function output(string $output = 'json'): static
    {
        $this->output = $output;
        return $this;
    }

    // Metoda do budowania zapytania
    public function build(): string
    {
        $settings = implode('', $this->settings);
        $queryElements = [];

        foreach ($this->queryParts as $part) {
            $filters = implode('', $this->filters);
            $area = $this->bbox ?? $this->around ?? '';
            $queryElements[] = "({$part}{$filters}{$area};);";
        }

        $query = $settings . implode('', $queryElements);

        if ($this->recursive) {
            $query .= "out body; >; out skel qt;";
        } else {
            $query .= "out body;";
        }

        return $query;
    }

    // Run api query like that Laravel eloquent
    public function get(): array|string
    {
        $fullQuery = $this->rawQuery ?? $this->build();

        try {
            $response = $this->client->request('POST', '', [
                'form_params' => ['data' => $fullQuery],
            ]);

            $body = $response->getBody()->getContents();

            if ($this->output === 'json') {
                return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } else {
                return $body;
            }
        } catch (GuzzleException $e) {
            throw new \Exception("Error communicating with Overpass API: " . $e->getMessage(), $e->getCode(), $e);
        } catch (\JsonException $e) {
            throw new \Exception("Error parsing JSON response: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
