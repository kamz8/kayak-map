<?php

it('returns a successful response', function () {
    $headers = [
        'X-Client-Type' => 'web',
    ];
    $response = $this->get('api/v1/',$headers);

    $response->assertStatus(200);
});
