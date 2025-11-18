<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'hellfish-media']]))
    );

    $this->clientMock->shouldReceive('request')->with('POST', 'servers/1/services/redis/restart', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => '100']))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('restart redis for a server', function () {
    $this->artisan('services:redis 1')
        ->expectsConfirmation('Are you sure you want to restart Redis on "hellfish-media"?', 'yes')
        ->expectsOutput('==> Server queued for Redis restart.');
});

test('restart redis for a server with force option', function () {
    $this->artisan('services:redis 1 --force')
        ->expectsOutput('==> Server queued for Redis restart.');
});

test('restart redis on all servers', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'name' => 'hellfish-media'],
            ['id' => 2, 'name' => 'staging.hellfish-media'],
        ]))
    );
    $this->clientMock->shouldReceive('request')->with('POST', 'servers/2/services/redis/restart', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => '101']))
    );
    $this->artisan('services:redis --all')
        ->expectsConfirmation('Are you sure you want to restart Redis on all servers?', 'yes')
        ->expectsOutput('==> Servers queued for Redis restart.');
});
