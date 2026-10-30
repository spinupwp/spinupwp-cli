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

test('list path redirects', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1/path-redirects?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson($this->redirects))
    );

    $this->artisan('sites:redirects:list 1')
        ->expectsOutput(json_encode($this->redirects, JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list path redirects as a table', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1/path-redirects?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson($this->redirects))
    );

    $this->artisan('sites:redirects:list 1 --format=table')
        ->expectsTable(['From', 'To', 'Type', 'Created At'], [
            ['/old-path', '/new-path', 'Permanent (301)', '2024-07-01T12:00:00.000000Z'],
            ['/blog', 'https://blog.hellfishmedia.com', 'Temporary (302)', '2024-07-01T12:00:00.000000Z'],
        ])
        ->assertExitCode(0);
});

test('list path redirects when there are none', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1/path-redirects?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([]))
    );

    $this->artisan('sites:redirects:list 1')
        ->expectsOutput('No path redirects found.')
        ->assertExitCode(0);
});
