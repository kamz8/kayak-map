<?php

namespace Kamz8\LaravelOverpass;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Kamz8\LaravelOverpass\Helpers\BoundingBoxHelper;

/**
 * Class OverpassQueryBuilder
 *
 * This class is responsible for building and executing Overpass API queries.
 *
 * @package Kamz8\LaravelOverpass
 */
class OverpassQueryBuilder
{
    /** @var Client The HTTP client used for API requests */
    protected Client $client;

    /** @var array The configuration array for the Overpass client */
    protected array $config;

    /** @var array The parts of the Overpass query */
    protected array $queryParts = [];

    /** @var array The filters to apply to the query */
    protected array $filters = [];

    /** @var string|null The bounding box for the query */
    protected ?string $bbox = null;

    /** @var string|null The 'around' parameter for the query */
    protected ?string $around = null;

    /** @var string The output format for the query */
    protected string $output = 'json';

    /** @var int The timeout for the query */
    protected int $timeout;

    /** @var array Additional settings for the query */
    protected array $settings = [];

    /** @var bool Whether to use recursion in the query */
    protected bool $recursive = false;

    /** @var string|null The raw query, if provided */
    protected ?string $rawQuery = null;

    /** @var int|null The limit for the number of results */
    protected ?int $limit = null;

    /**
     * OverpassQueryBuilder constructor.
     *
     * @param Client $client The HTTP client
     * @param array $config The configuration array
     */
    public function __construct(Client $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
        $this->timeout = $config['timeout'];
        $this->settings[] = "[out:{$this->output}]";
        $this->settings[] = "[timeout:{$this->timeout}]";
    }

    /**
     * Set a raw query to be executed.
     *
     * @param string $query The raw Overpass query
     * @return $this
     */
    public function raw(string $query): static
    {
        $this->rawQuery = $query;
        return $this;
    }

    /**
     * Add a node element to the query.
     *
     * @return $this
     */
    public function node(): static
    {
        $this->queryParts[] = 'node';
        return $this;
    }

    /**
     * Add a way element to the query.
     *
     * @return $this
     */
    public function way(): static
    {
        $this->queryParts[] = 'way';
        return $this;
    }

    /**
     * Add a relation element to the query.
     *
     * @return $this
     */
    public function relation(): static
    {
        $this->queryParts[] = 'relation';
        return $this;
    }

    /**
     * Add node, way, and relation elements to the query.
     *
     * @return $this
     */
    public function nwr(): static
    {
        $this->queryParts[] = 'node';
        $this->queryParts[] = 'way';
        $this->queryParts[] = 'relation';
        return $this;
    }

    /**
     * Add a filter to the query.
     *
     * @param string $key The key to filter on
     * @param string|null $value The value to filter for (optional)
     * @return $this
     */
    public function where(string $key, ?string $value = null): static
    {
        if (is_null($value)) {
            $this->filters[] = "['{$key}']";
        } else {
            $this->filters[] = "['{$key}'='{$value}']";
        }
        return $this;
    }

    /**
     * Add an OR filter to the query.
     *
     * @param string $key The key to filter on
     * @param string|null $value The value to filter for (optional)
     * @return $this
     */
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

    /**
     * Set the bounding box for the query.
     *
     * @param float $south The southern latitude of the bounding box
     * @param float $west The western longitude of the bounding box
     * @param float $north The northern latitude of the bounding box
     * @param float $east The eastern longitude of the bounding box
     * @return $this
     */
    public function bbox(float $south, float $west, float $north, float $east): static
    {
        $this->bbox = "({$south},{$west},{$north},{$east})";
        return $this;
    }

    /**
     * Set the bounding box for the query using two points and a margin.
     *
     * @param float $lat1 The latitude of the first point
     * @param float $lon1 The longitude of the first point
     * @param float $lat2 The latitude of the second point
     * @param float $lon2 The longitude of the second point
     * @param float $marginPercent The margin percentage to add around the bounding box
     * @return $this
     */
    public function bboxFromPoints(float $lat1, float $lon1, float $lat2, float $lon2, float $marginPercent = 10): static
    {
        $helper = new BoundingBoxHelper();
        $this->bbox = $helper->generateBBox($lat1, $lon1, $lat2, $lon2, $marginPercent);
        return $this;
    }

    /**
     * Set the 'around' parameter for the query.
     *
     * @param float $radius The radius in meters
     * @param float $lat The latitude of the center point
     * @param float $lon The longitude of the center point
     * @return $this
     */
    public function around(float $radius, float $lat, float $lon): static
    {
        $this->around = "(around:{$radius},{$lat},{$lon})";
        return $this;
    }

    /**
     * Enable recursion for the query.
     *
     * @return $this
     */
    public function recurse(): static
    {
        $this->recursive = true;
        return $this;
    }

    /**
     * Set the output format for the query.
     *
     * @param string $output The desired output format (e.g., 'json', 'xml')
     * @return $this
     */
    public function output(string $output = 'json'): static
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Set the limit for the number of results.
     *
     * @param int $limit The maximum number of results to return
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Build the Overpass query string.
     *
     * @return string The complete Overpass query
     */
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
            $query .= "out body";
            if ($this->limit !== null) {
                $query .= " {$this->limit}";
            }
            $query .= ";";
        }

        return $query;
    }

    /**
     * Execute the query and return the results.
     *
     * @return array|string The query results
     * @throws \Exception If there's an error communicating with the API or parsing the response
     */
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
