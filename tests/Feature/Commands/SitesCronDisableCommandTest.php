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

test('disable wp cron', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/cron', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:cron:disable 1 --force')
        ->expectsOutput('==> WP cron queued for disabling.')
        ->assertExitCode(0);
});

test('disable wp cron when no sites have it enabled', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'cron' => ['enabled' => false]],
        ]))
    );

    $this->artisan('sites:cron:disable')
        ->expectsOutput('There are no sites with WP cron enabled.')
        ->assertExitCode(0);
});
