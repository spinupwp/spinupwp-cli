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

test('delete a path redirect', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/path-redirects', [
        'json' => ['from' => '/old-path', 'to' => '/new-path'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:redirects:delete 1 --from=/old-path --to=/new-path --force')
        ->expectsOutput('==> Path redirect queued for deletion.')
        ->assertExitCode(0);
});

test('delete a path redirect selected interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1/path-redirects?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson($this->redirects))
    );
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/path-redirects', [
        'json' => ['from' => '/blog', 'to' => 'https://blog.hellfishmedia.com'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:redirects:delete 1')
        ->expectsQuestion('Which path redirect would you like to delete', '1')
        ->expectsConfirmation('Are you sure you want to delete the redirect from "/blog" to "https://blog.hellfishmedia.com"?', 'yes')
        ->expectsOutput('==> Path redirect queued for deletion.')
        ->assertExitCode(0);
});
