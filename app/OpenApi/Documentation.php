<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Kayak Trails API",
 *     description="API documentation for kayak trails application. Complete API reference for managing kayak routes, weather data, and geographical information."
 * )
 *
 * @OA\Server(
 *     url="api.wartkinurt.pl/api/v1",
 *     description="Main API Server"
 * )
 *
 *
 *
 *
 * @OA\Tag(name="Trails", description="Endpoints for managing kayak trails")
 * @OA\Tag(name="Regions", description="Endpoints for managing geographical regions")
 * @OA\Tag(name="Weather", description="Weather data endpoints")
 * @OA\Tag(name="Geocoding", description="Geocoding endpoints")
 * @OA\Tag(name="Maps", description="Map generation endpoints")
 * @OA\Tag(name="GPX", description="GPX file processing endpoints")
 * @OA\Tag(name="Search", description="Search functionality endpoints")
 *
 * @OA\ExternalDocumentation(
 *     description="Find out more about our API",
 *     url="https://wartkinurt.pl/api-docs"
 * )
 */
class Documentation {}
