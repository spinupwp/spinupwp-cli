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

test('update wp cron interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/cron', [
        'json' => ['interval' => 15],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:cron:update 1')
        ->expectsQuestion('How often should the WP cron run (1, 2, 5, 10, 15, 30, or 60 minutes)?', '15')
        ->expectsOutput('==> WP cron interval queued for update.')
        ->assertExitCode(0);
});

test('update wp cron with interval and force option', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/cron', [
        'json' => ['interval' => 30],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:cron:update 1 --interval=30 --force')
        ->expectsOutput('==> WP cron interval queued for update.')
        ->assertExitCode(0);
});
