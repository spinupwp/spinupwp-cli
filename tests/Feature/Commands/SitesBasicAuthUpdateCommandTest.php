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

test('update basic auth', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/basic-auth', [
        'json' => ['username' => 'newuser'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:basic-auth:update 1 --username=newuser')
        ->expectsOutput('==> Basic auth credentials queued for update.')
        ->assertExitCode(0);
});

test('update basic auth without changes', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/basic-auth', [
        'json' => ['username' => 'turnipjuice'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => null])));

    $this->artisan('sites:basic-auth:update 1 --username=turnipjuice')
        ->expectsOutput('==> Basic auth credentials are already up to date.')
        ->assertExitCode(0);
});
