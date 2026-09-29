<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'page_cache' => ['enabled' => true]]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('update page cache settings', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/page-cache', [
        'json' => [
            'duration'          => 1,
            'duration_unit'     => 'h',
            'path_exclusions'   => "/wp-admin/\n/cart/",
            'cookie_exclusions' => '',
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:page-cache:update 1 --duration=1 --duration-unit=h --path-exclusion=/wp-admin/ --path-exclusion=/cart/ --cookie-exclusion=')
        ->expectsOutput('==> Page cache settings queued for update.')
        ->assertExitCode(0);
});

test('update page cache settings rejects a non-integer duration', function () {
    $this->artisan('sites:page-cache:update 1 --duration=1.5')
        ->expectsOutput('The --duration option must be an integer.')
        ->assertExitCode(1);
});
