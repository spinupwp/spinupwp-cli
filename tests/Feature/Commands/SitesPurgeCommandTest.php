<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'         => 1,
            'domain'     => 'hellfish.media',
            'page_cache' => [
                'enabled' => true,
            ],
            'is_wordpress' => true,
        ]]))
    );

    $this->clientMock->shouldReceive('request')
        ->with('POST', 'sites/1/page-cache/purge', [])
        ->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->clientMock->shouldReceive('request')
        ->with('POST', 'sites/1/object-cache/purge', [])
        ->andReturn(new Response(200, [], json_encode(['event_id' => 100])));
});

afterEach(function () {
    deleteTestConfigFile();
});

test('purge site page cache', function () {
    $this->artisan('sites:purge 1 --cache=page')->expectsOutput('==> Site queued for purging page cache.');
});

test('purge site object cache', function () {
    $this->artisan('sites:purge 1 --cache=object')->expectsOutput('==> Site queued for purging object cache.');
});

test('purge all sites page cache', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfish.media', 'page_cache' => ['enabled' => true], 'is_wordpress' => true],
            ['id' => 2, 'domain' => 'staging.hellfish.media', 'page_cache' => ['enabled' => true], 'is_wordpress' => true],
        ]))
    );

    $this->clientMock->shouldReceive('request')
        ->with('POST', 'sites/2/page-cache/purge', [])
        ->andReturn(new Response(200, [], json_encode(['event_id' => 101])));

    $this->artisan('sites:purge --all --cache=page')
    ->expectsOutput('==> Sites queued for purging page cache.');
});

test('purge all sites object cache', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfish.media', 'page_cache' => ['enabled' => true], 'is_wordpress' => true],
            ['id' => 2, 'domain' => 'staging.hellfish.media', 'page_cache' => ['enabled' => true], 'is_wordpress' => true],
        ]))
    );

    $this->clientMock->shouldReceive('request')
        ->with('POST', 'sites/2/object-cache/purge', [])
        ->andReturn(new Response(200, [], json_encode(['event_id' => 101])));

    $this->artisan('sites:purge --all --cache=object')
        ->expectsOutput('==> Sites queued for purging object cache.');
});
