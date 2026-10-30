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

test('enable wp cron', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/cron', [
        'json' => ['interval' => 5],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:cron:enable 1 --interval=5')
        ->expectsOutput('==> WP cron queued for enabling.')
        ->assertExitCode(0);
});

test('enable wp cron for a selected site only lists sites with it disabled', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'cron' => ['enabled' => false]],
            ['id' => 2, 'domain' => 'staging.hellfishmedia.com', 'cron' => ['enabled' => true, 'interval' => 5]],
        ]))
    );
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/cron', [
        'json' => ['interval' => 5],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:cron:enable --interval=5')
        ->expectsChoice('Which site would you like to enable the WP cron for', '1', [1, 'hellfishmedia.com'])
        ->expectsOutput('==> WP cron queued for enabling.')
        ->assertExitCode(0);
});

test('enable wp cron when all sites already have it enabled', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'cron' => ['enabled' => true, 'interval' => 5]],
        ]))
    );

    $this->artisan('sites:cron:enable')
        ->expectsOutput('There are no sites with WP cron disabled.')
        ->assertExitCode(0);
});

test('enable wp cron with force option uses the default interval', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/cron', [
        'json' => ['interval' => 5],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:cron:enable 1 --force')
        ->expectsOutput('==> WP cron queued for enabling.')
        ->assertExitCode(0);
});
