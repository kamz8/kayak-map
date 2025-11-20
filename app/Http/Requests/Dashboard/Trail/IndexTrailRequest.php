<?php

namespace App\Http\Requests\Dashboard\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTrailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin users or users with permission can view trails
        return $this->user() && ($this->user()->is_admin || $this->user()->can('trails.view'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Pagination
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Search
            'search' => ['nullable', 'string', 'max:255'],

            // Status filter
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'draft', 'archived'])],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', Rule::in(['active', 'inactive', 'draft', 'archived'])],

            // Difficulty filter
            'difficulty' => ['nullable', 'string', Rule::in(['łatwy', 'umiarkowany', 'trudny'])],
            'difficulties' => ['nullable', 'array'],
            'difficulties.*' => ['string', Rule::in(['łatwy', 'umiarkowany', 'trudny'])],

            // Region filter
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['integer', 'exists:regions,id'],

            // Date range filter
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],

            // Scenery rating filter
            'min_scenery' => ['nullable', 'integer', 'min:0', 'max:10'],
            'max_scenery' => ['nullable', 'integer', 'min:0', 'max:10', 'gte:min_scenery'],

            // Trail rating filter
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'max_rating' => ['nullable', 'numeric', 'min:0', 'max:10', 'gte:min_rating'],

            // Trail length filter (in kilometers)
            'min_length' => ['nullable', 'integer', 'min:0'],
            'max_length' => ['nullable', 'integer', 'min:0', 'gte:min_length'],

            // Sorting
            'sort_by' => ['nullable', 'string', Rule::in([
                'id', 'trail_name', 'river_name', 'difficulty',
                'scenery', 'rating', 'trail_length', 'status',
                'created_at', 'updated_at'
            ])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],

            // Include relationships
            'with' => ['nullable', 'array'],
            'with.*' => ['string', Rule::in(['images', 'regions', 'sections', 'points', 'riverTrack'])],

            // Count relationships
            'with_count' => ['nullable', 'array'],
            'with_count.*' => ['string', Rule::in(['images', 'regions', 'sections', 'points'])],

            // Author filter
            'author' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'page' => 'numer strony',
            'per_page' => 'liczba elementów na stronę',
            'search' => 'wyszukiwanie',
            'status' => 'status',
            'statuses' => 'statusy',
            'difficulty' => 'trudność',
            'difficulties' => 'trudności',
            'region_id' => 'identyfikator regionu',
            'region_ids' => 'identyfikatory regionów',
            'start_date' => 'data początkowa',
            'end_date' => 'data końcowa',
            'min_scenery' => 'minimalna ocena krajobrazu',
            'max_scenery' => 'maksymalna ocena krajobrazu',
            'min_rating' => 'minimalna ocena',
            'max_rating' => 'maksymalna ocena',
            'min_length' => 'minimalna długość',
            'max_length' => 'maksymalna długość',
            'sort_by' => 'sortowanie według',
            'sort_order' => 'kolejność sortowania',
            'author' => 'autor',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Nieprawidłowy status. Dozwolone wartości: active, inactive, draft, archived.',
            'difficulty.in' => 'Nieprawidłowa trudność. Dozwolone wartości: łatwy, umiarkowany, trudny.',
            'end_date.after_or_equal' => 'Data końcowa musi być późniejsza lub równa dacie początkowej.',
            'max_scenery.gte' => 'Maksymalna ocena krajobrazu musi być większa lub równa minimalnej.',
            'max_rating.gte' => 'Maksymalna ocena musi być większa lub równa minimalnej.',
            'max_length.gte' => 'Maksymalna długość musi być większa lub równa minimalnej.',
        ];
    }

    /**
     * Get validated data with defaults
     */
    public function getFilters(): array
    {
        return [
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', 15),
            'search' => $this->input('search'),
            'status' => $this->input('status'),
            'statuses' => $this->input('statuses'),
            'difficulty' => $this->input('difficulty'),
            'difficulties' => $this->input('difficulties'),
            'region_id' => $this->input('region_id'),
            'region_ids' => $this->input('region_ids'),
            'start_date' => $this->input('start_date'),
            'end_date' => $this->input('end_date'),
            'min_scenery' => $this->input('min_scenery'),
            'max_scenery' => $this->input('max_scenery'),
            'min_rating' => $this->input('min_rating'),
            'max_rating' => $this->input('max_rating'),
            'min_length' => $this->input('min_length'),
            'max_length' => $this->input('max_length'),
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_order' => $this->input('sort_order', 'desc'),
            'with' => $this->input('with', []),
            'with_count' => $this->input('with_count', ['images', 'sections', 'points', 'regions']),
            'author' => $this->input('author'),
        ];
    }
}