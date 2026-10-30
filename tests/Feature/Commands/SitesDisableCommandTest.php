<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('disable a site', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'disabled_at' => null]]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/disable', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:disable 1')
        ->expectsConfirmation('Are you sure you want to disable "hellfishmedia.com"? It will stop serving traffic.', 'yes')
        ->expectsOutput('==> Site queued for disabling.')
        ->assertExitCode(0);
});

test('disable a site does nothing when not confirmed', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'disabled_at' => null]]))
    );

    $this->clientMock->shouldNotReceive('request')->with('POST', 'sites/1/disable', []);

    $this->artisan('sites:disable 1')
        ->expectsConfirmation('Are you sure you want to disable "hellfishmedia.com"? It will stop serving traffic.', 'no')
        ->assertExitCode(0);
});

test('disable a site only offers enabled sites', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'disabled_at' => '2026-10-01T12:00:00.000000Z'],
        ]))
    );

    $this->artisan('sites:disable')
        ->expectsOutput('There are no enabled sites.')
        ->assertExitCode(0);
});
