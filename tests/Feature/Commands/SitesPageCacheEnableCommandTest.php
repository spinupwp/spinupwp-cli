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

test('enable page cache', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/page-cache', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:page-cache:enable 1')
        ->expectsOutput('==> Page cache queued for enabling.')
        ->assertExitCode(0);
});

test('enable page cache for a selected site', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'page_cache' => ['enabled' => false]],
            ['id' => 2, 'domain' => 'staging.hellfishmedia.com', 'page_cache' => ['enabled' => true]],
        ]))
    );
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/page-cache', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:page-cache:enable')
        ->expectsQuestion('Which site would you like to enable the page cache for', '1')
        ->expectsOutput('==> Page cache queued for enabling.')
        ->assertExitCode(0);
});

test('enable page cache when all sites already have it enabled', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'page_cache' => ['enabled' => true]],
        ]))
    );

    $this->artisan('sites:page-cache:enable')
        ->expectsOutput('There are no sites with page cache disabled.')
        ->assertExitCode(0);
});
