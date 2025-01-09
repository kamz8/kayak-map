<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
/**
 * @method getJson(string $string, array $headers)
 */
class Api404Test extends TestCase
{

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->headers = [
            'X-Client-Type' => 'web'
        ];

    }
    /** @test */
    public function it_handles_invalid_api_endpoints_correctly(): void
    {
        $response = $this->getJson('/api/v1/invalid/endpoint', $this->headers);

        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => '404',
                     'message' => 'API endpoint not found'
                ]
            ]);
    }

}
