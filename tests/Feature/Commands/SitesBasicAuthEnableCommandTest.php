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

test('enable basic auth', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/basic-auth', [
        'json' => ['username' => 'turnipjuice', 'password' => 'secret'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:basic-auth:enable 1 --username=turnipjuice')
        ->expectsQuestion('Password', 'secret')
        ->expectsOutput('==> Basic auth queued for enabling.')
        ->assertExitCode(0);
});

test('enable basic auth with force option does not prompt', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/basic-auth', [
        'json' => ['username' => 'turnipjuice', 'password' => 'secret'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:basic-auth:enable 1 --username=turnipjuice --password=secret --force')
        ->expectsOutput('==> Basic auth queued for enabling.')
        ->assertExitCode(0);
});
