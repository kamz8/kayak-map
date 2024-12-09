<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Search",
 *     description="Endpointy wyszukiwania - pozwalają na wyszukiwanie szlaków kajakowych oraz regionów (krajów, województw, miast). Wspierają filtrowanie po typie wyników oraz limit liczby zwracanych elementów."
 * )
 */
class SearchController
{
    /**
     * @OA\Get(
     *     path="/search",
     *     summary="Wyszukiwanie szlaków i regionów",
     *     description="Elastyczne wyszukiwanie szlaków kajakowych i regionów. Pozwala na wyszukiwanie po frazie, z możliwością ograniczenia wyników do konkretnego typu (np. tylko szlaki lub tylko miasta). Wyniki są posortowane według trafności.",
     *     operationId="search",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Fraza wyszukiwania - minimum 2 znaki, maksimum 100 znaków. Może zawierać nazwę szlaku, rzeki, regionu lub dowolny inny tekst do wyszukania.",
     *         @OA\Schema(
     *             type="string",
     *             minLength=2,
     *             maxLength=100,
     *             example="Dunajec"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="Typ wyszukiwanych elementów. Dostępne opcje:
    - all: wszystkie typy (domyślnie)
    - trail: tylko szlaki kajakowe
    - country: tylko kraje
    - state: tylko województwa/stany
    - city: tylko miasta",
     *         @OA\Schema(
     *             type="string",
     *             enum={"all", "trail", "country", "state", "city"},
     *             default="all"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Maksymalna liczba zwracanych wyników. Wartość z zakresu od 1 do 100.",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             maximum=100,
     *             default=50
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sukces. Zwraca listę znalezionych elementów wraz z metadanymi.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SearchResultResource")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="query", type="string", example="Dunajec", description="Użyta fraza wyszukiwania"),
     *                 @OA\Property(property="type", type="string", example="all", description="Użyty typ filtrowania"),
     *                 @OA\Property(property="total_results", type="integer", example=15, description="Całkowita liczba znalezionych wyników"),
     *                 @OA\Property(property="displayed_results", type="integer", example=15, description="Liczba zwróconych wyników (może być mniejsza niż total_results ze względu na limit)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Błąd walidacji parametrów wyszukiwania",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="query",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"The query field must be at least 2 characters."}
     *                 )
     *             )
     *         )
     *     )
     * )
     */
}
