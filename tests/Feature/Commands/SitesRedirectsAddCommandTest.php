<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com']]))
    );

    $this->redirects = [
        ['from' => '/old-path', 'to' => '/new-path', 'type' => 'permanent', 'created_at' => '2024-07-01T12:00:00.000000Z'],
        ['from' => '/blog', 'to' => 'https://blog.hellfishmedia.com', 'type' => 'redirect', 'created_at' => '2024-07-01T12:00:00.000000Z'],
    ];
});

afterEach(function () {
    deleteTestConfigFile();
});

test('add a path redirect', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/path-redirects', [
        'json' => ['from' => '/old-path', 'to' => '/new-path', 'type' => 'permanent'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:redirects:add 1 --from=/old-path --to=/new-path --type=permanent')
        ->expectsOutput('==> Path redirect queued for creation.')
        ->assertExitCode(0);
});

test('add a path redirect interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/path-redirects', [
        'json' => ['from' => '/old-path', 'to' => '/new-path', 'type' => 'redirect'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:redirects:add 1')
        ->expectsQuestion('Redirect from', '/old-path')
        ->expectsQuestion('Redirect to', '/new-path')
        ->expectsQuestion('Redirect type', 'redirect')
        ->expectsOutput('==> Path redirect queued for creation.')
        ->assertExitCode(0);
});

test('add a path redirect with force option uses the default type', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/path-redirects', [
        'json' => ['from' => '/old-path', 'to' => '/new-path', 'type' => 'permanent'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:redirects:add 1 --from=/old-path --to=/new-path --force')
        ->expectsOutput('==> Path redirect queued for creation.')
        ->assertExitCode(0);
});
