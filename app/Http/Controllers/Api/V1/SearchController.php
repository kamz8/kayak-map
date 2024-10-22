<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Services\SearchService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Config;

class SearchController extends Controller
{
    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * SearchController constructor.
     *
     * @param SearchService $searchService
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Handle the search request.
     *
     * @param SearchRequest $request
     * @return AnonymousResourceCollection
     */
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = $request->input('query');
        $type = $request->input('type', 'all');
        $limit = $request->input('limit', Config::get('search.max_results', 50));

        $results = $this->searchService->search($query, $type, $limit);

        return SearchResultResource::collection($results)
            ->additional(['meta' => [
                'query' => $query,
                'type' => $type,
                'total_results' => $results->count(),
                'displayed_results' => min($results->count(), $limit)
            ]]);
    }
}
