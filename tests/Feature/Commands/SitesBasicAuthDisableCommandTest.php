<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'         => 1,
            'domain'     => 'hellfishmedia.com',
            'basic_auth' => ['enabled' => true],
        ]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('disable basic auth', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/basic-auth', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:basic-auth:disable 1')
        ->expectsConfirmation('Are you sure you want to disable basic auth for "hellfishmedia.com"?', 'yes')
        ->expectsOutput('==> Basic auth queued for disabling.')
        ->assertExitCode(0);
});
