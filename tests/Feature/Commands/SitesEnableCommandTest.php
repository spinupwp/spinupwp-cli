<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('enable a site', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'disabled_at' => '2026-10-01T12:00:00.000000Z']]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/enable', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:enable 1')
        ->expectsOutput('==> Site queued for enabling.')
        ->assertExitCode(0);
});

test('enable a site only offers disabled sites', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'disabled_at' => null],
        ]))
    );

    $this->artisan('sites:enable')
        ->expectsOutput('There are no disabled sites.')
        ->assertExitCode(0);
});
