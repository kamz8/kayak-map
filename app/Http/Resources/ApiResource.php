<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct(
        private readonly mixed $data,
        private readonly string $message = '',
        private readonly int $code = 200
    ) {
        parent::__construct($data);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->code >= 200 && $this->code < 300 ? 'success' : 'error',
            'message' => $this->message,
            'data' => $this->data
        ];
    }

    /**
     * @param $request
     * @return array
     */
    public function with($request): array
    {
        return [
            'meta' => [
                'timestamp' => now()->toIso8601String()
            ]
        ];
    }
}
